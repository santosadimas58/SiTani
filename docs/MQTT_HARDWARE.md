# SiTani MQTT Hardware Procedure

Dokumen ini menjelaskan jalur koneksi alat ke web SiTani memakai MQTT.

## Arsitektur

```text
Alat/Wemos/ESP -> MQTT broker Mosquitto -> Laravel mqtt:listen -> Database
Laravel/Web kontrol pompa -> MQTT broker Mosquitto -> Alat/Wemos/ESP
```

Jalur HTTP lama tetap tersedia. MQTT adalah jalur tambahan.

## Broker MQTT

Docker Compose sudah menambahkan service:

```text
hw-mqtt           : broker Mosquitto, port host 1883
hw-mqtt-listener  : worker Laravel untuk subscribe data sensor
```

Jalankan stack:

```bash
docker compose up -d --build
```

Cek service:

```bash
docker compose ps
```

Jika alat berada di jaringan WiFi yang sama dengan laptop/server, set broker host di firmware ke IP laptop/server, contoh:

```cpp
const char* MQTT_HOST = "192.168.1.10";
const int MQTT_PORT = 1883;
```

Jangan pakai `localhost` di firmware, karena `localhost` berarti alat itu sendiri.

## Topic

Base topic default:

```text
sitani
```

Alat publish data sensor ke:

```text
sitani/{KODE_NODE}/sensor
```

Contoh:

```text
sitani/NODE-01/sensor
```

Payload JSON:

```json
{
  "node_code": "NODE-01",
  "soil_1": 72.1,
  "soil_2": 68.4,
  "temperature": 28.2,
  "air_humidity": 81.5
}
```

Web publish status pompa ke:

```text
sitani/{KODE_NODE}/pump
```

Contoh payload:

```json
{
  "kode_node": "NODE-01",
  "pump_status": "ON",
  "pump_on": true,
  "updated_at": "2026-05-27T18:00:00+07:00"
}
```

Saat Laravel menerima data sensor via MQTT, Laravel juga publish balasan status pompa terakhir ke topic pump node tersebut.

## Konfigurasi Laravel

Variabel `.env` yang tersedia:

```env
MQTT_HOST=mqtt
MQTT_PORT=1883
MQTT_USERNAME=null
MQTT_PASSWORD=null
MQTT_CLIENT_ID=sitani-web
MQTT_BASE_TOPIC=sitani
MQTT_QOS=0
```

Di Docker, `MQTT_HOST` sudah diarahkan ke service `mqtt`. Untuk menjalankan listener tanpa Docker, pakai:

```bash
php artisan mqtt:listen
```

## Prosedur Koneksi Alat

1. Pastikan node sudah dibuat di menu Kelola Node, misalnya `NODE-01`.
2. Pastikan server dan alat berada di jaringan yang sama.
3. Jalankan Docker Compose sampai `hw-mqtt` dan `hw-mqtt-listener` aktif.
4. Upload firmware MQTT ke alat.
5. Di firmware, isi `WIFI_SSID`, `WIFI_PASSWORD`, `MQTT_HOST`, dan `NODE_CODE`.
6. Alat publish sensor ke `sitani/NODE-01/sensor`.
7. Alat subscribe ke `sitani/NODE-01/pump` untuk menerima perintah relay.
8. Buka web Monitoring untuk melihat data sensor masuk.
9. Buka Kontrol Pompa untuk mengubah status pompa dan cek relay di alat.

## Gateway Relay Terpisah

Jika sensor dan relay menggunakan Wemos yang berbeda, upload firmware:

```text
firmware/SiTaniESP32/SiTaniRelayGateway/SiTaniRelayGateway.ino
```

Atur WiFi, alamat broker, dan node yang dikendalikan:

```cpp
const char* MQTT_HOST = "192.168.1.21";
const char* CONTROLLED_NODE = "NODE-02";
```

Wiring relay default:

```text
Relay IN  -> D6 / GPIO12
Relay VCC -> 5V atau sesuai spesifikasi modul relay
Relay GND -> GND Wemos
```

Gateway subscribe ke `sitani/NODE-02/pump`. Karena perintah dari web
dipublish dengan retain, gateway akan menerima status pompa terakhir setelah
terhubung kembali ke MQTT.

Node sensor terpisah memakai:

```text
firmware/SiTaniESP32/SiTaniMQTT/SiTaniMQTT.ino
```

Node tersebut membaca DHT22 pada `D2 / GPIO4` dan dua sensor soil melalui
multiplexer analog CD4051/74HC4051. ESP8266 hanya punya satu input analog `A0`,
jadi dua soil tidak bisa dibaca langsung tanpa multiplexer atau ADC eksternal.

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

Payload MQTT node sensor:

```json
{
  "node_code": "NODE-02",
  "soil_1": 72.1,
  "soil_2": 68.4,
  "soil_raw_1": 440,
  "soil_raw_2": 500,
  "soil_raw_avg": 470,
  "soil_status": "lembap",
  "temperature": 28.2,
  "air_humidity": 81.5
}
```

Web hanya menampilkan satu nilai `Kelembaban Tanah`. Nilai itu dihitung dari
rata-rata `soil_1` dan `soil_2`, sementara nilai mentah kedua sensor tetap
disimpan untuk kebutuhan kalibrasi atau pengecekan.

Kalibrasi raw ADC soil yang dipakai firmware:

```text
300 -> tanah basah
550 -> tanah lembap
800 -> tanah kering
```

Gateway relay juga subscribe ke `sitani/NODE-02/sensor`:

```text
soil_raw_avg >= 800 -> relay ON / motor nyala
soil_raw_avg <= 550 -> relay OFF / motor mati
551..799          -> status relay dipertahankan
```

## Test Manual Broker

Dari komputer yang punya `mosquitto-clients`:

```bash
mosquitto_sub -h 127.0.0.1 -p 1883 -t 'sitani/NODE-01/pump' -v
```

Di terminal lain:

```bash
mosquitto_pub -h 127.0.0.1 -p 1883 -t 'sitani/NODE-01/sensor' -m '{"node_code":"NODE-01","soil_1":70,"soil_2":65,"temperature":28,"air_humidity":81}'
```

Jika `NODE-01` sudah ada di database, data akan masuk ke riwayat sensor dan status pompa akan dipublish balik.
