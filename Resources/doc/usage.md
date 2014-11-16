### A) Using service

#### Get service

```
$builder = $this->get('aramis_elasticsearch_builder');
```

#### Build index

##### Definition:
```
/**
 * Builds Index
 *
 * @param  string  $indexName        Index name
 * @param  boolean $byAlias          Use alias
 * @param  boolean $byQueue          Use RabbitMQ
 * @param  integer $rollBackMaxLevel depth of rollback
 */
public function buildIndex($indexName, $byAlias = false, $byQueue = false, $rollBackMaxLevel = 0);
```

##### Example:
```
$builder->buildIndex('twitter', true, false, 0);
```

> $rollBackMaxLevel must be greater than 0 to can use rollback (greater index versions will be deleted).
