<?php

declare(strict_types=1);

namespace RestClientTests;

use PHPUnit\Framework\TestCase;
use RestClient\Authentication\TokenAuthenticator;
use RestClient\Exceptions\ConstraintViolation;
use RestClient\Exceptions\MissingParameter;
use RestClient\Exceptions\OverrideExistingParameter;
use RestClient\Exceptions\WrongParameter;
use RestClient\RequestBuilder;
use RestClientTests\Implementierung\EntityMissingAuthenticator;
use RestClientTests\Implementierung\EntityMissingMethod;
use RestClientTests\Implementierung\EntityOKBasicAuthenticator;
use RestClientTests\Implementierung\EntityOKTokenAuthenticator;
use RestClientTests\Implementierung\EntityWrongMethod;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @internal
 * @coversNothing
 */
final class RequestBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $this->parameterBag = $this->getMockBuilder(ParameterBag::class)->getMock();
    }

    public function testAddHeaderOKTrue(): void
    {
        //header hinzufügen und überschreiben + ohne parameter
        $requestBuilder = (new RequestBuilder($this->parameterBag))->addHeader('field1', 'value1');
        static::assertInstanceOf(RequestBuilder::class, $requestBuilder);
    }

    public function testAddHeaderOKFalse(): void
    {
        $requestBuilder = (new RequestBuilder($this->parameterBag))->addHeader('field1', 'value1', false);
        static::assertInstanceOf(RequestBuilder::class, $requestBuilder);
    }

    public function testAddHeaderOKOverride(): void
    {
        $requestBuilder = (new RequestBuilder($this->parameterBag))->addHeader('field1', 'value1', false);
        $requestBuilder->addHeader('field1', 'value1', false);
        static::assertInstanceOf(RequestBuilder::class, $requestBuilder);
    }

    public function testAddHeaderExceptionOverride(): void
    {
        $this->expectException(OverrideExistingParameter::class);
        $requestBuilder = (new RequestBuilder($this->parameterBag))->addHeader('field1', 'value1');
        $requestBuilder->addHeader('field1', 'value1');
    }

    /**
     * @dataProvider entities
     *
     * @param mixed $entity
     * @param mixed $authentication
     * @param mixed $addHeaders
     * @param mixed $assertion
     * @param mixed $headers
     * @param mixed $json
     * @param mixed $httpMethod
     * @param mixed $query
     * @param mixed $url
     * @param mixed $authBasic
     */
    public function testGetRequest($entity, $authentication, $addHeaders, $assertion, $headers, $json, $httpMethod, $query, $url, $authBasic): void
    {
        if (class_exists($assertion)) {
            $this->expectException($assertion);
        }

        $requestBuilder = new RequestBuilder($this->parameterBag);

        //Entity
        if (null !== $entity) {
            $requestBuilder->setEntity($entity);
        }

        //Authenticator
        if (null !== $authentication) {
            $requestBuilder->setAuthentication($authentication);
        }

        //Header
        if (null !== $addHeaders) {
            foreach ($addHeaders as $header) {
                $requestBuilder->addHeader($header[0], $header[1]);
            }
        }

        //RequestBuilder
        $request = $requestBuilder->setEntity($entity)->getRequest();

        if (!class_exists($assertion)) {
            static::assertEqualsCanonicalizing($headers, $request->getHeaders());
            static::assertEqualsCanonicalizing($json, $request->getJson());
            static::assertEqualsCanonicalizing($httpMethod, $request->getHttpMethod());
            static::assertEqualsCanonicalizing($query, $request->getQuery());
            static::assertEqualsCanonicalizing($url, $request->getUrl());
            static::assertEqualsCanonicalizing($authBasic, $request->getAuthBasic());
        }
    }

    public function entities()
    {
        return [
            'EntityMissingId' => [
                'Entity' => new EntityOKTokenAuthenticator(),
                'Authentication' => null,
                'addHeader' => null,
                'assertion' => ConstraintViolation::class,
                'headers' => ['headers' => ['api' => 'jjjjj']],
                'json' => null,
                'httpMethod' => null,
                'query' => null,
                'url' => null,
                'authBasic' => null,
            ],
            'EntityOKTokenAuthenticator' => [
                'Entity' => (new EntityOKTokenAuthenticator())->setId(12),
                'Authentication' => null,
                'addHeader' => null,
                'assertion' => null,
                'headers' => ['headers' => ['api' => 'jjjjj']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityOKTokenAuthenticatorWithExtraTokenAuthenticator' => [
                'Entity' => (new EntityOKTokenAuthenticator())->setId(12),
                'Authentication' => (new TokenAuthenticator('api', 'jjjjj')),
                'addHeader' => null,
                'assertion' => null,
                'headers' => ['headers' => ['api' => 'jjjjj']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityOKBasicAuthenticator' => [
                'Entity' => (new EntityOKBasicAuthenticator())->setId(12),
                'Authentication' => null,
                'addHeader' => null,
                'assertion' => null,
                'headers' => null,
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => ['auth_basic' => ['api', 'jjjjj']],
            ],
            'EntityOKTokenAuthenticatorWithExtraHeader' => [
                'Entity' => (new EntityOKTokenAuthenticator())->setId(12),
                'Authentication' => null,
                'addHeader' => [['x-header', '123'], ['x-header4', '1234']],
                'assertion' => null,
                'headers' => ['headers' => ['api' => 'jjjjj', 'x-header' => '123', 'x-header4' => '1234']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityMissingMethod' => [
                'Entity' => (new EntityMissingMethod())->setId(12),
                'Authentication' => null,
                'addHeader' => [['x-header', '123'], ['x-header4', '1234']],
                'assertion' => MissingParameter::class,
                'headers' => ['headers' => ['api' => 'jjjjj', 'x-header' => '123', 'x-header4' => '1234']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityWrongMethod' => [
                'Entity' => (new EntityWrongMethod())->setId(12),
                'Authentication' => null,
                'addHeader' => [['x-header', '123'], ['x-header4', '1234']],
                'assertion' => WrongParameter::class,
                'headers' => ['headers' => ['api' => 'jjjjj', 'x-header' => '123', 'x-header4' => '1234']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityMissingAuthenticator' => [
                'Entity' => (new EntityMissingAuthenticator())->setId(12),
                'Authentication' => null,
                'addHeader' => [['x-header', '123'], ['x-header4', '1234']],
                'assertion' => MissingParameter::class,
                'headers' => ['headers' => ['api' => 'jjjjj', 'x-header' => '123', 'x-header4' => '1234']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
            'EntityMissingAuthenticatorOk' => [
                'Entity' => (new EntityMissingAuthenticator())->setId(12),
                'Authentication' => new TokenAuthenticator('api', 'jjjjj'),
                'addHeader' => [['x-header', '123'], ['x-header4', '1234']],
                'assertion' => null,
                'headers' => ['headers' => ['api' => 'jjjjj', 'x-header' => '123', 'x-header4' => '1234']],
                'json' => ['json' => ['id' => 12]],
                'httpMethod' => 'GET',
                'query' => null,
                'url' => 'https://google.de',
                'authBasic' => null,
            ],
        ];
    }
}
