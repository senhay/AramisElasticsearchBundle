<?php

namespace Aramis\Bundle\ElasticsearchBundle\Tests\RabbitMq;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PhpAmqpLib\Connection\AMQPConnection;
use Aramis\Bundle\ElasticsearchBundle\RabbitMq\RabbitMqClient;

class RabbitMqTest extends WebTestCase
{
    public function testConnection()
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $rabbitMqClient = new RabbitMqClient(
            $container->getParameter('rabbitmq')
        );

        $this->assertInstanceOf('PhpAmqpLib\Connection\AMQPConnection', $rabbitMqClient->getConnection());
    }
}
