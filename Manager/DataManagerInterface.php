<?php

namespace Aramis\Bundle\ElasticsearchBundle\Manager;

/**
 * @author i-team <iteam@aramisauto.com>
 *
 * Interface of data manager service
 */
interface DataManagerInterface
{
    /**
     * Gets analysis of index.
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
     * Gets name of index.
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Gets mapping of index.
     *
     * @return array
     */
    public function getMapping();

    /**
     * Gets mapping parameters of index.
     *
     * @return array
     */
    public function getMappingParams();

    /**
     * Gets one document.
     *
     * @param string $id
     *
     * @return array
     */
    public function getOneDocument($id);

    /**
     * Gets type name of index.
     *
     * @return string
     */
    public function getTypeName();
}
