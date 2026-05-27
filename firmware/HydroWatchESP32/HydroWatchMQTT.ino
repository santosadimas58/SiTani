#include <ESP8266WiFi.h>
#include <PubSubClient.h>

/*
  HydroWatch Wemos D1 mini MQTT firmware

  Publish sensor:
    hydrowatch/NODE-01/sensor

  Subscribe pompa:
    hydrowatch/NODE-01/pump
*/

const char* WIFI_SSID = "NAMA_WIFI";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";

const char* MQTT_HOST = "192.168.1.10";
const int MQTT_PORT = 1883;
const char* MQTT_USERNAME = "";
const char* MQTT_PASSWORD = "";

const char* NODE_CODE = "NODE-01";
const char* MQTT_BASE_TOPIC = "hydrowatch";

const unsigned long SEND_INTERVAL_MS = 5000;
const unsigned long MQTT_RECONNECT_MS = 5000;

const int SOIL_PIN = A0;
const int FLOW_PIN = D5;
const int RELAY_PIN = D6;
const int STATUS_LED_PIN = LED_BUILTIN;

const bool RELAY_ACTIVE_LOW = true;
const bool STATUS_LED_ACTIVE_LOW = true;

const int SOIL_DRY_ADC = 850;
const int SOIL_WET_ADC = 350;
const float DEFAULT_PH = 7.0;
const float FLOW_PULSES_PER_LITER = 450.0;

WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);

volatile unsigned long flowPulseCount = 0;
unsigned long lastSendAt = 0;
unsigned long lastMqttReconnectAt = 0;

void ICACHE_RAM_ATTR onFlowPulse()
{
  flowPulseCount++;
}

void setup()
{
  Serial.begin(115200);
  delay(200);

  pinMode(RELAY_PIN, OUTPUT);
  pinMode(STATUS_LED_PIN, OUTPUT);
  pinMode(FLOW_PIN, INPUT_PULLUP);

  setPump(false);
  attachInterrupt(digitalPinToInterrupt(FLOW_PIN), onFlowPulse, RISING);

  connectWiFi();
  mqtt.setServer(MQTT_HOST, MQTT_PORT);
  mqtt.setCallback(onMqttMessage);
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

  String clientId = String("hydrowatch-") + NODE_CODE + "-" + String(ESP.getChipId(), HEX);
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

  String pumpTopic = topic("pump");
  mqtt.subscribe(pumpTopic.c_str());
  Serial.print("MQTT subscribe ");
  Serial.println(pumpTopic);
}

void publishSensorReading()
{
  const float soil = readSoilMoisturePercent();
  const float tempC = readTemperatureC();
  const float phAir = readPh();
  const float flowRate = readFlowRateLpm();

  String payload = "{";
  payload += "\"node_code\":\"" + String(NODE_CODE) + "\",";
  payload += "\"soil_moisture\":" + String(soil, 1) + ",";
  payload += "\"temperature\":" + String(tempC, 1) + ",";
  payload += "\"ph\":" + String(phAir, 2) + ",";
  payload += "\"flow\":" + String(flowRate, 2);
  payload += "}";

  String sensorTopic = topic("sensor");
  bool ok = mqtt.publish(sensorTopic.c_str(), payload.c_str());

  Serial.print("MQTT publish ");
  Serial.print(sensorTopic);
  Serial.print(" ");
  Serial.println(ok ? "OK" : "GAGAL");
  Serial.println(payload);
}

void onMqttMessage(char* topicName, byte* payloadBytes, unsigned int length)
{
  String message;
  for (unsigned int i = 0; i < length; i++) {
    message += (char) payloadBytes[i];
  }

  Serial.print("MQTT message ");
  Serial.print(topicName);
  Serial.print(" ");
  Serial.println(message);

  bool pumpOn = message.indexOf("\"pump_on\":true") >= 0
    || message.indexOf("\"pump_status\":\"ON\"") >= 0;

  setPump(pumpOn);
}

String topic(const char* suffix)
{
  return String(MQTT_BASE_TOPIC) + "/" + NODE_CODE + "/" + suffix;
}

float readSoilMoisturePercent()
{
  int raw = analogRead(SOIL_PIN);
  float percent = (float)(SOIL_DRY_ADC - raw) * 100.0 / (float)(SOIL_DRY_ADC - SOIL_WET_ADC);
  return constrain(percent, 0.0, 100.0);
}

float readTemperatureC()
{
  return 28.0;
}

float readPh()
{
  return DEFAULT_PH;
}

float readFlowRateLpm()
{
  static unsigned long lastReadAt = millis();
  unsigned long now = millis();
  unsigned long elapsedMs = now - lastReadAt;

  if (elapsedMs == 0) {
    return 0.0;
  }

  noInterrupts();
  unsigned long pulses = flowPulseCount;
  flowPulseCount = 0;
  interrupts();

  lastReadAt = now;

  float liters = pulses / FLOW_PULSES_PER_LITER;
  return liters * 60000.0 / elapsedMs;
}

void setPump(bool on)
{
  digitalWrite(RELAY_PIN, relayLevel(on));
  Serial.print("Pompa: ");
  Serial.println(on ? "ON" : "OFF");
}

int relayLevel(bool on)
{
  if (RELAY_ACTIVE_LOW) {
    return on ? LOW : HIGH;
  }

  return on ? HIGH : LOW;
}

void setStatusLed(bool on)
{
  if (STATUS_LED_ACTIVE_LOW) {
    digitalWrite(STATUS_LED_PIN, on ? LOW : HIGH);
    return;
  }

  digitalWrite(STATUS_LED_PIN, on ? HIGH : LOW);
}
