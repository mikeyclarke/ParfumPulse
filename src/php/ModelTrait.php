<?php

declare(strict_types=1);

namespace ParfumPulse;

trait ModelTrait
{
    private function mapProperties(array $properties): void
    {
        foreach ($properties as $key => $value) {
            if (!isset(self::FIELD_MAP[$key])) {
                continue;
            }

            $methodName = 'set' . self::FIELD_MAP[$key];

            $callback = [$this, $methodName];
            if (!is_callable($callback)) {
                continue;
            }

            call_user_func($callback, $value);
        }
    }
}
