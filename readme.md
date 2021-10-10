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

# Entity:

### Attribute for Class:

* ApiEndpoint (optional)
* HttpMethod (required)
* Url (optional)
* BasicAuthenticator (optional)
* TokenAuthenticator (optional)

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