#include <ESP8266WiFi.h>
#include <PubSubClient.h>

/*
  SiTani MQTT relay gateway for Wemos D1 mini / ESP8266.

  This gateway controls the pump relay for one SiTani node.
  It also listens to sensor telemetry and turns the pump on automatically
  when soil raw ADC is dry.

  Subscribe:
    sitani/NODE-02/pump
    sitani/NODE-02/sensor
*/

const char* WIFI_SSID = "mukaroms";
const char* WIFI_PASSWORD = "mukarom01";

const char* MQTT_HOST = "192.168.1.21";
const int MQTT_PORT = 1883;
const char* MQTT_USERNAME = "";
const char* MQTT_PASSWORD = "";

const char* CONTROLLED_NODE = "NODE-02";
const char* MQTT_BASE_TOPIC = "sitani";

const int RELAY_PIN = D6;
const int STATUS_LED_PIN = LED_BUILTIN;
const bool RELAY_ACTIVE_LOW = true;
const bool STATUS_LED_ACTIVE_LOW = true;

const unsigned long WIFI_RECONNECT_MS = 10000;
const unsigned long MQTT_RECONNECT_MS = 5000;

const bool AUTO_DRY_CONTROL = true;
const int SOIL_MOIST_ADC = 550;
const int SOIL_DRY_ADC = 800;

WiFiClient wifiClient;
PubSubClient mqtt(wifiClient);

unsigned long lastWiFiReconnectAt = 0;
unsigned long lastMqttReconnectAt = 0;
bool pumpOn = false;

void setOutput(int pin, bool activeLow, bool on)
{
  digitalWrite(pin, activeLow ? (on ? LOW : HIGH) : (on ? HIGH : LOW));
}

void setPump(bool on)
{
  pumpOn = on;
  setOutput(RELAY_PIN, RELAY_ACTIVE_LOW, on);

  Serial.print("Pompa ");
  Serial.println(on ? "ON" : "OFF");
}

void setStatusLed(bool on)
{
  setOutput(STATUS_LED_PIN, STATUS_LED_ACTIVE_LOW, on);
}

String pumpTopic()
{
  return String(MQTT_BASE_TOPIC) + "/" + CONTROLLED_NODE + "/pump";
}

String sensorTopic()
{
  return String(MQTT_BASE_TOPIC) + "/" + CONTROLLED_NODE + "/sensor";
}

void handlePumpCommand(const String& message)
{
  const bool commandOn = message.indexOf("\"pump_on\":true") >= 0
    || message.indexOf("\"pump_status\":\"ON\"") >= 0;
  const bool commandOff = message.indexOf("\"pump_on\":false") >= 0
    || message.indexOf("\"pump_status\":\"OFF\"") >= 0;

  if (commandOn) {
    setPump(true);
  } else if (commandOff) {
    setPump(false);
  } else {
    Serial.println("Perintah pompa tidak dikenali; kondisi relay tidak diubah.");
  }
}

void handleSensorTelemetry(const String& message)
{
  if (!AUTO_DRY_CONTROL) {
    return;
  }

  float rawAvg = extractJsonNumber(message, "soil_raw_avg");

  if (isnan(rawAvg)) {
    float raw1 = extractJsonNumber(message, "soil_raw_1");
    float raw2 = extractJsonNumber(message, "soil_raw_2");

    if (!isnan(raw1) && !isnan(raw2)) {
      rawAvg = (raw1 + raw2) / 2.0;
    }
  }

  if (isnan(rawAvg)) {
    Serial.println("Telemetry tidak punya soil_raw_avg; kontrol otomatis dilewati.");
    return;
  }

  Serial.print("Soil raw avg: ");
  Serial.println(rawAvg, 0);

  if (rawAvg >= SOIL_DRY_ADC) {
    Serial.println("Tanah kering, pompa ON.");
    setPump(true);
    return;
  }

  if (rawAvg <= SOIL_MOIST_ADC) {
    Serial.println("Tanah sudah lembap/basah, pompa OFF.");
    setPump(false);
    return;
  }

  Serial.println("Tanah mulai kering, status pompa dipertahankan.");
}

float extractJsonNumber(const String& json, const char* key)
{
  String pattern = String("\"") + key + "\":";
  int start = json.indexOf(pattern);

  if (start < 0) {
    return NAN;
  }

  start += pattern.length();

  while (start < (int) json.length() && json[start] == ' ') {
    start++;
  }

  int end = start;
  while (end < (int) json.length()) {
    char c = json[end];
    if ((c >= '0' && c <= '9') || c == '-' || c == '.') {
      end++;
    } else {
      break;
    }
  }

  if (end == start) {
    return NAN;
  }

  return json.substring(start, end).toFloat();
}

void onMqttMessage(char* topicName, byte* payloadBytes, unsigned int length)
{
  String message;
  message.reserve(length);

  for (unsigned int i = 0; i < length; i++) {
    message += (char) payloadBytes[i];
  }

  String topic = String(topicName);

  Serial.print("MQTT message ");
  Serial.print(topic);
  Serial.print(": ");
  Serial.println(message);

  if (topic == sensorTopic()) {
    handleSensorTelemetry(message);
  } else if (topic == pumpTopic()) {
    handlePumpCommand(message);
  }
}

void connectWiFi()
{
  Serial.print("Menghubungkan WiFi: ");
  Serial.println(WIFI_SSID);

  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
}

void ensureWiFi()
{
  if (WiFi.status() == WL_CONNECTED) {
    return;
  }

  setStatusLed(false);

  if (millis() - lastWiFiReconnectAt < WIFI_RECONNECT_MS) {
    return;
  }

  lastWiFiReconnectAt = millis();
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

  String clientId = String("sitani-relay-") + CONTROLLED_NODE + "-" + String(ESP.getChipId(), HEX);
  Serial.print("Menghubungkan MQTT: ");
  Serial.print(MQTT_HOST);
  Serial.print(":");
  Serial.println(MQTT_PORT);

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

  String pump = pumpTopic();
  String sensor = sensorTopic();
  mqtt.subscribe(pump.c_str());
  mqtt.subscribe(sensor.c_str());
  setStatusLed(true);

  Serial.print("MQTT subscribe ");
  Serial.println(pump);
  Serial.print("MQTT subscribe ");
  Serial.println(sensor);
}

void setup()
{
  Serial.begin(115200);
  delay(200);

  pinMode(RELAY_PIN, OUTPUT);
  pinMode(STATUS_LED_PIN, OUTPUT);

  setPump(false);
  setStatusLed(false);

  mqtt.setServer(MQTT_HOST, MQTT_PORT);
  mqtt.setCallback(onMqttMessage);
  connectWiFi();
}

void loop()
{
  ensureWiFi();
  ensureMqtt();

  if (mqtt.connected()) {
    mqtt.loop();
  }

  delay(10);
}
