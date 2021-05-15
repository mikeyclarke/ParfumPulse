<?php

declare(strict_types=1);

namespace ParfumPulse\Validation;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationListFormatter
{
    public static function format(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $key = self::formatViolationPropertyPath($violation);
            if (!isset($errors[$key])) {
                $errors[$key] = [];
            }
            $errors[$key][] = $violation->getMessage();
        }

        return $errors;
    }

    private static function formatViolationPropertyPath(ConstraintViolationInterface $violation): string
    {
        $result = preg_replace(
            ['(^\[)', '(\]\[)', '(\]$)'],
            ['', '.', ''],
            $violation->getPropertyPath()
        );
        if (null === $result) {
            return '';
        }
        return $result;
    }
}
