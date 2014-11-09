<?php

namespace Aramis\Bundle\ElasticsearchBundle\Services;

/**
 * IndexDataCollector class
 *
 * @category  Indexing
 * @package   AramisFrontBundle
 * @author    Hayssen CHOUIKH <hayssen.chouikh@aramisauto.com>
 */
class IndexDataCollector
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
     * The index type name for the general information
     * @var string
     */
    protected $indexTypeGeneralName;

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
    public function __construct(\Elastica\Client $elasticsearchClient, $indexName, $indexGeneralName, $indexTypeName = null, $indexTypeGeneralName = null)
    {
        $this->esc           = $elasticsearchClient;
        $this->indexName     = $indexName;
        $this->indexGeneralName = $indexGeneralName;
        $this->indexTypeName = $indexTypeName;
        $this->indexTypeGeneralName = $indexTypeGeneralName;
        $this->index         = $this->esc->getIndex($this->indexName);
        $this->indexGeneral  = $this->esc->getIndex($this->indexGeneralName);

        //$this->search        = new \Elastica\Search($this->esc);

        // query string
        $this->elasticaQueryString    = new \Elastica\Query\QueryString();

        // query
        $this->elasticaQuery          = new \Elastica\Query();

        // query term
        $this->queryTerms             = new \Elastica\Query\Terms();

        // facet
        $this->elasticaFacet          = new \Elastica\Facet\Terms('Facettes');

        // filter
        $this->elasticaFilterTerms    = new \Elastica\Filter\Terms();
        $this->elasticaFilterAnd      = new \Elastica\Filter\BoolAnd();
    }

    /**
     * Creates the index (Deletes index first if already exists)
     *
     * @return string
     */
    public function getFacet($field, $arrTerms = array(), $sync = true, $maxSize = 100)
    {
        if (!$sync) {
            $this->elasticaFacet = new \Elastica\Facet\Terms('Facettes' . ucfirst($field));
        } else {
            $this->elasticaFacet = new \Elastica\Facet\Terms('Facettes');
        }

        $this->elasticaFacet->setField($field);
        $this->elasticaFacet->setSize($maxSize);

        if (count($arrTerms)) {
            foreach ($arrTerms as $oneTermIndex => $oneTermElements) {
                $oneTermElements = array_filter($oneTermElements, 'strlen');
                if (count($oneTermElements)) {
                    $filter = new \Elastica\Filter\Terms();
                    $filter->setTerms($oneTermIndex, $oneTermElements);
                    $this->elasticaFilterAnd->addFilter($filter);
                }
            }
            $this->elasticaFacet->setFilter($this->elasticaFilterAnd);
        }

        $this->elasticaQuery->addFacet($this->elasticaFacet);

        //Search on the index.
        $type               = $this->index->getType($this->indexTypeName);
        $elasticaResultSet  = $type->search($this->elasticaQuery);
        $elasticaFacets     = $elasticaResultSet->getFacets();
        $arrTerms           = array();

        foreach ($elasticaFacets['Facettes']['terms'] as $elasticaFacet) {
            $arrTerms[] = $elasticaFacet;
        }

        return $arrTerms;
    }

    /**
     * Get brands
     *
     * @return string
     */
    public function getBrands($vehicleType = 'ALL', $maxSize = 100)
    {
        $elasticaFacetHeader = new \Elastica\Facet\Terms('FacettesHeader');
        $elasticaQuery       = new \Elastica\Query();
        $brandIndex          = $this->esc->getIndex($this->indexName);
        $elasticaFilterAnd   = new \Elastica\Filter\BoolAnd();

        $elasticaFacetHeader->setField('brand');
        $elasticaFacetHeader->setSize($maxSize);
        if ($vehicleType != 'ALL') {
            if (strtoupper($vehicleType) == 'NV0K') {
                $arrVehicleType = array('NV', '0k');
            } else {
                $arrVehicleType = array($vehicleType);
            }
            $filter = new \Elastica\Filter\Terms();
            $filter->setTerms('vehicle_type', $arrVehicleType);
            $elasticaFilterAnd->addFilter($filter);
            $elasticaFacetHeader->setFilter($elasticaFilterAnd);
        }

        $elasticaQuery->addFacet($elasticaFacetHeader);

        $elasticaResultSet  = $brandIndex->search($elasticaQuery);
        $elasticaFacets     = $elasticaResultSet->getFacets();
        $arrTerms           = array();

        foreach ($elasticaFacets['FacettesHeader']['terms'] as $elasticaFacet) {
            $arrTerms[] = $elasticaFacet;
        }

        return $arrTerms;
    }

    /**
     * Return a given offer.
     *
     * @return Elastica\Document
     */
    public function getOffer($idAramis)
    {
        $terms = new \Elastica\Query\Terms();
        $terms->setTerms('id_aramis', array($idAramis));
        $elasticaQuery = new \Elastica\Query($terms);
        $elasticaResultSet = $this->index->search($elasticaQuery);
        $results = $elasticaResultSet->getResults();

        return isset($results[0]) ? $results[0]->getData() : null;
    }

    /**
     * Return a given offer.
     *
     * @return Elastica\Document
     */
    public function getOfferPrice($idAramisOfferPrice)
    {
        $terms = new \Elastica\Query\Terms();
        $terms->setTerms('offer_price_id_aramis', array($idAramisOfferPrice));
        $elasticaQuery = new \Elastica\Query($terms);
        $elasticaResultSet = $this->index->search($elasticaQuery);
        $results = $elasticaResultSet->getResults();

        return isset($results[0]) ? $results[0]->getData() : null;
    }

    /**
     * Delete a given offer.
     */
    public function deleteOffer($id)
    {
        $type = new \Elastica\Type($this->index, $this->indexTypeName);
        $type->deleteById($id);
    }

    /**
     * Return a given offer.
     *
     * @return array
     */
    public function getOffers()
    {
        $elasticaQuery = new \Elastica\Query();
        $elasticaQuery->setFrom(0);
        $elasticaQuery->setLimit(10000);
        $type = $this->index->getType($this->indexTypeName);
        $elasticaResultSet = $type->search($elasticaQuery);


        return $this->getResultsAsArray($elasticaResultSet);
    }

    /**
     * Search offers
     * @param  array  $vehicleType      array with (NV, 0K, UV)
     * @param  array  $params           this is params must be clean
     * @param  array  $rangeParams      this is params must be clean
     * @param  integer $from
     * @param  integer $limit
     * @param  array   $sort
     * @return array
     */
    public function searchOffers($vehicleType, $params, $rangeParams, $from = 0, $limit = 100, $sort = array('offer_price'))
    {
        $boolQuery = new \Elastica\Query\Bool();
        $filterAnd = null;
        $isBool    = false;
        $isRange   = false;
        $isModel   = false;

        if (is_array($vehicleType)) {
            $terms = new \Elastica\Query\Terms();
            $terms->setTerms('vehicle_type', $vehicleType['vehicle_type']);
            $boolQuery->addMust($terms);
            $isBool  = true;
        } elseif ($vehicleType != 'ALL') {
            $terms = new \Elastica\Query\Terms();
            $terms->setTerms('vehicle_type', array($vehicleType['vehicle_type']));
            $boolQuery->addMust($terms);
            $isBool  = true;
        }

        // Fix url quand on supprime directement la marque de la facet
        // recherche au lieu du model plus la marque.
        if (empty($params['brand'])) {
            unset($params['model']);
        }

        if (!empty($params)) {
            $filterAnd  = new \Elastica\Filter\BoolAnd();
            foreach ($params as $name => $value) {
                if (!empty($value)) {
                    if (($name == 'brand') || ($name == 'model')) {
                        continue;
                    }
                    $terms = new \Elastica\Filter\Terms();
                    $terms->setTerms($name, $value);
                    $filterAnd->addFilter($terms);
                    $isBool  = true;
                }
            }
        }

        if (isset($params['brand'])) {

            // affecter les models par rapport à leur brand
            $modelByBrand = array();
            foreach ($params['brand'] as $brandName) {
                $modelByBrand[$brandName] = $this->getFacetModelByBrand($brandName);
            }

            // Si un model est défini dans la recherche.
            if (isset($params['model'])) {

                $filterOr  = new \Elastica\Filter\BoolOr();
                $removeBrand = array();

                foreach ($params['model'] as $modelName) {
                    $filterBranModAnd  = new \Elastica\Filter\BoolAnd();
                    foreach ($modelByBrand as $brand => $models) {
                        foreach ($models as $model) {
                            if (in_array($modelName, $model)) {

                                $modelTerms = new \Elastica\Filter\Term();
                                $modelTerms->setTerm('model', $modelName);
                                $filterBranModAnd->addFilter($modelTerms);

                                $brandTerms = new \Elastica\Filter\Term();
                                $brandTerms->setTerm('brand', $brand);
                                $filterBranModAnd->addFilter($brandTerms);

                                $removeBrand[] = $brand;
                                $isBool  = true;
                            }
                        }
                    }
                    $filterOr->addFilter($filterBranModAnd); // spécifier le model et le brand comme obligatoire.
                }

                foreach ($params['brand'] as $brandName) {
                    if (!in_array($brandName, $removeBrand)) {
                        $terms = new \Elastica\Filter\Term();
                        $terms->setTerm('brand', $brandName);
                        $filterOr->addFilter($terms);
                        $isBool  = true;
                    }
                }

                $filterAnd->addFilter($filterOr);
                $isModel = true;
            } else {
                $terms = new \Elastica\Filter\Terms();
                $terms->setTerms('brand', $params['brand']);
                $filterAnd->addFilter($terms);
                $isBool  = true;
            }
        }

        if (!empty($rangeParams)) {

            if ($filterAnd == null) {
                $filterAnd  = new \Elastica\Filter\BoolAnd();
            }

            foreach ($rangeParams as $field => $value) {
                $value = intval($value);
                if ($field == 'min_budget' && $value > 0) {
                    $filter = new \Elastica\Filter\Range();
                    $field_params = array('from' => $value);
                    $filter->addField('offer_price', $field_params);
                    $filterAnd->addFilter($filter);
                    $isRange = true;
                } elseif ($field == 'max_budget' && $value > 0 && $value < 45000) {
                    $filter = new \Elastica\Filter\Range();
                    $field_params = array('to' => $value);
                    $filter->addField('offer_price', $field_params);
                    $filterAnd->addFilter($filter);
                    $isRange = true;
                }
                if ($field == 'min_km' && $value > 0) {
                    $filter = new \Elastica\Filter\Range();
                    $field_params = array('from' => $value);
                    $filter->addField('offer_km', $field_params);
                    $filterAnd->addFilter($filter);
                    $isRange = true;
                } elseif ($field == 'max_km' && $value > 0) {
                    $filter = new \Elastica\Filter\Range();
                    $field_params = array('to' => $value);
                    $filter->addField('offer_km', $field_params);
                    $filterAnd->addFilter($filter);
                    $isRange = true;
                }
            }
        }

        $elasticaQuery = $isBool ? new \Elastica\Query($boolQuery) : new \Elastica\Query();
        if ($filterAnd != null && ($isRange || $isBool || $isModel)) {
            $elasticaQuery = $elasticaQuery->setFilter($filterAnd);
        }

        $elasticaQuery->setFrom($from);
        $elasticaQuery->setLimit($limit);
        $elasticaQuery->setSort($sort);
        $type = $this->index->getType($this->indexTypeName);
        $elasticaResultSet = $type->search($elasticaQuery);

        return array('total' => $elasticaResultSet->getTotalHits(), 'results' => $this->getResultsAsArray($elasticaResultSet));
    }

    /**
     *
     */
    public function getGeneralInfos()
    {
        $type = $this->indexGeneral->getType($this->indexTypeGeneralName);
        $elasticaResultSet = $type->search('*');

        $results = $this->getResultsAsArray($elasticaResultSet);
        if (empty($results)) {
            return array();
        }
        // normalement, il n'y a qu'un seul generalInfos par catalog
        return array_pop($results);
    }

    /**
     * [getResultsAsArray description]
     * @param  elasticaResultSet $elasticaResultSet [description]
     * @return array
     */
    private function getResultsAsArray($elasticaResultSet)
    {
        $results = array();
        foreach ($elasticaResultSet->getResults() as $oneResult) {
            $results[] = $oneResult->getData();
        }

        return $results;
    }



    /**
     *  Cette fonction sera à mettre à jour lorsque vous mettrez à jour Elastica en version 1.12
     *  Il faudra alors supprimer les facets car ils seront supprimer et utiliser les aggregations
     *  les terms Facets. Elle fonctionne uniquement avec ElasticSearch 1.0 et +
     *  voir http://blog.qbox.io/elasticsearch-aggregations
     *
     *
     *  Cette fonction permet de grouper les models par rapport aux brand.
     *
     *  @param $TermValue le nom du brand
     *
     */
    private function getFacetModelByBrand($TermValue, $maxSize = 100)
    {

        $this->elasticaFacet = new \Elastica\Facet\Terms('Facettes');

        $this->elasticaFacet->setField('model');
        $this->elasticaFacet->setSize($maxSize);

        if (!empty($TermValue)) {
            $filter = new \Elastica\Filter\Term();
            $filter->setTerm('brand', $TermValue);
            $this->elasticaFilterAnd->setFilters(array($filter));
            $this->elasticaFacet->setFilter($this->elasticaFilterAnd);
        }

        $this->elasticaQuery->setFacets(array($this->elasticaFacet));

        //Search on the index.
        $type               = $this->index->getType($this->indexTypeName);
        $elasticaResultSet  = $type->search($this->elasticaQuery);
        $elasticaFacets     = $elasticaResultSet->getFacets();
        $arrTerms           = array();

        foreach ($elasticaFacets['Facettes']['terms'] as $elasticaFacet) {
            $arrTerms[] = $elasticaFacet;
        }

        return $arrTerms;
    }
}
