<?php

namespace Aramis\Bundle\ElasticsearchBundle\Index;

use Aramis\Bundle\ElasticsearchBundle\Manager\DataManagerInterface;
use Aramis\Bundle\ElasticsearchBundle\Exception\InvalidException;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * http://elastica.io/getting-started/storing-and-indexing-documents.html
 *
 * @author i-team <iteam@aramisauto.com>
 *
 * Index manager
 */
class IndexBuilder
{
    protected $_container;
    /**
     * @var array
     */
    protected $_config = array('host' => '127.0.0.1', 'port' => 9200);

    /**
     * @var array
     */
    protected $_dataManagers = array();

    /**
     * @var DataManagerInterface
     */
    protected $_theDataManager;

    /**
     * @var array
     */
    protected $_indexes = array();

    /**
     * @var \Elastica\Client
     */
    protected $_elasticaClient;

    /**
     * @var \Elastica\Status
     */
    protected $_elasticaStatus;

    /**
     * @param array $config
     */
    public function __construct(ContainerInterface $container, array $config = array(), $dataManagers = array())
    {
        $this->setConfig($config);

        foreach ($dataManagers as $oneDataManager) {
            $this->addDataManager($container->get($oneDataManager));
        }

        $this->_elasticaClient = $this->getElasticaClient();
        $this->_elasticaStatus = new \Elastica\Status($this->_elasticaClient);
    }

    /**
     * Sets config
     *
     * @param  array
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }
    }

    /**
     * Gets Elastica Client
     *
     * @return \Elastica\Client
     */
    public function getElasticaClient()
    {
        return new \Elastica\Client(array('host' => $this->_config['host'], 'port' => $this->_config['port']));
    }

    /**
     * Adds data manager
     *
     * @param DataManagerInterface $dataManager
     */
    protected function addDataManager(DataManagerInterface $dataManager)
    {
        $this->_dataManagers[] = $dataManager;
    }

    /**
     * Builds Index
     *
     * @param  string $indexName
     * @param  boolean $byAlias
     */
    public function buildIndex($indexName, $byAlias = false)
    {
        // Build a name for Index with alias
        $indexBuildName = $byAlias ? $indexName . '_' . uniqid() : $indexName;

        // Select the data manager
        $theDataManager = $this->selectDataManager($indexName);

        // Load index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

        // Create the index new
        $elasticaIndex->create($theDataManager->getAnalysis(), true); // true: deletes index first if already exists

        // Define Mapping
        $this->defineMapping($elasticaIndex, $theDataManager);

        // Bulk indexing
        $this->addDocuments($elasticaIndex, $theDataManager);

        if ($byAlias) {
            // Change alias and delete old indexes
            $this->changeAliasAndClean($elasticaIndex, $indexName);
        }
    }

    /**
     * Post one Document
     *
     * @param  string $indexName
     * @param  string $id
     */
    public function postDocument($indexName, $id)
    {
        // Select the Data Manager
        $theDataManager = $this->selectDataManager($indexName);

        // Get Index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexName);

        // Create a Type
        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

        // Gets Data
        $dataManagerDocument = $theDataManager->getOneDocument($id);

        // Elastica document
        $elasticaDocument = new \Elastica\Document($id, $dataManagerDocument);

        // Add document to type
        $elasticaType->addDocument($elasticaDocument);

        // Refresh Index
        $elasticaType->getIndex()->refresh();
    }

    /**
     * Post Documents
     *
     * @param  string $indexName
     * @param  array $ids
     */
    public function postDocuments($indexName, $ids)
    {
        // Select the Data Manager
        $theDataManager = $this->selectDataManager($indexName);

        // Get Index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexName);

        // Create a type
        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

        // Create holder for Elastica documents
        $elasticaDocuments = array();

        // Gets Data
        $documents = array();
        foreach ($ids as $oneId) {
            $documents[] = $theDataManager->getOneDocument($id);
        }

        // Bulk indexing
        foreach ($documents as $oneDataLine) {
            $elasticaDocuments[] = new \Elastica\Document(
                $oneDataLine['id'],
                $oneDataLine
            );
        }
        $elasticaType->addDocuments($elasticaDocuments);
        $elasticaType->getIndex()->refresh();
    }

    /**
     * Select Data Manager
     *
     * @param  string $indexName
     *
     * @return DataManagerInterface
     */
    private function selectDataManager($indexName)
    {
        foreach ($this->_dataManagers as $oneDataManager) {
            if ($oneDataManager->getIndexName() == $indexName) {

                return $oneDataManager;
            }
        }

        throw new InvalidException('DataManager does not exist');
    }

    /**
     * Defines Analysis
     *
     * @param string $indexBuildName
     * @param DataManagerInterface $theDataManager
     */
    private function defineAnalysis(DataManagerInterface $theDataManager, $indexBuildName)
    {
        // Load index
        $elasticaIndex = $this->_elasticaClient->getIndex($indexBuildName);

        // Create the index new
        $elasticaIndex->create($theDataManager->getAnalysis(), true); // true: deletes index first if already exists
    }

    /**
     * Defines Mapping
     *
     * @param \Elastica\Index $elasticaIndex
     * @param DataManagerInterface $theDataManager
     */
    private function defineMapping(\Elastica\Index $elasticaIndex, DataManagerInterface $theDataManager)
    {
        // Create a type
        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);
        foreach ($theDataManager->getMappingParams() as $oneParamIndex => $oneParamValue) {
            $mapping->setParam($oneParamIndex, $oneParamValue);
        }
        $mapping->setProperties($theDataManager->getMapping());

        // Send mapping to type
        $mapping->send();
    }

    /**
     * Bulk indexing
     *
     * @param \Elastica\Index $elasticaIndex
     * @param DataManagerInterface $theDataManager
     */
    private function addDocuments(\Elastica\Index $elasticaIndex, DataManagerInterface $theDataManager)
    {
        // Create a type
        $elasticaType = $elasticaIndex->getType($theDataManager->getTypeName());

        // Create holder for Elastica documents
        $elasticaDocuments = array();

        // Gets data
        $dataManagerDocuments = $theDataManager->getDocuments();

        // Bulk indexing
        foreach ($dataManagerDocuments as $oneDataLine) {
            $elasticaDocuments[] = new \Elastica\Document(
                $oneDataLine['id'],
                $oneDataLine
            );
        }
        $elasticaType->addDocuments($elasticaDocuments);
        $elasticaType->getIndex()->refresh();
    }

    /**
     * Changes alias and deletes old indexes
     *
     * @param \Elastica\Index $elasticaIndex
     * @param  string $indexName
     */
    private function changeAliasAndClean(\Elastica\Index $elasticaIndex, $indexName)
    {
        // Delete old indexes by name
        if ($this->_elasticaStatus->indexExists($indexName)) {
            $this->_elasticaClient->getIndex($indexName)->delete();
        }

        // Get old indexes by alias
        $indexesToDelete = $this->_elasticaStatus->getIndicesWithAlias($indexName);

        // Change alias
        $elasticaIndex->addAlias($indexName, true);

        // Delete old indexes by alias
        foreach ($indexesToDelete as $indexToDelete) {
            $indexToDelete->delete();
        }
    }
}
