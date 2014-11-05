<?php

namespace Aramis\Bundle\ElasticsearchBundle\Services;

use Symfony\Component\Yaml\Parser;

/**
 * IndexManager class
 *
 * @category  Indexing
 * @package   AramisFrontBundle
 * @author    Guillaume MACAIRE <gmacaire@clever-age.com>
 * @author    Carlos PEREIRA DE AMORIM <carlos.pereira-de-amorim@aramisauto.com>
 * @author    Hayssen CHOUIKH <hayssen.chouikh@aramisauto.com>
 */
class IndexManager
{
    /**
     * The index name
     * @var Elastica\Index
     */
    protected $index;

    /**
     * The index name
     * @var string
     */
    protected $indexName;

    /**
     * The index type
     * @var Elastica\Type\Abstract
     */
    protected $indexType;

    /**
     * The index type name
     * @var string
     */
    protected $indexTypeName;

    /**
     * Elasticsearch client
     * @var \Elastica\Client
     */
    protected $esc;

    /**
     * Constructor
     *
     * @param \Elastica\Client $elasticsearchClient [description]
     */
    public function __construct(\Elastica\Client $elasticsearchClient, $indexName, $indexTypeName = null, $indexConfig = null, $mapping = null)
    {
        $this->esc           = $elasticsearchClient;
        $this->indexName     = $indexName;
        $this->indexTypeName = $indexTypeName;
        $this->indexConfig   = $indexConfig;
        $this->mapping       = $mapping;
    }

    /**
     * Creates the index (Deletes index first if already exists)
     *
     * @return string
     */
    public function create()
    {
        $this->index = $this->esc->getIndex($this->indexName);
        $this->index->create($this->indexConfig, true);
        $this->indexType = $this->index->getType($this->indexTypeName);
        $this->initMapping();
    }

    /**
     * Destroys the index
     *
     * @return string
     */
    public function destroy()
    {
        $this->esc->getIndex($this->getIndexName())->delete();
    }

    /**
     * Initializes indexes mapping
     *
     * @return void
     */
    public function initMapping()
    {
        $mapping   = new \Elastica\Type\Mapping();
        $mapping->setType($this->indexType);
        $mapping->setProperties($this->mapping);
        $mapping->send();
    }

    /**
     * [getIndexName description]
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->indexName;
    }

    /**
     * [setIndexName description]
     *
     * @param string $indexName The index name
     *
     * @return string
     */
    public function setIndexName($indexName)
    {
        $this->indexName = $indexName;
    }

    /**
     * [getIndexType description]
     *
     * @return string
     */
    public function getIndexType()
    {
        return $this->indexType;
    }

    /**
     * [setIndexName description]
     *
     * @param string $indexName The index name
     *
     * @return string
     */
    public function setIndexType($indexType)
    {
        $this->indexType = $indexType;
    }
}
