<?php

declare(strict_types=1);

namespace ParfumPulse\Typography;

use Michelf\SmartyPantsTypographer;

class StringNormalizer
{
    public function normalize(string $input): string
    {
        $result = $input;
        $result = $this->transformPunctuation($result);
        $result = $this->replaceDuplicateWhitespace($result);
        $result = $this->removeTrailingSpace($result);
        return $result;
    }

    private function removeTrailingSpace(string $input): string
    {
        return trim($input);
    }

    private function replaceDuplicateWhitespace(string $input): string
    {
        $result = preg_replace('/[[:space:]]+/', ' ', $input);
        if (null === $result) {
            throw new \RuntimeException('preg_replace failed.');
        }
        return $result;
    }

    private function transformPunctuation(string $input): string
    {
        $typographer = new SmartyPantsTypographer();

        $typographer->do_space_colon = 0;
        $typographer->do_space_semicolon = 0;
        $typographer->do_space_marks = 0;
        $typographer->do_space_emdash = 0;
        $typographer->do_space_endash = 0;
        $typographer->do_space_frenchquote = 0;
        $typographer->do_space_thousand = 0;
        $typographer->do_space_unit = 0;

        $typographer->decodeEntitiesInConfiguration();

        return $typographer->transform($input);
    }
}
