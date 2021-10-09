<?php

namespace RestClient;

use RestClient\Dto\Request;
use RestClient\Interfaces\Authenticator;

interface RequestBuilderInterface
{
    public function setAuthentication(Authenticator $authentication): self;

    public function setEntity(object $entity): RequestBuilder;

    public function addHeader(string $fieldName, string $fieldValue, bool $exception = true): self;

    public function getRequest(): Request;
}