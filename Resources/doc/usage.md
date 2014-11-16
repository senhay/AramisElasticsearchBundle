### A) Using service

#### Get service

```
$builder = $this->get('aramis_elasticsearch_builder');
```

#### Build index

##### Definition:
```
    /**
     * Creates and Populates Index
     *
     * @param  string  $indexName         Index name
     * @param  boolean $byAlias           Use alias
     * @param  boolean $byQueue           Use RabbitMQ
     * @param  integer $rollBackMaxLevel  Depth of rollback
     */
    public function buildIndex($indexName, $byAlias = false, $byQueue = false, $rollBackMaxLevel = 0);
```

##### Example:
```
$builder->buildIndex('twitter', true, false, 1);
```

> $rollBackMaxLevel must be greater than 0 to can use rollback (greater index versions will be deleted).

#### Create index

##### Definition:
```
    /**
     * Creates Index
     *
     * @param  string      $indexName        Index name (alias name)
     * @param  boolean     $byAlias          Use alias
     * @param  boolean     $replaceIfExists  Replace Index if exists
     * @param  string|null $indexBuildName   Index's unique name (for alias mode, null recommended)
     */
    public function createIndex($indexName, $byAlias = false, $replaceIfExists = false, $indexBuildName = null);
```

##### Example:
```
$builder->createIndex('twitter', true, true, null);
```

#### Delete index

##### Definition:
```
    /**
     * Deletes Index
     *
     * @param  string $indexName  Index name
     */
    public function deleteIndex($indexName);
```

##### Example:
```
$builder->deleteIndex('twitter');
```

#### Request Documents

##### Definition:
```
    /**
     * Request Documents
     *
     * @param  string      $indexName       Index name
     * @param  string|null $action          Action (post|delete|null)
     * @param  boolean     $byQueue         Use RabbitMQ
     * @param  array|null  $ids             List of id
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function requestDocuments($indexName, $action = 'post', $byQueue = false, $ids = null, $indexBuildName = null);
```

##### Example:
```
$builder->requestDocuments('twitter', 'delete', false);
```

#### Refresh Documents

##### Definition:
```
    /**
     * Refresh Documents
     *
     * @param  string      $indexName       Index name
     * @param  boolean     $byQueue         Use RabbitMQ
     * @param  array|null  $ids             id's list
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function refreshDocuments($indexName, $byQueue = false, $ids = null, $indexBuildName = null);
```

##### Example:
```
$builder->refreshDocuments('twitter', true, array(1, 2), null);
```
