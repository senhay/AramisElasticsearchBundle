<?php

namespace Aramis\Bundle\ElasticsearchBundle\RabbitMq;

use PhpAmqpLib\Connection\AMQPConnection;

class RabbitMqClient
{
    private $connection = null;

    public function __construct($config)
    {
        $this->connection = new AMQPConnection(
            $config['host'],
            $config['port'],
            $config['username'],
            $config['password'],
            $config['vhost']
        );
    }


    /**
     * @return AMQPConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
