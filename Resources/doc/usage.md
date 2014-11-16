### A) Using service

#### Get service

```
$builder = $this->get('aramis_elasticsearch_builder');
```

#### Build index

##### Definition:
```
    /**
     * Creates and populates Index
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

#### Request documents

##### Definition:
```
    /**
     * Request documents
     *
     * @param  string      $indexName       Index name
     * @param  string|null $action          Action (post|delete|null)
     * @param  boolean     $byQueue         Use RabbitMQ
     * @param  array|null  $ids             Id's list
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function requestDocuments($indexName, $action = 'post', $byQueue = false, $ids = null, $indexBuildName = null);
```

##### Example:
```
$builder->requestDocuments('twitter', 'delete', false);
```

#### Refresh documents

##### Definition:
```
    /**
     * Refresh documents
     *
     * @param  string      $indexName       Index name
     * @param  boolean     $byQueue         Use RabbitMQ
     * @param  array|null  $ids             Id's list
     * @param  string|null $indexBuildName  Index's unique name (for alias mode, null recommended)
     */
    public function refreshDocuments($indexName, $byQueue = false, $ids = null, $indexBuildName = null);
```

##### Example:
```
$builder->refreshDocuments('twitter', true, array(1, 2), null);
```

#### Get document

##### Definition:
```
    /**
     * Gets document by id
     *
     * @param  string $indexName  Index's name
     * @param  string $id         Id
     *
     * @return array
     */
    public function getDocumentById($indexName, $id);
```

##### Example:
```
$builder->getDocumentById('twitter', 1);
```

#### Get documents

##### Definition:
```
    /**
     * Gets documents by ids
     *
     * @param  string $indexName  Index's name
     * @param  array  $ids        Id's list
     *
     * @return array
     */
    public function getDocumentsByIds($indexName, array $ids);
```

##### Example:
```
$builder->getDocumentsByIds('twitter', array(1,2));
```

#### Rollback

##### Definition:
```
    /**
     * Rollback
     *
     * @param  string  $indexName  Index's name
     * @param  integer $level      Index's version (1: last version, 2: second last version, ...)
     */
    public function rollback($indexName, $level = 1);
```

##### Example:
```
$builder->rollback('twitter');
```

### B) Using command line

#### Command's list:
```
-  aramis:elasticsearch:admin            Elasticsearch Command Line Tool
-  aramis:elasticsearch:build            Elasticsearch Build Tool
-  aramis:elasticsearch:rabbitmq_river   Elasticsearch RabbitMQ River Manager
```

#### Examples:
```
-  aramis:elasticsearch:admin alias
-  aramis:elasticsearch:build build twitter
-  aramis:elasticsearch:build rollback twitter
-  ...
```

> Check [--help] for more details and actions about these commands.