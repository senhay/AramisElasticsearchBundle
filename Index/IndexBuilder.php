<?php

namespace Aramis\Bundle\ElasticsearchBundle\Index;

use Aramis\Bundle\ElasticsearchBundle\Index\Index;
use Aramis\Bundle\ElasticsearchBundle\Manager\DataManagerInterface;
use Aramis\Bundle\ElasticsearchBundle\Exception\InvalidException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Index manager
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
    protected $_dataManagers = array();

    /**
     * @param  ContainerInterface $container
     * @param  array              $config
     * @param  array              $dataManagers
     */
    public function __construct(ContainerInterface $container, array $config = array(), $dataManagers = array())
    {
        parent::__construct($config);

        $this->_container = $container;

        // Get DataManagers
        foreach ($dataManagers as $oneDataManager) {
            $this->addDataManager($container->get($oneDataManager));
        }
    }

    /**
     * Builds Index
     *
     * @param  string  $indexName
     * @param  boolean $byAlias
     * @param  boolean $byQueue
     * @param  integer $rollBackMaxLevel
     */
    public function buildIndex($indexName, $byAlias = false, $byQueue = false, $rollBackMaxLevel = 0)
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
     * @param  string      $indexName
     * @param  boolean     $byAlias
     * @param  boolean     $replaceIfExists
     * @param  string|null $indexBuildName
     */
    public function createIndex($indexName, $byAlias = false, $replaceIfExists = false, $indexBuildName = null)
    {
        $theDataManager = $this->selectDataManager($indexName);

        // Build a name for Index with alias
        $indexBuildName = ($byAlias && !$indexBuildName) ? self::createUniqName($indexName) : $indexBuildName;
        $indexBuildName = ($indexBuildName) ? $indexBuildName : $indexName;

        $indicesByAlias = $this->_elasticaStatus->getIndicesWithAlias($indexName);
        if (!$replaceIfExists && (count($indicesByAlias) || $this->_elasticaStatus->indexExists($indexName))) {
            $indexBuildName = $indicesByAlias[0]->getName();
        } else {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

            if (method_exists($theDataManager, 'getAnalysis')) {
                $elasticaIndex->create($theDataManager->getAnalysis(), true); // true: deletes index first if already exists
            }

            $this->defineMapping($elasticaIndex, $theDataManager);
        }
        if ($byAlias && $replaceIfExists) {
            $this->changeAliasAndClean($indexBuildName, $indexName);
        }
    }

    /**
     * Gets document by id
     *
     * @param  string $indexName
     * @param  string $id
     *
     * @return array
     */
    public function getDocumentById($indexName, $id)
    {
        $theDataManager = $this->selectDataManager($indexName);
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

        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

        return $elasticaType->getDocument($id)->getData();
    }

    /**
     * Gets documents by ids
     *
     * @param  string $indexName
     * @param  array  $ids
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
     * Post Documents
     *
     * @param  string      $indexName
     * @param  string|null $action (post|delete|null)
     * @param  boolean     $byQueue
     * @param  array|null  $ids
     * @param  string|null $indexBuildName
     */
    public function requestDocuments($indexName, $action = 'post', $byQueue = false, $ids = null, $indexBuildName = null)
    {
        $theDataManager = $this->selectDataManager($indexName);

        // Get Index
        $indexesByAlias = $this->_elasticaStatus->getIndicesWithAlias($indexName);
        if (null === $indexBuildName && count($indexesByAlias)) {
            $elasticaIndex = $indexesByAlias[0];
        } else {
            $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName ? $indexBuildName : $indexName);
        }

        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());
        $elasticaDocuments = $this->getElasticaDocuments($indexName, $ids);
        $opType = ('delete' == strtolower($action)) ? strtolower($action) : null;

        if ($byQueue) { // Using RabbitMQ
            if (!$theDataManager->getRabbitMqProducerName()) {
                throw new InvalidException('There is no RabbitMQ producer in your DataManager.');
            }

            $elasticaBulk = new \Elastica\Bulk($this->_elasticaClient);
            $elasticaBulk->addDocuments($elasticaDocuments, $opType);

            $rabbitMQServiceName = sprintf('old_sound_rabbit_mq.%s', $theDataManager->getRabbitMqProducerName());
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
     * Refresh Documents
     *
     * @param  string      $indexName
     * @param  boolean     $byQueue
     * @param  array|null  $ids
     * @param  string|null $indexBuildName
     */
    public function refreshDocuments($indexName, $byQueue = false, $ids = null, $indexBuildName = null)
    {
        $this->requestDocuments($indexName, 'delete', $byQueue, $ids, $indexBuildName);
        $this->requestDocuments($indexName, 'post', $byQueue, $ids, $indexBuildName);
    }

    /**
     * Rollback by alias
     *
     * @param  string  $indexName
     * @param  integer $level
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
     * Adds DataManager
     *
     * @param  DataManagerInterface $dataManager
     */
    protected function addDataManager(DataManagerInterface $dataManager)
    {
        $this->_dataManagers[] = $dataManager;
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
        $theDataManager = $this->selectDataManager($indexName);

        // Get Index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

        // Delete old Indexes by name
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $this->_elasticaClient->getIndex($indexName)->delete();
        }
        // Get old Indexes by alias
        // $lastIndexes = $this->_elasticaStatus->getIndicesWithAlias($indexName);

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
        if (method_exists($theDataManager, 'getRollBackMaxLevel') && !$rollBackMaxLevel) {
            $rollBackMaxLevel = $theDataManager->getRollBackMaxLevel();
        }
        foreach ($oldIndexesNames as $level => $oneOldIndexName) {
            if ($level >= $rollBackMaxLevel) {
                $this->_elasticaClient->getIndex($oneOldIndexName)->delete();
            }
        }
    }

    /**
     * Create Unique Name
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
     * @param  \Elastica\Index      $elasticaIndex
     * @param  DataManagerInterface $theDataManager
     */
    protected function defineMapping(\Elastica\Index $elasticaIndex, DataManagerInterface $theDataManager)
    {
        if (method_exists($theDataManager, 'getMapping')) {
            $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

            // Define mapping
            $mapping = new \Elastica\Type\Mapping();
            $mapping->setType($elasticaType);
            if (method_exists($theDataManager, 'getMappingParams')) {
                foreach ($theDataManager->getMappingParams() as $oneParamIndex => $oneParamValue) {
                    $mapping->setParam($oneParamIndex, $oneParamValue);
                }
            }
            $mapping->setProperties($theDataManager->getMapping());
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
        $theDataManager = $this->selectDataManager($indexName);

        // Get Data
        $documents = array();
        if (null === $ids) {
            $documents = $theDataManager->getDocuments();
        } elseif (is_array($ids)) {
            $documents = $theDataManager->getDocumentsByIds($oneId);
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
            $oneDocument->setType($theDataManager->getTypeName());
            $oneDocument->setIndex($indexName);
            $elasticaDocuments[] = $oneDocument;
        }

        return $elasticaDocuments;
    }

    /**
     * Selects a DataManager
     *
     * @param   string $indexName
     *
     * @return  DataManagerInterface
     */
    protected function selectDataManager($indexName)
    {
        foreach ($this->_dataManagers as $oneDataManager) {
            if ($oneDataManager->getIndexName() == $indexName) {

                return $oneDataManager;
            }
        }

        throw new InvalidException('DataManager does not exists.');
    }
}
