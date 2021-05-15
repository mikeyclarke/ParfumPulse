<?php

declare(strict_types=1);

namespace ParfumPulse\Validation\Exception;

class ValidationException extends \Exception
{
    private const MESSAGE = 'Invalid data';
    private const NAME = 'invalid_request_payload';

    public function __construct(
        private array $violations = [],
    ) {
        parent::__construct(self::MESSAGE);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}
