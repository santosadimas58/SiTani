# SiTani Wemos D1 Mini Firmware

Firmware ini menghubungkan Wemos/LOLIN D1 mini ESP8266 ke web SiTani lewat API Laravel.

## Yang Perlu Diubah

Buka `SiTaniHTTP/SiTaniHTTP.ino` di Arduino IDE, lalu ubah:

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

## Node Sensor MQTT

Untuk node dengan DHT22 dan soil moisture, buka sketch
`SiTaniMQTT/SiTaniMQTT.ino` di Arduino IDE.

Sketch HTTP dan MQTT ditempatkan dalam folder terpisah karena Arduino IDE
akan menggabungkan semua file `.ino` yang berada dalam satu folder.

Library tambahan Arduino IDE:

- `PubSubClient`

Topic default:

```text
Publish sensor : sitani/NODE-02/sensor
```

Wiring node sensor:

```text
DHT22 DATA -> D2 / GPIO4
CD4051 SIG -> A0
CD4051 S0  -> D5 / GPIO14
CD4051 S1  -> D6 / GPIO12
CD4051 S2  -> D7 / GPIO13
Soil 1 AO  -> CD4051 channel 0 / C0
Soil 2 AO  -> CD4051 channel 1 / C1
```

Kalibrasi raw ADC soil:

```text
300 -> tanah basah
550 -> tanah lembap
800 -> tanah kering
```

Nilai yang tampil di web tetap satu `Kelembaban Tanah`, dihitung dari rata-rata
dua soil sensor.

## Gateway Relay MQTT

Untuk memakai Wemos terpisah yang hanya mengendalikan relay pompa, buka:

```text
SiTaniRelayGateway/SiTaniRelayGateway.ino
```

Gateway tidak mengirim data sensor. Gateway subscribe perintah pompa dari web
dan telemetry sensor untuk kontrol otomatis:

```text
sitani/NODE-02/pump
sitani/NODE-02/sensor
```

Ubah `CONTROLLED_NODE` sesuai node yang pompanya akan dikendalikan. Relay default
terhubung ke `D6 / GPIO12` dan diatur `OFF` saat gateway mulai menyala.

Kontrol otomatis gateway:

```text
soil_raw_avg >= 800 -> relay ON / motor nyala
soil_raw_avg <= 550 -> relay OFF / motor mati
```

Prosedur lengkap ada di `docs/MQTT_HARDWARE.md`.
