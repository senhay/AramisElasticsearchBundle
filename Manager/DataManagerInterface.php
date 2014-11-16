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
     * Gets type name of index.
     *
     * @return string
     */
    public function getTypeName();
}
