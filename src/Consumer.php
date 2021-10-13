<?php

namespace KafkaBus;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use Illuminate\Contracts\Cache\Repository as CacheContract;

class Consumer
{
    /**
     * @var KafkaConsumer
     */
    protected $consumer;
    /**
     * @var bool
     */
    protected $autocommit = false;
    /**
     * @var CacheContract
     */
    protected $cache;
    /**
     * @var int
     */
    protected $memoryLimit;
    /**
     * @var int
     */
    private $timeout;

    /**
     * @param CacheContract $cache
     */
    public function __construct(CacheContract $cache)
    {
        $this->cache = $cache;
        $this->consumer = new KafkaConsumer($this->getConfig());
        $this->memoryLimit = config('kafka-bus.memory_limit', 128);
        $this->timeout = config('kafka-bus.timeout', 120000);
        $this->autocommit = config('kafka-bus.auto_commit');
    }

    /**
     * @param int $timeout
     * @param KafkaHandler $handler
     * @throws Exception
     */
    public function consume(KafkaHandler $handler, int $timeout = 0)
    {
        $lastRestart = $this->getLastRestart();

        $this->consumer->subscribe($handler->getTopics());

        while (true) {
            $message = $this->consumer->consume($timeout ?: $this->timeout);

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    if ($message->payload) {
                        if (!$handler->process($message)) {
                            break;
                        }
                    }

                    if (!$this->autocommit) {
                        $this->consumer->commit($message);
                    }
                    break;
                default:
                    $handler->error(new Error($message->err, $message->errstr(), $message));
                    break;
            }

            $this->checkRestart($lastRestart);
        }
    }

    /**
     * @return int
     */
    protected function getLastRestart(): int
    {
        if ($this->cache) {
            try {
                return $this->cache->get('illuminate:queue:restart');
            } catch (InvalidArgumentException $e) {
                return 0;
            }
        }

        return 0;
    }

    /**
     * @param int $lastRestart
     */
    protected function checkRestart(int $lastRestart)
    {
        if ($this->memoryExceeded()) {
            $this->stop(12);
        }
        if ($this->getLastRestart() !== $lastRestart) {
            $this->stop();
        }
    }

    /**
     * @return bool
     */
    public function memoryExceeded(): bool
    {
        return (memory_get_usage(true) / 1024 / 1024) >= $this->memoryLimit;
    }

    /**
     * @param int $status
     */
    public function stop(int $status = 0)
    {
        exit($status);
    }

    /**
     * @return Conf
     */
    private function getConfig(): Conf
    {
        $conf = new Conf();

        $conf->set('group.id', config('kafka-bus.group_id'));
        $conf->set('metadata.broker.list', config('kafka-bus.brokers'));
        $conf->set('auto.offset.reset', config('kafka-bus.auto_offset_reset'));
        $conf->set('enable.auto.commit', $this->autocommit ? 'true' : 'false');

        if (config('kafka-bus.security_protocol') === 'SASL_SSL') {
            $conf->set('security.protocol', config('kafka-bus.security_protocol'));
            $conf->set('sasl.mechanisms', config('kafka-bus.sasl.mechanisms'));
            $conf->set('sasl.password', config('kafka-bus.sasl.password'));
            $conf->set('sasl.username', config('kafka-bus.sasl.username'));
            $conf->set('ssl.certificate.location', config('kafka-bus.ssl.certificate_location'));
            $conf->set('ssl.ca.location', config('kafka-bus.ssl.ca_location'));
        }

        return $conf;
    }
}
