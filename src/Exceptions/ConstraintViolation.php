<?php

namespace RestClient\Exceptions;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class ConstraintViolation extends \Exception
{

    private ConstraintViolationListInterface $violations;
    public function __construct($message, ConstraintViolationListInterface $violations, $code = 0, Throwable $previous = null)
    {
        $this->violations = $violations;
        parent::__construct($message, $code, $previous);
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}