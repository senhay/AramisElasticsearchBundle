<?php

namespace Aramis\Bundle\ElasticsearchBundle\Index;

use Aramis\Bundle\ElasticsearchBundle\Manager\DataManagerInterface;
use Aramis\Bundle\ElasticsearchBundle\Exception\InvalidException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Index
 */
class Index
{
    /**
     * @var array
     */
    protected $_config = array('host' => '127.0.0.1', 'port' => 9200);

    /**
     * @var \Elastica\Client
     */
    protected $_elasticaClient;

    /**
     * @var \Elastica\Status
     */
    protected $_elasticaStatus;

    /**
     * @param  array $config
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);

        $this->_elasticaClient = $this->getElasticaClient();
        $this->_elasticaStatus = new \Elastica\Status($this->_elasticaClient);
    }

    /**
     * Changes alias
     *
     * @param  string $indexName (alias)
     * @param  string $indexBuildName
     */
    public function changeAlias($indexBuildName, $indexName)
    {
        $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

        // Delete old Indexes by name
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $this->_elasticaClient->getIndex($indexName)->delete();
        }
        $elasticaIndex->addAlias($indexName, true);
    }

    /**
     * Deletes Index
     *
     * @param  string $indexName
     */
    public function deleteIndex($indexName)
    {
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $this->_elasticaClient->getIndex($indexName)->delete();
        }
        $this->deleteIndexByAlias($indexName);
    }

    /**
     * Deletes Index
     *
     * @param  string $alias
     */
    public function deleteIndexByAlias($alias)
    {
        $indexesByAlias = $this->_elasticaStatus->getIndicesWithAlias($alias);
        foreach ($indexesByAlias as $oneIndex) {
            $oneIndex->delete();
        }
    }

    /**
     * Gets Index
     *
     * @return \Elastica\Index
     */
    public function getIndex($indexName)
    {
        return $this->_elasticaClient->getIndex($indexName);
    }

    /**
     * Gets Index names
     *
     * @return array
     */
    public function getIndexNames()
    {
        $names = array();
        $statuses = $this->_elasticaClient->getStatus()->getIndexStatuses();

        foreach ($statuses as $status) {
            $index  = $status->getIndex();
            $names[]  = $index->getName();
        }

        return $names;
    }

    /**
     * Gets Status
     *
     * @return \Elastica\Index\Status
     */
    public function getStatus()
    {
        return $this->_elasticaClient->getStatus();
    }

    /**
     * Removes alias
     *
     * @param  string $indexName (alias)
     * @param  string $indexBuildName
     */
    public function removeAlias($indexBuildName, $indexName)
    {
        if ($this->_elasticaStatus->aliasExists($indexName)) {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);
            $elasticaIndex->removeAlias($indexName);
        }
    }

    /**
     * Gets Elastica Client
     *
     * @return  \Elastica\Client
     */
    protected function getElasticaClient()
    {
        return new \Elastica\Client(array('host' => $this->_config['host'], 'port' => $this->_config['port']));
    }

    /**
     * Sets config
     *
     * @param  array
     */
    protected function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }
    }
}
