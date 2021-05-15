<?php

declare(strict_types=1);

namespace ParfumPulse\Validation\Constraints;

use ParfumPulse\Validation\Constraints\UrlPath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UrlPathValidator extends ConstraintValidator
{
    /**
    * RegEx adapted from Symfony
    * https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Validator/Constraints/UrlValidator.php
    * https://github.com/symfony/symfony/blob/4.4/src/Symfony/Component/Validator/Constraints/EmailValidator.php
    *
    * @copyright Copyright (c) 2004-2019 Fabien Potencier
    * @license   https://github.com/symfony/symfony/blob/4.4/LICENSE
    */
    private const URL_PATH_REGEX = '~^
		(?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*		    # a path
		(?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?   # a query (optional)
		(?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?	    # a fragment (optional)
    $~ixu';

    // @phpstan-ignore-next-line
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UrlPath) {
            throw new UnexpectedTypeException($constraint, UrlPath::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!preg_match(self::URL_PATH_REGEX, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
