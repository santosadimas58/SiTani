/*
  Wemos sensor node: DHT + capacitive soil moisture sensor.

  Board      : Wemos D1 mini / ESP8266
  DHT pin    : D2 / GPIO4
  Soil pin   : A0
  Transport  : ESP-NOW broadcast to gateway

  Libraries:
  - DHT sensor library by Adafruit
  - Adafruit Unified Sensor
*/

#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <DHT.h>

extern "C" {
  #include <espnow.h>
  #include <user_interface.h>
}

#ifndef D2
#define D2 4
#endif

const uint8_t DHT_PIN = D2;
const uint8_t DHT_TYPE = DHT22;        // Change to DHT11 if you use DHT11.
const uint8_t WSN_CHANNEL = 6;         // Must match gateway/router WiFi channel.
const unsigned long SEND_INTERVAL_MS = 10000;

// Calibrate these after reading Serial Monitor values in dry and wet soil.
const int SOIL_DRY_RAW = 760;
const int SOIL_WET_RAW = 330;

// Broadcast is easiest for one-way sensor-to-gateway WSN.
uint8_t gatewayMac[] = {0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF};

DHT dht(DHT_PIN, DHT_TYPE);
unsigned long lastSendAt = 0;
uint32_t packetId = 0;

struct SensorPacket {
  uint32_t magic;
  uint8_t version;
  uint32_t packetId;
  char nodeId[16];
  float temperatureC;
  float humidityPercent;
  uint16_t soilRaw;
  uint8_t soilPercent;
};

int soilPercentFromRaw(int raw) {
  int percent = map(raw, SOIL_DRY_RAW, SOIL_WET_RAW, 0, 100);
  return constrain(percent, 0, 100);
}

void onDataSent(uint8_t *macAddr, uint8_t status) {
  Serial.print(F("ESP-NOW send status: "));
  Serial.println(status == 0 ? F("OK") : F("FAILED"));
}

void setupEspNow() {
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  wifi_set_channel(WSN_CHANNEL);

  if (esp_now_init() != 0) {
    Serial.println(F("ESP-NOW init failed. Restarting..."));
    delay(1000);
    ESP.restart();
  }

  esp_now_set_self_role(ESP_NOW_ROLE_CONTROLLER);
  esp_now_register_send_cb(onDataSent);

  if (esp_now_add_peer(gatewayMac, ESP_NOW_ROLE_SLAVE, WSN_CHANNEL, NULL, 0) != 0) {
    Serial.println(F("Failed to add ESP-NOW gateway peer."));
  }
}

void sendSensorPacket() {
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  int soilRaw = analogRead(A0);
  int soilPercent = soilPercentFromRaw(soilRaw);

  if (isnan(temperature) || isnan(humidity)) {
    Serial.println(F("DHT read failed, packet skipped."));
    return;
  }

  SensorPacket packet = {};
  packet.magic = 0x4D454B41; // "MEKA"
  packet.version = 1;
  packet.packetId = ++packetId;
  strncpy(packet.nodeId, "node-01", sizeof(packet.nodeId) - 1);
  packet.temperatureC = temperature;
  packet.humidityPercent = humidity;
  packet.soilRaw = soilRaw;
  packet.soilPercent = soilPercent;

  Serial.print(F("Packet #"));
  Serial.print(packet.packetId);
  Serial.print(F(" | T: "));
  Serial.print(packet.temperatureC, 1);
  Serial.print(F(" C | RH: "));
  Serial.print(packet.humidityPercent, 1);
  Serial.print(F("% | Soil raw: "));
  Serial.print(packet.soilRaw);
  Serial.print(F(" | Soil: "));
  Serial.print(packet.soilPercent);
  Serial.println(F("%"));

  esp_now_send(gatewayMac, reinterpret_cast<uint8_t *>(&packet), sizeof(packet));
}

void setup() {
  Serial.begin(115200);
  delay(200);

  dht.begin();
  setupEspNow();

  Serial.println(F("Wemos sensor node ready."));
  Serial.print(F("Node MAC: "));
  Serial.println(WiFi.macAddress());
}

void loop() {
  unsigned long now = millis();
  if (now - lastSendAt >= SEND_INTERVAL_MS) {
    lastSendAt = now;
    sendSensorPacket();
  }
}
