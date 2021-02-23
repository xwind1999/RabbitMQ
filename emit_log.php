<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create Connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

// Create channel
$channel = $connection->channel();

// Declare an exchange type
$channel->exchange_declare('logs', 'fanout', false, false, false);

$data = implode(' ', array_slice($argv, 1));
if (empty($data)) {
    $data = "info: Hello World!";
}

// Create Message
$msg = new AMQPMessage($data);

// Publish
// Send message to Exchange
$channel->basic_publish($msg, 'logs');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();