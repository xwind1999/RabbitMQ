<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class FibonacciRpcClient
{
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;

    public function __construct()
    {
        // Declare Connection
        $this->connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'guest',
            'guest'
        );
        $this->channel = $this->connection->channel();

        // Declare Queue
        list($this->callback_queue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false
        );

        // Consuming Message In Queue
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,

            // Callback Function
            array(
                $this,
                'onResponse'
            )
        );
    }

    public function onResponse($rep)
    {
        if ($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    /**
     * @param $n
     * @return int
     * @throws ErrorException
     */
    public function call($n)
    {
        $this->response = null;

        // Declare Correlation Id
        $this->corr_id = uniqid();

        // Declare Message
        $msg = new AMQPMessage(
            (string) $n,
            array(
                'correlation_id' => $this->corr_id,
                'reply_to' => $this->callback_queue
            )
        );

        // Publishing Message to rpc_queue
        $this->channel->basic_publish($msg, '', 'rpc_queue');

        while (!$this->response) {
            $this->channel->wait();
        }
        return intval($this->response);
    }
}

$fibonacci_rpc = new FibonacciRpcClient();
$response = $fibonacci_rpc->call(30);
echo ' [.] Got ', $response, "\n";