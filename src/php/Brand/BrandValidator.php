<?php

declare(strict_types=1);

namespace ParfumPulse\Brand;

use ParfumPulse\Validation\ConstraintViolationListFormatter;
use ParfumPulse\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BrandValidator
{
    public function __construct(
        private ValidatorInterface $validatorInterface,
    ) {
    }

    public function validate(array $input, bool $isNew = false): void
    {
        $this->validateInput($input, $isNew);
    }

    private function validateInput(array $input, bool $isNew): void
    {
        $requiredOnCreate = ['name'];
        $constraints = [
            'name' => [
                new NotBlank([
                    'message' => 'A brand can’t have a blank name.',
                ]),
                new Length([
                    'min' => 1,
                    'max' => 100,
                    'minMessage' => 'Brand names can’t be less than {{ limit }} characters long.',
                    'maxMessage' => 'Brand names can’t be more than {{ limit }} characters long.',
                ]),
            ],
        ];

        foreach ($constraints as $key => &$value) {
            if (in_array($key, $requiredOnCreate) && $isNew) {
                $constraints[$key] = [new Required($constraints[$key])];
            } else {
                $constraints[$key] = [new Optional($constraints[$key])];
            }
        }

        $collectionConstraint = new Collection($constraints);

        $violations = $this->validatorInterface->validate($input, $collectionConstraint);

        if (count($violations) > 0) {
            throw new ValidationException(
                ConstraintViolationListFormatter::format($violations)
            );
        }
    }
}
