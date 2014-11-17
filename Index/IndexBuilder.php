<?php

namespace Aramis\Bundle\ElasticsearchBundle\Index;

use Aramis\Bundle\ElasticsearchBundle\Index\Index;
use Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderInterface;
use Aramis\Bundle\ElasticsearchBundle\Exception\InvalidException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Index builder
 */
class IndexBuilder extends Index
{
    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * @var array
     */
    protected $_dataProviders = array();

    /**
     * @param  ContainerInterface $container
     * @param  array              $config
     * @param  array              $dataProviders
     */
    public function __construct(ContainerInterface $container, array $config = array(), $dataProviders = array())
    {
        parent::__construct($config);

        $this->_container = $container;

        // Get DataProviders
        foreach ($dataProviders as $oneDataProvider) {
            $this->addDataProvider($container->get($oneDataProvider));
        }
    }

    /**
     * Creates and populates Index
     *
     * @param  string  $indexName         Index's name
     * @param  boolean $byAlias           Use alias
     * @param  boolean $byQueue           Use RabbitMQ River
     * @param  integer $rollBackMaxLevel  Depth of rollback
     */
    public function buildIndex($indexName, $byAlias = false, $byQueue = false, $rollBackMaxLevel = 1)
    {
        // Build a name for Index with alias
        $indexBuildName = $byAlias ? self::createUniqName($indexName) : $indexName;

        $this->createIndex($indexName, false, true, $indexBuildName);
        $this->requestDocuments($indexName, 'post', $byQueue, null, $indexBuildName);
        if ($byAlias) {
            $this->changeAliasAndClean($indexBuildName, $indexName, $rollBackMaxLevel);
        }
    }

    /**
     * Creates Index
     *
     * @param  string      $indexName        Index's name (alias name)
     * @param  boolean     $byAlias          Use alias
     * @param  boolean     $replaceIfExists  Replace Index if exists
     * @param  string|null $indexBuildName   Index's unique name (for alias mode, null recommended)
     */
    public function createIndex($indexName, $byAlias = false, $replaceIfExists = false, $indexBuildName = null)
    {
        $theDataProvider = $this->selectDataProvider($indexName);

        // Build a name for Index with alias
        $indexBuildName = ($byAlias && !$indexBuildName) ? self::createUniqName($indexName) : $indexBuildName;
        $indexBuildName = ($indexBuildName) ? $indexBuildName : $indexName;

        $indicesByAlias = $this->_elasticaStatus->getIndicesWithAlias($indexName);
        if (!$replaceIfExists && (count($indicesByAlias) || $this->_elasticaStatus->indexExists($indexName))) {
            $indexBuildName = $indicesByAlias[0]->getName();
        } else {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

            if (!empty($analysis = $theDataProvider->getAnalysis())) {
                $elasticaIndex->create($analysis, true); // true: deletes index first if already exists
            }

            $this->defineMapping($elasticaIndex, $theDataProvider);
        }
        if ($byAlias && $replaceIfExists) {
            $this->changeAliasAndClean($indexBuildName, $indexName);
        }
    }

    /**
     * Gets document by id
     *
     * @param  string $indexName  Index's name
     * @param  string $id         Id
     *
     * @return array
     */
    public function getDocumentById($indexName, $id)
    {
        $theDataProvider = $this->selectDataProvider($indexName);
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexName);
        } else {
            $indexesByAlias = $this->_elasticaStatus->getIndicesWithAlias($indexName);
            if (count($indexesByAlias)) {
                $elasticaIndex = $indexesByAlias[0];
            } else {
                throw new InvalidException('Index does not exists.');
            }
        }

        $elasticaType = $elasticaIndex->getType($theDataProvider->getTypeName());

        return $elasticaType->getDocument($id)->getData();
    }

    /**
     * Gets documents by ids
     *
     * @param  string $indexName  Index's name
     * @param  array  $ids        Id's list
     *
     * @return array
     */
    public function getDocumentsByIds($indexName, array $ids)
    {
        $documents = array();
        foreach ($ids as $id) {
            $documents[$id] = $this->getDocumentById($indexName, $id);
        }

        return $documents;
    }

    /**
     * Request documents
     *
     * @param  string      $indexName       Index's name
     * @param  string|null $action          Action (post|delete|null)
     * @param  boolean     $byQueue         Use RabbitMQ River
     * @param  array|null  $ids             Id's list
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function requestDocuments($indexName, $action = 'post', $byQueue = false, $ids = null, $indexBuildName = null)
    {
        $theDataProvider = $this->selectDataProvider($indexName);

        // Get Index
        $indexesByAlias = $this->_elasticaStatus->getIndicesWithAlias($indexName);
        if (null === $indexBuildName && count($indexesByAlias)) {
            $elasticaIndex = $indexesByAlias[0];
        } else {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName ? $indexBuildName : $indexName);
        }

        $elasticaType = $elasticaIndex->getType($theDataProvider->getTypeName());
        $elasticaDocuments = $this->getElasticaDocuments($indexName, $ids);
        $opType = ('delete' == strtolower($action)) ? strtolower($action) : null;

        if ($byQueue) { // Using RabbitMQ River
            if (!$theDataProvider->getRabbitMqProducerName()) {
                throw new InvalidException('There is no RabbitMQ producer name in your DataProvider.');
            }

            $elasticaBulk = new \Elastica\Bulk($this->_elasticaClient);
            $elasticaBulk->addDocuments($elasticaDocuments, $opType);

            $rabbitMQServiceName = sprintf('old_sound_rabbit_mq.%s', $theDataProvider->getRabbitMqProducerName());
            $rabbitMQService = $this->_container->get($rabbitMQServiceName);
            $rabbitMQService->publish($elasticaBulk->toString());
        } else { // Send documents to Elasticesearch
            if ('delete' == $opType) {
                $elasticaType->deleteDocuments($elasticaDocuments);
            } else {
                $elasticaType->addDocuments($elasticaDocuments);
            }
            $elasticaType->getIndex()->refresh();
        }
    }

    /**
     * Refresh documents
     *
     * @param  string      $indexName       Index's name
     * @param  boolean     $byQueue         Use RabbitMQ River
     * @param  array|null  $ids             Id's list
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function refreshDocuments($indexName, $byQueue = false, $ids = null, $indexBuildName = null)
    {
        $this->requestDocuments($indexName, 'delete', $byQueue, $ids, $indexBuildName);
        $this->requestDocuments($indexName, 'post', $byQueue, $ids, $indexBuildName);
    }

    /**
     * Rollback
     *
     * @param  string  $indexName  Index's name
     * @param  integer $level      Index's version (1: last version, 2: second last version, ...)
     */
    public function rollback($indexName, $level = 1)
    {
        // Get all other Indexes
        $otherIndexesNames = $this->getIndexNames();
        $oldIndexesNames   = array();

        // Get current index
        $currentIndexes = $this->_elasticaStatus->getIndicesWithAlias($indexName);
        $currentIndexesNames = array();
        foreach ($currentIndexes as $currentIndex) {
            $currentIndexesNames[] = $currentIndex->getName();
        }

        // Sort other versions
        foreach ($otherIndexesNames as $oneIndexName) {
            if (preg_match(sprintf('/^%s_/', $indexName), $oneIndexName)) {
                if (strtotime(str_replace(sprintf('%s_', $indexName), '', $oneIndexName))) {
                    if (!in_array($oneIndexName, $currentIndexesNames)) {
                        $oldIndexesNames[] = $oneIndexName;
                    }
                }
            }
        }
        rsort($oldIndexesNames);

        // Change alias
        if ($this->_elasticaStatus->indexExists($oldIndexesNames[$level - 1])) {
            $elasticaIndex = $this->_elasticaClient->getIndex($oldIndexesNames[$level - 1]);
            $elasticaIndex->addAlias($indexName, true);
        } else {
            throw new InvalidException('Check rollback level.');
        }
    }

    /**
     * Adds DataProvider
     *
     * @param  DataProviderInterface $dataProvider
     */
    protected function addDataProvider(DataProviderInterface $dataProvider)
    {
        $this->_dataProviders[] = $dataProvider;
    }

    /**
     * Changes alias and deletes old indexes
     *
     * @param  string  $indexName (alias)
     * @param  string  $indexBuildName
     * @param  integer $rollBackMaxLevel
     */
    protected function changeAliasAndClean($indexBuildName, $indexName, $rollBackMaxLevel = 0)
    {
        $theDataProvider = $this->selectDataProvider($indexName);

        // Get Index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

        // Delete old Indexes by name
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $this->_elasticaClient->getIndex($indexName)->delete();
        }

        $keepedIndexesNames = array();
        $oldIndexesNames    = array();
        $keepedIndexesNames[] = $indexBuildName;

        // Get all other Indexes
        $otherIndexesNames = $this->getIndexNames();

        // Change alias
        $elasticaIndex->addAlias($indexName, true);

        // Delete old indexes by alias
        foreach ($otherIndexesNames as $oneIndexName) {
            if (preg_match(sprintf('/^%s_/', $indexName), $oneIndexName)) {
                if (strtotime(str_replace(sprintf('%s_', $indexName), '', $oneIndexName))) {
                    if (!in_array($oneIndexName, $keepedIndexesNames)) {
                        $oldIndexesNames[] = $oneIndexName;
                    }
                }
            }
        }
        rsort($oldIndexesNames);
        if (!empty($provRollbackMaxLevel = $theDataProvider->getRollBackMaxLevel()) && !$rollBackMaxLevel) {
            $rollBackMaxLevel = $provRollbackMaxLevel;
        }
        foreach ($oldIndexesNames as $level => $oneOldIndexName) {
            if ($level >= $rollBackMaxLevel) {
                $this->_elasticaClient->getIndex($oneOldIndexName)->delete();
            }
        }
    }

    /**
     * Creates Unique Name
     *
     * @param  string $indexName
     *
     * @return string
     */
    protected static function createUniqName($indexName)
    {
        return sprintf('%s_%s', $indexName, date('YmdHis'));
    }

    /**
     * Defines Mapping
     *
     * @param  \Elastica\Index       $elasticaIndex
     * @param  DataProviderInterface $theDataProvider
     */
    protected function defineMapping(\Elastica\Index $elasticaIndex, DataProviderInterface $theDataProvider)
    {
        if (!empty($proMapping = $theDataProvider->getMapping())) {
            $elasticaType = $elasticaIndex->getType($theDataProvider->getTypeName());

            // Define mapping
            $mapping = new \Elastica\Type\Mapping();
            $mapping->setType($elasticaType);
            if (!empty($mappingParams = $theDataProvider->getMappingParams())) {
                foreach ($mappingParams as $oneParamIndex => $oneParamValue) {
                    $mapping->setParam($oneParamIndex, $oneParamValue);
                }
            }
            $mapping->setProperties($proMapping);
            $mapping->send();
        }
    }

    /**
     * Gets Elastica Documents
     *
     * @param  string $indexName
     * @param  array  $ids
     *
     * @return array
     */
    protected function getElasticaDocuments($indexName, $ids = null)
    {
        $theDataProvider = $this->selectDataProvider($indexName);

        // Get Data
        $documents = array();
        if (null === $ids) {
            $documents = $theDataProvider->getDocuments();
        } elseif (is_array($ids)) {
            $documents = $theDataProvider->getDocumentsByIds($oneId);
        } else {
            throw new InvalidException('Parameter {$ids} must be an array or null.');
        }

        // Bulk indexing
        $elasticaDocuments = array();
        foreach ($documents as $oneDataLine) {
            $oneDocument = new \Elastica\Document(
                $oneDataLine['id'],
                $oneDataLine
            );
            $oneDocument->setType($theDataProvider->getTypeName());
            $oneDocument->setIndex($indexName);
            $elasticaDocuments[] = $oneDocument;
        }

        return $elasticaDocuments;
    }

    /**
     * Selects a DataProvider
     *
     * @param   string $indexName
     *
     * @return  DataProviderInterface
     */
    protected function selectDataProvider($indexName)
    {
        foreach ($this->_dataProviders as $oneDataProvider) {
            if ($oneDataProvider->getIndexName() == $indexName) {

                return $oneDataProvider;
            }
        }

        throw new InvalidException('DataProvider does not exists.');
    }
}
