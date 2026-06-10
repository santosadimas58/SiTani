#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>

/*
  SiTani Wemos D1 mini MQTT sensor node.

  Sensors:
    DHT22             : D2 / GPIO4
    Soil moisture mux : A0
    Mux select S0     : D5 / GPIO14
    Mux select S1     : D6 / GPIO12
    Mux select S2     : D7 / GPIO13

  Publish:
    sitani/NODE-02/sensor
*/

const char* WIFI_SSID = "mukaroms";
const char* WIFI_PASSWORD = "mukarom01";

const char* MQTT_HOST = "192.168.1.21";
const int MQTT_PORT = 1883;
const char* MQTT_USERNAME = "";
const char* MQTT_PASSWORD = "";

const char* NODE_CODE = "NODE-02";
const char* MQTT_BASE_TOPIC = "sitani";

const unsigned long SEND_INTERVAL_MS = 10000;
const unsigned long MQTT_RECONNECT_MS = 5000;

const int DHT_PIN = D2;
const int DHT_TYPE = DHT22;
const int SOIL_PIN = A0;
const int MUX_S0_PIN = D5;
const int MUX_S1_PIN = D6;
const int MUX_S2_PIN = D7;
const int STATUS_LED_PIN = LED_BUILTIN;

const bool STATUS_LED_ACTIVE_LOW = true;

const int SOIL_1_MUX_CHANNEL = 0;
const int SOIL_2_MUX_CHANNEL = 1;

const int SOIL_WET_ADC = 300;
const int SOIL_MOIST_ADC = 550;
const int SOIL_DRY_ADC = 800;

WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);
DHT dht(DHT_PIN, DHT_TYPE);

unsigned long lastSendAt = 0;
unsigned long lastMqttReconnectAt = 0;

struct SoilReading {
  int raw;
  float percent;
};

void setup()
{
  Serial.begin(115200);
  delay(200);

  pinMode(STATUS_LED_PIN, OUTPUT);
  pinMode(MUX_S0_PIN, OUTPUT);
  pinMode(MUX_S1_PIN, OUTPUT);
  pinMode(MUX_S2_PIN, OUTPUT);
  dht.begin();

  connectWiFi();
  mqtt.setServer(MQTT_HOST, MQTT_PORT);
}

void loop()
{
  ensureWiFi();
  ensureMqtt();

  if (mqtt.connected()) {
    mqtt.loop();
  }

  const unsigned long now = millis();
  if (mqtt.connected() && now - lastSendAt >= SEND_INTERVAL_MS) {
    lastSendAt = now;
    publishSensorReading();
  }
}

void connectWiFi()
{
  Serial.print("Menghubungkan WiFi: ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  unsigned long startAt = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startAt < 15000) {
    digitalWrite(STATUS_LED_PIN, !digitalRead(STATUS_LED_PIN));
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  if (WiFi.status() == WL_CONNECTED) {
    setStatusLed(true);
    Serial.print("WiFi tersambung. IP Wemos: ");
    Serial.println(WiFi.localIP());
  } else {
    setStatusLed(false);
    Serial.println("WiFi belum tersambung.");
  }
}

void ensureWiFi()
{
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  setStatusLed(false);
  connectWiFi();
}

void ensureMqtt()
{
  if (WiFi.status() != WL_CONNECTED || mqtt.connected()) {
    return;
  }

  if (millis() - lastMqttReconnectAt < MQTT_RECONNECT_MS) {
    return;
  }

  lastMqttReconnectAt = millis();

  String clientId = String("sitani-") + NODE_CODE + "-" + String(ESP.getChipId(), HEX);
  Serial.print("Menghubungkan MQTT: ");
  Serial.println(MQTT_HOST);

  bool connected;
  if (strlen(MQTT_USERNAME) > 0) {
    connected = mqtt.connect(clientId.c_str(), MQTT_USERNAME, MQTT_PASSWORD);
  } else {
    connected = mqtt.connect(clientId.c_str());
  }

  if (!connected) {
    Serial.print("MQTT gagal, state=");
    Serial.println(mqtt.state());
    return;
  }

  Serial.println("MQTT tersambung.");
}

void publishSensorReading()
{
  const SoilReading soil1 = readSoil(SOIL_1_MUX_CHANNEL);
  const SoilReading soil2 = readSoil(SOIL_2_MUX_CHANNEL);
  const int soilRawAvg = (soil1.raw + soil2.raw) / 2;
  const float tempC = dht.readTemperature();
  const float humidity = dht.readHumidity();

  if (isnan(tempC) || isnan(humidity)) {
    Serial.println("Pembacaan DHT22 gagal; data tidak dikirim.");
    return;
  }

  String payload = "{";
  payload += "\"node_code\":\"" + String(NODE_CODE) + "\",";
  payload += "\"soil_1\":" + String(soil1.percent, 1) + ",";
  payload += "\"soil_2\":" + String(soil2.percent, 1) + ",";
  payload += "\"soil_raw_1\":" + String(soil1.raw) + ",";
  payload += "\"soil_raw_2\":" + String(soil2.raw) + ",";
  payload += "\"soil_raw_avg\":" + String(soilRawAvg) + ",";
  payload += "\"soil_status\":\"" + soilStatus(soilRawAvg) + "\",";
  payload += "\"temperature\":" + String(tempC, 1) + ",";
  payload += "\"air_humidity\":" + String(humidity, 1);
  payload += "}";

  String sensorTopic = topic("sensor");
  bool ok = mqtt.publish(sensorTopic.c_str(), payload.c_str());

  Serial.print("MQTT publish ");
  Serial.print(sensorTopic);
  Serial.print(" ");
  Serial.println(ok ? "OK" : "GAGAL");
  Serial.println(payload);
}

String topic(const char* suffix)
{
  return String(MQTT_BASE_TOPIC) + "/" + NODE_CODE + "/" + suffix;
}

SoilReading readSoil(int channel)
{
  selectMuxChannel(channel);
  delay(5);

  analogRead(SOIL_PIN);
  delay(2);

  int raw = analogRead(SOIL_PIN);
  float percent = (float)(SOIL_DRY_ADC - raw) * 100.0 / (float)(SOIL_DRY_ADC - SOIL_WET_ADC);

  SoilReading reading;
  reading.raw = raw;
  reading.percent = constrain(percent, 0.0, 100.0);
  return reading;
}

void selectMuxChannel(int channel)
{
  digitalWrite(MUX_S0_PIN, bitRead(channel, 0));
  digitalWrite(MUX_S1_PIN, bitRead(channel, 1));
  digitalWrite(MUX_S2_PIN, bitRead(channel, 2));
}

String soilStatus(int raw)
{
  if (raw <= SOIL_WET_ADC) {
    return "basah";
  }

  if (raw <= SOIL_MOIST_ADC) {
    return "lembap";
  }

  if (raw >= SOIL_DRY_ADC) {
    return "kering";
  }

  return "mulai_kering";
}

void setStatusLed(bool on)
{
  if (STATUS_LED_ACTIVE_LOW) {
    digitalWrite(STATUS_LED_PIN, on ? LOW : HIGH);
    return;
  }

  digitalWrite(STATUS_LED_PIN, on ? HIGH : LOW);
}
