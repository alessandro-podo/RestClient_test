<?php

namespace RestClient\DefaultHandler;

use RestClient\Interfaces\HandlerInterface;

class InformationalHandler extends HandlerInterface
{


    public function getResult(): array
    {
        return $this->response->toArray();
    }
}