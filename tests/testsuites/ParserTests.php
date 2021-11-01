<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mistralys\SprintfParser\FormatParser;
use function Mistralys\SprintfParser\Functions\parseString;

class SprintfInfoTests extends TestCase
{
    /**
     * sprintf allows using literal `%` signs by using
     * a double `%%`. These must be taken into account
     * when parsing a string.
     */
    public function test_escapePercentSign() : void
    {
        $info = new FormatParser('%%s');

        $this->assertFalse($info->hasPlaceholders(), 'Must not detect a placeholder.');
    }

    public function test_matches() : void
    {
        $this->assertTrue(parseString('Some %s placeholder')
            ->hasPlaceholders()
        );
    }
}
