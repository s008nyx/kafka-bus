<?php

namespace KafkaBus;

use RdKafka\Message;

/**
 * Kafka message processing handler
 */
interface KafkaHandler
{
    /**
     * Topics list
     * @return array
     */
    public function getTopics(): array;

    /**
     * Processing success message
     * @param Message $message
     * @return bool
     */
    public function process(Message $message): bool;

    /**
     * Processing fail message
     * @param Error $error
     * @return bool
     */
    public function error(Error $error): bool;
}