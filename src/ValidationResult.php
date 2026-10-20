<?php

declare(strict_types=1);

namespace StdNumCentroamerica;

final class ValidationResult
{
    private function __construct(public readonly ?ValidationError $error)
    {
    }

    public static function ok(): self
    {
        return new self(null);
    }

    public static function fail(ValidationError $error): self
    {
        return new self($error);
    }

    public static function from(?ValidationError $error): self
    {
        return new self($error);
    }

    public function isValid(): bool
    {
        return $this->error === null;
    }
}
