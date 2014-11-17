<?php

namespace Aramis\Bundle\ElasticsearchBundle\Provider;

use Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderInterface;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Abstract DataProvider class
 */
abstract class AbstractDataProvider implements DataProviderInterface
{
    /**
     * Gets Index's name.
     *
     * @return string
     */
    abstract public function getIndexName();

    /**
     * Gets type name of Index.
     *
     * @return string
     */
    abstract public function getTypeName();

    /**
     * Gets analysis of Index.
     *
     * @return array
     */
    public function getAnalysis()
    {
        return array();
    }

    /**
     * Gets documents.
     *
     * @return array
     */
    public function getDocuments()
    {
        return array();
    }

    /**
     * Gets Index's mapping.
     *
     * @return array
     */
    public function getMapping()
    {
        return array();
    }

    /**
     * Gets Index's mapping parameters.
     *
     * @return array
     */
    public function getMappingParams()
    {
        return array();
    }

    /**
     * Gets documents by ids.
     *
     * @param array $ids
     *
     * @return array
     */
    public function getDocumentsByIds($ids)
    {
        return array();
    }

    /**
     * Gets RabbitMQ producer name.
     *
     * @return string
     */
    public function getRabbitMqProducerName()
    {
        return 'elasticsearch_producer';
    }

    /**
     * Gets Rollback Max Level.
     *
     * @return string
     */
    public function getRollBackMaxLevel()
    {
        return 1;
    }
}
