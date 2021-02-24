<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

// Create Connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare Exchange
$channel->exchange_declare('direct_logs', 'direct', false, false, false);

// Create Queue
list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

$severities = array_slice($argv, 1);

if (empty($severities)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [info] [warning] [error]\n");
    echo("Boom");
    exit(1);
}

// Mapping Exchange To Queue
foreach ($severities as $severity) {
    echo($severity.PHP_EOL);
    $channel->queue_bind($queue_name, 'direct_logs', $severity);
}

echo " [*] Waiting for logs. To exit press CTRL+C\n";

// Callback function
$callback = function ($msg) {
    echo ' [x] ', $msg->delivery_info['routing_key'], ' : ', $msg->body, "\n";
};

// Consuming Message
$channel->basic_consume($queue_name, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();
