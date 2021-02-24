<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Create Connection
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare Exchange
$channel->exchange_declare('direct_logs', 'direct', false, false, false);

$severity = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'info';

$data = implode(' ', array_slice($argv, 2));
if (empty($data)) {
    $data = "Hello World!";
}

// Create Message
$msg = new AMQPMessage($data);

// Publishing Message
$channel->basic_publish($msg, 'direct_logs', $severity);

echo ' [x] Sent ', $severity, ' : ', $data, "\n";

$channel->close();
$connection->close();