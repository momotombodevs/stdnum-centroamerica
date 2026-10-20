<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

final class ValidationResult
{
    public function __construct(public readonly ?ValidationError $error = null)
    {
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }
}
