# HydroWatch MQTT Hardware Procedure

Dokumen ini menjelaskan jalur koneksi alat ke web HydroWatch memakai MQTT.

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
hydrowatch
```

Alat publish data sensor ke:

```text
hydrowatch/{KODE_NODE}/sensor
```

Contoh:

```text
hydrowatch/NODE-01/sensor
```

Payload JSON:

```json
{
  "node_code": "NODE-01",
  "soil_moisture": 72.1,
  "temperature": 28.2,
  "ph": 6.9,
  "flow": 12.3
}
```

Web publish status pompa ke:

```text
hydrowatch/{KODE_NODE}/pump
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
MQTT_CLIENT_ID=hydrowatch-web
MQTT_BASE_TOPIC=hydrowatch
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
6. Alat publish sensor ke `hydrowatch/NODE-01/sensor`.
7. Alat subscribe ke `hydrowatch/NODE-01/pump` untuk menerima perintah relay.
8. Buka web Monitoring untuk melihat data sensor masuk.
9. Buka Kontrol Pompa untuk mengubah status pompa dan cek relay di alat.

## Test Manual Broker

Dari komputer yang punya `mosquitto-clients`:

```bash
mosquitto_sub -h 127.0.0.1 -p 1883 -t 'hydrowatch/NODE-01/pump' -v
```

Di terminal lain:

```bash
mosquitto_pub -h 127.0.0.1 -p 1883 -t 'hydrowatch/NODE-01/sensor' -m '{"node_code":"NODE-01","soil_moisture":70,"temperature":28,"ph":6.8,"flow":12}'
```

Jika `NODE-01` sudah ada di database, data akan masuk ke riwayat sensor dan status pompa akan dipublish balik.
