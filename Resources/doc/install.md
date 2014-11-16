Step 1: Setting up the bundle
=============================
### A) Add AramisElasticsearchBundle to your composer.json

```yaml
{
    "require": {
        "aramis/elasticsearch-bundle": "dev-master"
    }
}
```

### B) Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Aramis\Bundle\ElasticsearchBundle\AramisElasticsearchBundle(),
    );
}
```

### C) Create a DataProvider service

You need to create a service that will feed index with data.

The service class must implements DataProviderInterface: `Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderInterface`.

There is an example of DataProvider class: `Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderExample`.

### C) Basic Bundle Configuration

The basic minimal configuration for AramisElasticsearchBundle is one client with one DataProvider service:

```yaml
# app/config/config.yml
aramis_elasticsearch:
    elasticsearch:
        host: 127.0.0.1
        port: 9200
    data_providers: ['data_provider_example']
```

In this example, we used the "data_provider_example" service:

```yaml
# vendor/aramis/elasticsearch-bundle/Aramis/Bundle/ElasticsearchBundle/Resources/config/services.yml
data_provider_example:
        class: Aramis\Bundle\ElasticsearchBundle\Provider\DataProviderExample
```

**Note:**

> Of course, the goal is to use one (or SEVERAL) DataProvider own to you, to supply the index.