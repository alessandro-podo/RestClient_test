<?php

declare(strict_types=1);

namespace RestClient\Helper;

use Psr\Log\LoggerInterface;
use RestClient\Dto\Request;
use RestClient\Interfaces\RestClientResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class LoggerHelper
{
    private bool $loggen;
    private array $logLevel;

    public function __construct(
        private LoggerInterface $logger,
        private SerializerInterface $serializer,
        private ParameterBagInterface $parameterBag
    ) {
        $this->loggen = $this->parameterBag->get('rest_client.logging')['logging'];
        $this->logLevel = $this->parameterBag->get('rest_client.logging')['loglevel'];
    }

    public function log(Request $request, RestClientResponseInterface $response): void
    {
        if (!$this->loggen) {
            return;
        }

        $this->sendLog($this->convertMessage($request), $this->convertMessage($response));
    }

    public function setLoggen(bool $loggen): self
    {
        $this->loggen = $loggen;

        return $this;
    }

    public function reset(): self
    {
        $this->loggen = $this->parameterBag->get('rest_client.logging')['logging'];
        $this->logLevel = $this->parameterBag->get('rest_client.logging')['loglevel'];

        return $this;
    }

    private function sendLog(array $request, array $response): void
    {
        $loglevel = mb_substr((string)$response['statusCode'], 0, 1).'xx';
        $this->logger->log($this->logLevel[$loglevel], '('.$response['statusCode'].') '.$request['url'], [
            'request' => $request,
            'response' => $response,
        ]);
    }

    private function convertMessage(RestClientResponseInterface|Request $message): array
    {
        try {
            //context, dass die Exception nicht normalized wird, sonst wird eine Exception geworden
            $array = $this->serializer->normalize($message, 'array', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['previous']]);
        } catch (\Throwable $throwable) {
            $array = [
                'body' => $throwable->getMessage(),
                'statusCode' => $message->getStatusCode(),
            ];
        }

        return $this->cleanArray($array);
    }

    private function cleanArray(array $array): array
    {
        $toDeleteKeys = ['id', 'cacheExpiresAfter', 'cacheBeta', 'authBasic', 'informationalHandler', 'successHandler', 'redirectionHandler', 'clientHandler', 'serverHandler'];
        $connections = $this->parameterBag->get('rest_client.connections');

        foreach ($connections as $connection) {
            $toDeleteKeys[] = $connection['keyField'];
        }

        $this->recursiveUnset($array, $toDeleteKeys);

        return $array;
    }

    private function recursiveUnset(&$array, $toDeleteKeys): void
    {
        foreach ($toDeleteKeys as $key) {
            if (\array_key_exists($key, $array)) {
                unset($array[$key]);
            }
        }

        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->recursiveUnset($value, $toDeleteKeys);
            }
        }
    }
}
