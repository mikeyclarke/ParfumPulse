<?php

declare(strict_types=1);

namespace ParfumPulse\Validation\Constraints;

use Symfony\Component\Validator\Constraint;

class UrlPath extends Constraint
{
    public string $message = 'The value "{{ string }}" is not a valid URL path.';
}
