<?php

namespace App\Services;

use App\Models\Node;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttBroker
{
    public function publishPumpStatus(Node $node, string $status): void
    {
        $payload = [
            'kode_node' => $node->kode_node,
            'pump_status' => $status,
            'pump_on' => $status === 'ON',
            'updated_at' => now()->toIso8601String(),
        ];

        $this->publish($this->pumpTopic($node->kode_node), $payload);
    }

    public function publish(string $topic, array $payload, bool $retain = true): void
    {
        $mqtt = new MqttClient(
            config('mqtt.host'),
            config('mqtt.port'),
            config('mqtt.client_id').'-publisher-'.getmypid()
        );

        $mqtt->connect($this->connectionSettings(), true);
        $mqtt->publish($topic, json_encode($payload), config('mqtt.qos'), $retain);
        $mqtt->disconnect();
    }

    public function sensorTopicFilter(): string
    {
        return config('mqtt.base_topic').'/+/sensor';
    }

    public function pumpTopic(string $kodeNode): string
    {
        return config('mqtt.base_topic').'/'.$kodeNode.'/pump';
    }

    public function connectionSettings(bool $reconnectAutomatically = false): ConnectionSettings
    {
        return (new ConnectionSettings)
            ->setUsername(config('mqtt.username'))
            ->setPassword(config('mqtt.password'))
            ->setConnectTimeout(2)
            ->setSocketTimeout(2)
            ->setKeepAliveInterval(30)
            ->setReconnectAutomatically($reconnectAutomatically);
    }
}
