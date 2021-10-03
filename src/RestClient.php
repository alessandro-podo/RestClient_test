<?php

namespace RestClient;

class RestClient
{

    public function __construct()
    {
        $this->client = new \Symfony\Component\HttpClient\HttpClient();
    }

    public function __toString()
    {
        return"Test";
    }
}