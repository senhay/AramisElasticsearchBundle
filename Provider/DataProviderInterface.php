<?php

namespace Aramis\Bundle\ElasticsearchBundle\Provider;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * DataProvider interface
 */
interface DataProviderInterface
{
    /**
     * Gets analysis of Index.
     *
     * @return array
     */
    public function getAnalysis();

    /**
     * Gets data of index.
     *
     * @return array
     */
    public function getDocuments();

    /**
     * Gets documents by ids.
     *
     * @param array $id
     *
     * @return array
     */
    public function getDocumentsByIds($ids);

    /**
     * Gets name of index.
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Gets mapping of Index.
     *
     * @return array
     */
    public function getMapping();

    /**
     * Gets mapping parameters of Index.
     *
     * @return array
     */
    public function getMappingParams();

    /**
     * Gets RabbitMQ producer name.
     *
     * @return string
     */
    public function getRabbitMqProducerName();

    /**
     * Gets Rollback Max Level.
     *
     * @return string
     */
    public function getRollBackMaxLevel();

    /**
     * Gets type name of index.
     *
     * @return string
     */
    public function getTypeName();
}
