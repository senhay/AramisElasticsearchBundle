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

An example of DataProvider class is here: `Aramis\Bundle\ElasticsearchBundle\Provider\ExampleDataProvider`.

### C) Basic Bundle Configuration

The basic minimal configuration for AramisElasticsearchBundle is one client with one DataProvider service:

```yaml
# app/config/config.yml
aramis_elasticsearch:
    elasticsearch:
        host: 127.0.0.1
        port: 9200
    data_providers: ['example_data_provider']
```

In this example, we used the "example_data_provider" service:

```yaml
# vendor/aramis/elasticsearch-bundle/Aramis/Bundle/ElasticsearchBundle/Resources/config/services.yml
example_data_provider:
        class: Aramis\Bundle\ElasticsearchBundle\Provider\ExampleDataProvider
```

**Note:**

> Of course, the goal is to use one (or SEVERAL) DataProvider own to you to supply the index.