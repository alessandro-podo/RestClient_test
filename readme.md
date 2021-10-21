# Offene ToDos:

* Unit Tests schreiben
* RecursivHandler abstrakt schreiben

# Hinzufügen vom Configfile:

~~~yaml
rest_client:
    connections:
        <apiEndpointName>:
            url: 'https://url.de/'
            username: asdsad
            password: asdsadsa
        <apiEndpointName>:
            url: 'https://url.de'
            keyField: 'x-api'
            keyValue: 'asdadadasdasdsadsadsadSA'
~~~
php bin/console config:de

# Entity:

### Attribute for Class:

* ApiEndpoint (optional)
* HttpMethod (required)
* Url (optional)
* BasicAuthenticator (optional)
* TokenAuthenticator (optional)

maker:re:r

### Atrribute for Property:

* Type

# Usage

### RequestBuilder

~~~injectablephp
$requestBuilder = new \RestClient\RequestBuilder($parameterBag);
$requestBuilder->setEntity($entity);

try {
    $request = $requestBuilder->getRequest();
}catch (Throwable $throwable){
    dd($throwable);
}
~~~

in service.yaml to change logger helper.logger:
class: RestClient\Helper\LoggerHelper arguments:
$logger: '@monolog.logger.http_client'