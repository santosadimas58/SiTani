# HydroWatch Wemos D1 Mini Firmware

Firmware ini menghubungkan Wemos/LOLIN D1 mini ESP8266 ke web HydroWatch lewat API Laravel.

## Yang Perlu Diubah

Buka `HydroWatchESP32.ino` di Arduino IDE, lalu ubah:

```cpp
const char* WIFI_SSID = "NAMA_WIFI";
const char* WIFI_PASSWORD = "PASSWORD_WIFI";
const char* NODE_CODE = "NODE-01";
```

`SERVER_BASE_URL` sudah memakai IP laptop saat ini:

```cpp
const char* SERVER_BASE_URL = "http://10.120.37.81:8081";
```

Pastikan `NODE_CODE` sama dengan kode node di menu Kelola Node web.

## Pin Default

```text
Soil moisture : A0
pH            : nilai tetap 7.0
Flow sensor   : D5 / GPIO14
Relay pompa   : D6 / GPIO12
LED status    : LED_BUILTIN
```

Wemos D1 mini ESP8266 hanya punya satu input analog (`A0`). Firmware ini memakai `A0` untuk soil moisture, sehingga nilai pH sementara dikirim `7.0`. Jika pH juga ingin dibaca analog, perlu multiplexer analog atau modul ADC tambahan.

## Library Arduino IDE

Sketch ini memakai library bawaan core ESP8266:

- `ESP8266WiFi`
- `ESP8266HTTPClient`

Pilih board `LOLIN(WEMOS) D1 R2 & mini` dari Boards Manager ESP8266.

## Endpoint Yang Dipakai

Firmware mengirim:

```http
POST /api/sensor
Content-Type: application/json
```

Lalu membaca response `pump_on` dari web untuk mengontrol relay.

## Versi MQTT

Jika ingin memakai MQTT, gunakan sketch `HydroWatchMQTT.ino`.

Library tambahan Arduino IDE:

- `PubSubClient`

Topic default:

```text
Publish sensor  : hydrowatch/NODE-01/sensor
Subscribe pompa : hydrowatch/NODE-01/pump
```

Prosedur lengkap ada di `docs/MQTT_HARDWARE.md`.
