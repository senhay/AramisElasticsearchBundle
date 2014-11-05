<?php

namespace Aramis\Bundle\ElasticsearchBundle\Tests\RabbitMq;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use PhpAmqpLib\Connection\AMQPConnection;

use Aramis\Bundle\ElasticsearchBundle\Services\IndexDataCollector;
use Aramis\Bundle\ElasticsearchBundle\RabbitMq\RabbitMqClient;
use Aramis\Bundle\ElasticsearchBundle\RabbitMq\RabbitMqRiver;

class RabbitMqRiverTest extends WebTestCase
{

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->entityManager = static::$kernel->getContainer()->get('doctrine')->getEntityManager();
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
    }

    public function testPublishDe()
    {
        $rabbitMqClient = new RabbitMqClient(
            $this->container->getParameter('rabbitmq')
        );

        $rabbitMqRiver = new RabbitMqRiver(
            $rabbitMqClient,
            $this->container->getParameter('elasticsearch'),
            $this->container->getParameter('river'),
            $this->container->getParameter('indexes'),
            $this->container->getParameter('elasticsearch.type'),
            static::$kernel->getContainer()->get('doctrine.dbal.default_connection')
        );

        //////// DISABLED BY HCH ///////
        ////////////////////////////////
        /// TODO : REPLACE QUERIES /////
        ////////////////////////////////

        // index offer
        //$locale = $this->entityManager->getRepository('AramisFaroDbBundle:Locale')->findOneBy(array('id' => 'de'));
        //$offer = $this->entityManager->getRepository('AramisFaroDbBundle:Offer')->findOneBy(array('locale' => $locale));
        //$rabbitMqRiver->index($offer);

        // try to retrieve it
        $indexes = $this->container->getParameter('indexes');
        $indexDataCollector = new IndexDataCollector(
            new \Elastica\Client(array('host' => $this->container->getParameter('elasticsearch.host'), 'port' => $this->container->getParameter('elasticsearch.port'))),
            $indexes[$offer->getLocale()->getId()],
            $this->container->getParameter('elasticsearch.type')
        );

        try {
            $document = $indexDataCollector->getOffer($offer->getId());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
        $this->assertEquals($document['id'], $offer->getId());
    }

    public function testDeleteDe()
    {
        $mapping = $this->container->getParameter('offer');
        $rabbitMqClient = new RabbitMqClient(
            $this->container->getParameter('rabbitmq')
        );

        $rabbitMqRiver = new RabbitMqRiver(
            $rabbitMqClient,
            $this->container->getParameter('elasticsearch'),
            $this->container->getParameter('river'),
            $this->container->getParameter('indexes'),
            $this->container->getParameter('elasticsearch.type'),
            static::$kernel->getContainer()->get('doctrine.dbal.default_connection')
        );

        //////// DISABLED BY HCH ///////
        ////////////////////////////////
        /// TODO : REPLACE QUERIES /////
        ////////////////////////////////
        // delete offer
        //$locale = $this->entityManager->getRepository('AramisFaroDbBundle:Locale')->findOneBy(array('id' => 'de'));
        //$offer = $this->entityManager->getRepository('AramisFaroDbBundle:Offer')->findOneBy(array('locale' => $locale));
        //$rabbitMqRiver->delete($offer);

        // try to retrieve it
        $config = $this->container->getParameter('elasticsearch');
        $indexes = $this->container->getParameter('indexes');
        $indexDataCollector = new IndexDataCollector(
            new \Elastica\Client(array('host' => $this->container->getParameter('elasticsearch.host'), 'port' => $this->container->getParameter('elasticsearch.port'))),
            $indexes[$offer->getLocale()->getId()],
            $this->container->getParameter('elasticsearch.type')
        );

        try {
            $document = $indexDataCollector->getOffer($offer->getId());
        } catch (\Exception $e) {
            $this->fail();
            return;
        }
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
