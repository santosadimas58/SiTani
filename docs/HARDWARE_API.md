# SiTani Hardware API

Base URL lokal Docker:

```text
http://localhost:8081
```

Jika ESP32/hardware berada di jaringan yang berbeda dari laptop/server, ganti `localhost` dengan IP server, misalnya:

```text
http://192.168.1.10:8081
```

## Health Check

Gunakan endpoint ini untuk memastikan hardware bisa menjangkau server.

```http
GET /api/health
```

Response sukses:

```json
{
  "success": true,
  "app": "SiTani",
  "time": "2026-04-27T14:00:00+07:00"
}
```

## Kirim Data Sensor

```http
POST /api/sensor
Content-Type: application/json
```

Payload utama:

```json
{
  "kode_node": "NODE-01",
  "kelembaban_tanah": 72.1,
  "suhu": 28.2,
  "ph_air": 6.9,
  "debit_air": 12.3
}
```

Nama field alternatif juga diterima agar lebih mudah disesuaikan dengan firmware:

```json
{
  "node_code": "NODE-01",
  "soil_1": 72.1,
  "soil_2": 68.4,
  "temperature": 28.2,
  "air_humidity": 81.5,
  "ph": 6.9,
  "flow": 12.3
}
```

Untuk Wemos/ESP8266 yang lebih mudah memakai query string, format ini juga diterima:

```http
GET /api/sensor?device=WEMOS-01&soil=72.1&temp_c=28.2&phAir=6.9&flowRate=12.3
```

Atau dengan form URL encoded:

```http
POST /api/sensor
Content-Type: application/x-www-form-urlencoded

device=WEMOS-01&soil=72.1&temp_c=28.2&phAir=6.9&flowRate=12.3
```

Response sukses:

```json
{
  "success": true,
  "message": "Data sensor tersimpan",
  "node": {
    "id": 1,
    "kode_node": "NODE-01",
    "nama_node": "Node Kebun-1"
  },
  "reading_id": 15,
  "received_at": "2026-04-27T14:00:00+07:00",
  "pump_status": "OFF",
  "pump_on": false
}
```

Catatan:

- `kode_node` harus sama dengan kode di menu Kelola Node.
- Saat data diterima, node otomatis ditandai `Aktif`.
- `pump_status` dan `pump_on` bisa langsung dipakai firmware untuk menyalakan atau mematikan relay.

## Contoh Wemos D1 Mini / ESP8266

Contoh ini memakai `ESP8266HTTPClient` dan query string agar sederhana.

```cpp
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>

const char* ssid = "NAMA_WIFI";
const char* password = "PASSWORD_WIFI";
const char* serverBaseUrl = "http://192.168.1.10:8081";
const char* nodeCode = "WEMOS-01";

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.print("IP Wemos: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  float soil = 72.1;
  float tempC = 28.2;
  float phAir = 6.9;
  float flowRate = 12.3;

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    String url = String(serverBaseUrl) + "/api/sensor"
      + "?device=" + nodeCode
      + "&soil=" + String(soil, 1)
      + "&temp_c=" + String(tempC, 1)
      + "&phAir=" + String(phAir, 1)
      + "&flowRate=" + String(flowRate, 1);

    http.begin(client, url);
    int httpCode = http.GET();

    if (httpCode > 0) {
      String payload = http.getString();
      Serial.println(payload);
      // Baca "pump_on":true atau "pump_status":"ON" dari payload
      // untuk mengontrol relay pompa.
    } else {
      Serial.printf("HTTP error: %s\n", http.errorToString(httpCode).c_str());
    }

    http.end();
  }

  delay(5000);
}
```

## Ambil Status Pompa

```http
GET /api/pump/NODE-01
```

Response:

```json
{
  "success": true,
  "kode_node": "NODE-01",
  "pump_status": "OFF",
  "pump_on": false
}
```

## Batas Validasi

- `kelembaban_tanah`: 0 sampai 100
- `suhu`: -40 sampai 100
- `ph_air`: 0 sampai 14
- `debit_air`: minimal 0

Jika `kode_node` tidak ditemukan, server mengembalikan HTTP 404.
