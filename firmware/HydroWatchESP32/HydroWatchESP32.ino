#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

/*
  HydroWatch Wemos D1 mini firmware

  Kirim data sensor ke Laravel:
    POST http://10.120.37.81:8081/api/sensor

  Response web berisi pump_on true/false dan dipakai untuk relay pompa.
*/

const char* WIFI_SSID = "NAMA_WIFI";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";

const char* SERVER_BASE_URL = "http://10.120.37.81:8081";
const char* NODE_CODE = "NODE-01";

const unsigned long SEND_INTERVAL_MS = 5000;
const unsigned long WIFI_RECONNECT_MS = 10000;

const int SOIL_PIN = A0;
const int FLOW_PIN = D5;
const int RELAY_PIN = D6;
const int STATUS_LED_PIN = LED_BUILTIN;

const bool RELAY_ACTIVE_LOW = true;
const bool STATUS_LED_ACTIVE_LOW = true;

// Kalibrasi soil moisture. Ubah sesuai nilai ADC sensor saat kering dan basah.
const int SOIL_DRY_ADC = 850;
const int SOIL_WET_ADC = 350;

// ESP8266/Wemos D1 mini hanya punya satu input analog (A0).
// Default firmware ini memakai A0 untuk soil moisture, jadi pH dikirim nilai tetap.
const float DEFAULT_PH = 7.0;

// Umum untuk flow sensor YF-S201: sekitar 450 pulse per liter.
const float FLOW_PULSES_PER_LITER = 450.0;

volatile unsigned long flowPulseCount = 0;
unsigned long lastSendAt = 0;
unsigned long lastReconnectAt = 0;

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
}

void loop()
{
  ensureWiFi();

  const unsigned long now = millis();
  if (now - lastSendAt >= SEND_INTERVAL_MS) {
    lastSendAt = now;
    sendSensorReading();
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

  if (millis() - lastReconnectAt >= WIFI_RECONNECT_MS) {
    lastReconnectAt = millis();
    connectWiFi();
  }
}

void sendSensorReading()
{
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("Lewati kirim data: WiFi belum tersambung.");
    return;
  }

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

  HTTPClient http;
  WiFiClient client;
  String url = String(SERVER_BASE_URL) + "/api/sensor";

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(8000);

  Serial.print("POST ");
  Serial.println(url);
  Serial.println(payload);

  int httpCode = http.POST(payload);
  String response = http.getString();

  Serial.print("HTTP ");
  Serial.println(httpCode);
  Serial.println(response);

  if (httpCode == 200 || httpCode == 201) {
    bool pumpOn = response.indexOf("\"pump_on\":true") >= 0
      || response.indexOf("\"pump_status\":\"ON\"") >= 0;
    setPump(pumpOn);
  }

  http.end();
}

float readSoilMoisturePercent()
{
  int raw = analogRead(SOIL_PIN);
  float percent = (float)(SOIL_DRY_ADC - raw) * 100.0 / (float)(SOIL_DRY_ADC - SOIL_WET_ADC);
  return constrain(percent, 0.0, 100.0);
}

float readTemperatureC()
{
  // Ganti fungsi ini jika memakai DHT11/DHT22/DS18B20.
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
