# kafka-bus

## Installation

```dotenv
KAFKA_BROKERS="kafka-node01:9093,kafka-node02:9093"
KAFKA_AUTOCOMMIT=true
KAFKA_GROUP_ID="myGroup"
KAFKA_SECURITY_PROTOCOL=SASL_SSL
KAFKA_SASL_MECHANISMS=SCRAM-SHA-512
KAFKA_SASL_PASSWORD=password
KAFKA_SASL_USERNAME=username
KAFKA_SSL_CA_LOCATION=/path/to/ca.crt
KAFKA_SSL_CERTIFICATE_LOCATION=/path/to/chain.crt
```

## Usage
###Create Handler
MyHandler.php

```php
<?php

use KafkaBus\Error;
use KafkaBus\KafkaHandler;
use RdKafka\Message;

class MyHandler implements KafkaHandler
{
    /**
     * Topics list
     * @return array
     */
    public function getTopics(): array
    {
        return ['myTopic'];
    }

    /**
     * Processing success message
     * @param Message $message
     * @return bool
     */
    public function process(Message $message): bool
    {
        // Do something
    }

    /**
     * Processing fail message
     * @param Error $error
     * @return bool
     */
    public function error(Error $error): bool
    {
        // Do something
    }
}

```

###Create artisan command
KafkaCommand.php
```php
<?php

namespace App\Console\Commands;

use KafkaBus\Consumer;
use Illuminate\Console\Command;

class KafkaConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consume';

    /**
     * @param Consumer $consumer
     */
    public function handle(Consumer $consumer)
    {
        try {
            $consumer->consume(new MyHandler());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
```

###Start command
```shell
php artisan kafka:consume
```