<?php

namespace App\Console\Commands;

use App\Services\MqttBroker;
use App\Services\SensorReadingIngestor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use PhpMqtt\Client\MqttClient;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen';

    protected $description = 'Subscribe data sensor MQTT dan publish status pompa balik ke alat.';

    public function handle(SensorReadingIngestor $ingestor, MqttBroker $broker): int
    {
        $clientId = config('mqtt.client_id').'-subscriber-'.getmypid();
        $mqtt = new MqttClient(config('mqtt.host'), config('mqtt.port'), $clientId);

        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            pcntl_signal(SIGINT, fn () => $mqtt->interrupt());
            pcntl_signal(SIGTERM, fn () => $mqtt->interrupt());
        }

        $topic = $broker->sensorTopicFilter();

        $this->info('Connecting to MQTT broker '.config('mqtt.host').':'.config('mqtt.port'));
        $mqtt->connect($broker->connectionSettings(), true);

        $this->info("Subscribing to {$topic}");
        $mqtt->subscribe($topic, function (string $topic, string $message) use ($ingestor, $broker, $mqtt) {
            $payload = json_decode($message, true);

            if (! is_array($payload)) {
                Log::warning('Invalid MQTT sensor payload', ['topic' => $topic, 'message' => $message]);
                $this->warn("Invalid JSON on {$topic}");

                return;
            }

            try {
                $result = $ingestor->ingest($payload);
            } catch (ValidationException $exception) {
                Log::warning('Invalid MQTT sensor data', ['topic' => $topic, 'errors' => $exception->errors()]);
                $this->warn("Validation failed on {$topic}");

                return;
            }

            if (! $result['success']) {
                Log::warning('MQTT sensor node not found', ['topic' => $topic, 'payload' => $payload]);
                $this->warn("Node not found on {$topic}");

                return;
            }

            $node = $result['node'];
            $responseTopic = $broker->pumpTopic($node->kode_node);
            $response = [
                'kode_node' => $node->kode_node,
                'pump_status' => $result['pump_status'],
                'pump_on' => $result['pump_on'],
                'reading_id' => $result['reading']->id,
                'received_at' => $result['reading']->created_at->toIso8601String(),
            ];

            $mqtt->publish($responseTopic, json_encode($response), config('mqtt.qos'), true);
            $this->line("Saved reading {$result['reading']->id} from {$node->kode_node}; published {$responseTopic}");
        }, config('mqtt.qos'));

        $mqtt->loop(true);
        $mqtt->disconnect();

        return self::SUCCESS;
    }
}
