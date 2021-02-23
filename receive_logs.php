<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Create Connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

// Create channel
$channel = $connection->channel();

// Declare Exchange Type
$channel->exchange_declare('logs', 'fanout', false, false, false);

// Declare Queue with random name
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

// Binding Exchange to Queue
$channel->queue_bind($queue_name, 'logs');

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
    echo ' [x] ', $msg->body, "\n";
};

// Consuming the Message
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
