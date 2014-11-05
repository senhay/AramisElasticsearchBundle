<?php

namespace Aramis\Bundle\ElasticsearchBundle\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;
use Aramis\Bundle\ElasticsearchBundle\Query\OfferQuery;

class RabbitMqRiver
{
    const MESSAGE_INDEX  = 'index';
    const MESSAGE_DELETE = 'delete';

    /**
     * @param $client              RabbitMqClient
     * @param $elasticSearchConfig Array containing ElasticSearch index conf and rabbitMQ river conf
     * @param $databaseConnection  Database connection
     */
    public function __construct(RabbitMqClient $client, $elasticSearchConfig, $riverConfig, $indexes, $indexType, $databaseConnection, $tools)
    {
        $this->connection         = $client->getConnection();
        $this->queue              = $riverConfig['queue'];
        $this->exchange           = $riverConfig['exchange'];
        $this->routingKey         = $riverConfig['routing_key'];
        $this->exchangeType       = $riverConfig['exchange_type'];
        $this->channel            = $this->connection->channel();
        $this->indexes            = $indexes;
        $this->indexType          = $indexType;
        $this->databaseConnection = $databaseConnection;
        // boite à outils nécessaire lors de l'indexation
        $this->tools              = $tools;

        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->exchange_declare($this->exchange, $this->exchangeType, false, true, false);
        $this->channel->queue_bind($this->queue, $this->exchange, $this->routingKey);
    }

    /**
     * Update given offers
     *
     * @param $offers array|Offer
     */
    public function update($offers)
    {
        if (!is_array($offers)) {
            $offers = array($offers);
        }

        foreach ($offers as $offer) {
            if ($offer->getIsActive() === false) {
                $this->delete($offer);
            } else {
                $statement = $this->databaseConnection->prepare('SELECT id FROM offer_price WHERE offer_id = :offer_id AND is_online = 1');
                $statement->bindValue('offer_id', $offer->getId());
                $statement->execute();
                $offerPrices = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if (count($offerPrices) > 0) {
                    $this->index($offer);
                } else {
                    $this->delete($offer);
                }
            }
        }
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Proxy method to index offers
     *
     * @param $offers array|Offer
     */
    public function index($offers)
    {
        $this->publish($offers, self::MESSAGE_INDEX);
    }

    /**
     * Proxy method to delete offers
     *
     * @param $offers array|Offer
     */
    public function delete($offers)
    {
        $this->publish($offers, self::MESSAGE_DELETE);
    }

    /**
     * Publish a message to the river
     *
     * @param $offers      array|Offer
     * @param $messageType index|delete
     */
    private function publish($offers, $messageType)
    {
        if (!$offers) {
            return;
        }

        $messageBody = '';

        if (is_array($offers)) {
            foreach ($offers as $offer) {
                $messageBody .= $this->buildMessageBody($offer, $messageType);
            }
        } else {
            $messageBody = $this->buildMessageBody($offers, $messageType);
        }

        // delivery_mode: 2 => make message persistent, tells RabbitMQ to save message to the disk so it doesn't get lost
        $message = new AMQPMessage($messageBody, array('content_type' => 'application/json', 'delivery_mode' => 2));
        $this->channel->basic_publish($message, $this->exchange, $this->routingKey);
    }

    /**
     * Build the body of a message to be pushed in the queue
     *
     * @param $offer       An offer entity
     * @param $messageType index|delete
     *
     * @return JSON Message
     */
    private function buildMessageBody($offer, $messageType)
    {
        if (!$offer) {
            return;
        }

        $localeId = $offer->getLocale()->getId();
        $offerId  = $offer->getIdAramis();

        $body = json_encode(
            array(
                $messageType => array(
                    '_index' => $this->indexes[$localeId],
                    '_type'  => $this->indexType,
                    '_id'    => $offerId,
                )
            )
        ) . "\n";

        if ($messageType != self::MESSAGE_DELETE) {
            $query  = new OfferQuery($this->databaseConnection, $this->tools);
            $result = $query->getOffer($offer->getId(), $offer->getLocale());
            $body .= json_encode($result)."\n";
        }

        return $body;
    }
}
