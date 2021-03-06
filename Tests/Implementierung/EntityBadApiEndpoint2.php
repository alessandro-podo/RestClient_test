<?php

declare(strict_types=1);

namespace RestClientTests\Implementierung;

use RestClient\Attribute\ApiEndpoint;
use RestClient\Attribute\HttpMethod;
use RestClient\Attribute\Type;
use Symfony\Component\Validator\Constraints as Assert;

#[HttpMethod(HttpMethod::GET)]
#[ApiEndpoint('badEndpoint2')]
class EntityBadApiEndpoint2
{
    #[Assert\NotBlank()]
    #[Type(Type::JSON)]
    private int $id;

    #[Type(Type::JSON)]
    private string $display;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function setDisplay(string $display): self
    {
        $this->display = $display;

        return $this;
    }
}
