<?php

namespace Aramis\Bundle\ElasticsearchBundle\Provider;

use Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderInterface;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Interface of DataProvider service
 */
class DataProviderExample implements DataProviderInterface
{
    /**
     * @var string
     */
    private $_indexName = 'twitter';

    /**
     * @var string
     */
    private $_typeName = 'tweet';

    /**
     * @var array
     */
    private $_analysis = array(
        'number_of_shards' => 1,
        'number_of_replicas' => 1,
        'analysis' => array(
            'analyzer' => array(
                'indexAnalyzer' => array(
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'filter' => array('lowercase', 'mySnowball')
                ),
                'searchAnalyzer' => array(
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'filter' => array('standard', 'lowercase', 'mySnowball')
                )
            ),
            'filter' => array(
                'mySnowball' => array(
                    'type' => 'snowball',
                    'language' => 'German'
                )
            )
        )
        );

    /**
     * @var array
     */
    private $_documents = array(
        '1' => array(
            'id'      => 1,
            'user'    => array(
                'name'      => 'mewantcookies',
                'fullName'  => 'Cookie Monster'
            ),
            'msg'     => 'Me wish there were expression for cookies like there is for apples. "A cookie a day make the doctor diagnose you with diabetes" not catchy.',
            'tstamp'  => '1238081389',
            'location'=> '41.12,-71.34',
            '_boost'  => 1.0
        ),
        '2' => array(
            'id'      => 2,
            'user'    => array(
                'name'      => 'mewantpizzakkk',
                'fullName'  => 'Pizza Monster'
            ),
            'msg'     => 'Me wish there were expression for pizza like there is for apples. "A cookie a day make the doctor diagnose you with diabetes" not catchy.',
            'tstamp'  => '1238081389',
            'location'=> '41.12,-71.34',
            '_boost'  => 1.0
        )
        );

    /**
     * @var array
     */
    private $_mapping = array(
        'id'      => array('type' => 'integer', 'include_in_all' => false),
        'user'    => array(
            'type' => 'object',
            'properties' => array(
                'name'      => array('type' => 'string', 'include_in_all' => true),
                'fullName'  => array('type' => 'string', 'include_in_all' => true)
            ),
        ),
        'msg'     => array('type' => 'string', 'include_in_all' => true),
        'tstamp'  => array('type' => 'date', 'include_in_all' => true),
        'location'=> array('type' => 'geo_point', 'include_in_all' => true),
        '_boost'  => array('type' => 'float', 'include_in_all' => true)
        );

    /**
     * @var array
     */
    private $_mappingParams = array(
        'index_analyzer'  => 'indexAnalyzer',
        'search_analyzer' => 'searchAnalyzer',
        '_boost' => array('name' => '_boost', 'null_value' => 1.0)
        );

    /**
     * @var string
     */
    private $_rabbitMqProducerName = 'elasticsearch_producer';

    /**
     * @var integer
     */
    private $_rollBackMaxLevel = 0;

    /**
     * Sets analysis for Index.
     *
     * @param array $analysis
     */
    public function setAnalysis($analysis)
    {
        $this->_analysis = $analysis;
    }

    /**
     * Sets documents.
     *
     * @param array $documents
     */
    public function setDocuments($documents)
    {
        $this->_documents = $documents;
    }

    /**
     * Sets name for Index.
     *
     * @param string $name
     */
    public function setIndexName($indexName)
    {
        $this->_indexName = $indexName;
    }

    /**
     * Sets mapping for Index.
     *
     * @return array $mapping
     */
    public function setMapping($mapping)
    {
        $this->_mapping = $mapping;
    }

    /**
     * Sets mapping parameters for Index.
     *
     * @return array $mappingParams
     */
    public function setMappingParams($mappingParams)
    {
        $this->_mappingParams = $mappingParams;
    }

    /**
     * Sets type name for Index.
     *
     * @param string $name
     */
    public function setTypeName($typeName)
    {
        $this->_typeName = $typeName;
    }

    /**
     * Sets RabbitMQ producer name.
     *
     * @param string $name
     */
    public function setRabbitMqProducerName($rabbitMqProducerName)
    {
        $this->_rabbitMqProducerName = $rabbitMqProducerName;
    }

    /**
     * Gets analysis of Index. | Optional
     *
     * @return array
     */
    public function getAnalysis()
    {
        return $this->_analysis;
    }

    /**
     * Gets documents.
     *
     * @return array
     */
    public function getDocuments()
    {
        return $this->_documents;
    }

    /**
     * Gets name of Index.
     *
     * @return string
     */
    public function getIndexName()
    {
        return $this->_indexName;
    }

    /**
     * Gets mapping of Index. | Optional
     *
     * @return array
     */
    public function getMapping()
    {
        return $this->_mapping;
    }

    /**
     * Gets mapping parameters of Index. | Optional
     *
     * @return array
     */
    public function getMappingParams()
    {
        return $this->_mappingParams;
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
        $documents = array();
        foreach ($ids as $id) {
            $documents[] = $this->_documents[$id];
        }

        return $documents;
    }

    /**
     * Gets type name of Index.
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->_typeName;
    }

    /**
     * Gets RabbitMQ producer name.  | Optinal
     *
     * @return string
     */
    public function getRabbitMqProducerName()
    {
        return $this->_rabbitMqProducerName;
    }

    /**
     * Gets Rollback Max Level.  | Optinal
     *
     * @return string
     */
    public function getRollBackMaxLevel()
    {
        return $this->_rollBackMaxLevel;
    }
}
