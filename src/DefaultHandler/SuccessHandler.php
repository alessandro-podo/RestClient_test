<?php

namespace RestClient\DefaultHandler;

use RestClient\Interfaces\HandlerInterface;

class SuccessHandler extends HandlerInterface
{
    public function getResult(): object|array|null
    {
        try {
            return $this->response->toArray();
        } catch (\Throwable $throwable) {
            return null;
        }
    }

}