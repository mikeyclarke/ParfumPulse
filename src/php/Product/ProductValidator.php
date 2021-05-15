<?php

declare(strict_types=1);

namespace ParfumPulse\Product;

use ParfumPulse\Validation\ConstraintViolationListFormatter;
use ParfumPulse\Validation\Constraints\UrlPath;
use ParfumPulse\Validation\Exception\ValidationException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductValidator
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
        $requiredOnCreate = ['url_path'];
        $constraints = [
            'url_path' => [
                new NotBlank([
                    'message' => 'A product can’t have a blank URL path.',
                ]),
                new Length([
                    'min' => 1,
                    'max' => 150,
                    'minMessage' => 'URL path can’t be less than {{ limit }} characters long.',
                    'maxMessage' => 'URL path can’t be more than {{ limit }} characters long.',
                ]),
                new UrlPath(),
            ],
            'free_delivery' => [
                new Type([
                    'type' => 'bool',
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

        if (!$isNew) {
            unset($constraints['url_path']);
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
