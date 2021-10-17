<?php

namespace RestClient\Helper;

use Psr\Log\LoggerInterface;
use RestClient\Dto\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;

class LoggerHelper
{
    private bool $loggen;
    private array $logLevel;

    public function __construct(
        private LoggerInterface       $logger,
        private SerializerInterface   $serializer,
        private ParameterBagInterface $parameterBag
    )
    {
        $this->loggen = $this->parameterBag->get('rest_client.logging')["logging"];
        $this->logLevel = $this->parameterBag->get('rest_client.logging')["loglevel"];

    }

    public function log(Request $request, object $response): void
    {
        if (!$this->loggen) {
            return;
        }

        $this->sendLog($this->convertMessage($request), $this->convertMessage($response));
    }

    private function sendLog(array $request, array $response): void
    {
        $loglevel = substr($response["statusCode"], 0, 1) . "xx";
        $this->logger->log($this->logLevel[$loglevel], '(' . $response["statusCode"] . ') ' . $request['url'], [
            "request" => $request,
            'response' => $response
        ]);

    }

    private function convertMessage(object $message): array
    {
        try {
            $array = $this->serializer->normalize($message, 'array');
        } catch (\Throwable $throwable) {
            $array = [$throwable->getMessage()];
        }

        return $this->cleanArray($array);
    }

    private function cleanArray(array $array): array
    {
        //TODO: API-Key aus den Logs nehmen
        $toDeleteKeys = ['id', 'cacheExpiresAfter', 'cacheBeta', 'authBasic', 'informationalHandler', 'successHandler', 'redirectionHandler', 'clientHandler', 'serverHandler'];

        foreach ($toDeleteKeys as $toDeleteKey) {
            if (array_key_exists($toDeleteKey, $array)) {
                unset($array[$toDeleteKey]);
            }
        }
        return $array;
    }

    public function setLoggen(bool $loggen): self
    {
        $this->loggen = $loggen;

        return $this;
    }

    public function reset(): self
    {
        $this->loggen = $this->parameterBag->get('rest_client.logging')["logging"];
        $this->logLevel = $this->parameterBag->get('rest_client.logging')["loglevel"];

        return $this;
    }
}