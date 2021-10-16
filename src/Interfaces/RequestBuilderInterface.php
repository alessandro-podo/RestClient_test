<?php

declare(strict_types=1);

namespace RestClient\Interfaces;

use RestClient\Dto\Request;
use RestClient\RequestBuilder;

interface RequestBuilderInterface
{
    public function setAuthentication(Authenticator $authentication): self;

    public function setEntity(object $entity): RequestBuilder;

    public function addHeader(string $fieldName, string $fieldValue, bool $exception = true): self;

    public function getRequest(): Request;

    public function setCacheExpiresAfter(int $cacheDuration): self;

    public function setCacheBeta(float $cacheBeta): self;
}
