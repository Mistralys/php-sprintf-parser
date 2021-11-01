<?php

declare(strict_types=1);

namespace Mistralys\SprintfParser\FormatParser\Placeholder;

use Mistralys\SprintfParser\FormatParser\AbstractFlag;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\LeftJustify;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\LeftPadOnlyZero;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\PlusMinusPrefix;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\SpacePadding;

class ArgumentsParser
{
    private bool $hasNumber = false;
    private bool $hasPrecision = false;
    private bool $hasWidth = false;

    /**
     * @var AbstractFlag[]
     */
    private array $flags = array();

    private int $precision = 0;
    private int $number = 0;
    private string $paddingChar = ' ';

    private bool $valid = true;
    private string $errorMessage = '';
    private string $workString;
    private int $width = 0;

    /**
     * Stores the indexes of the capturing groups
     * in the width and flags regex, to correctly
     * assign them in the resulting array.
     *
     * @var string[]
     * @see ArgumentsParser::analyzeFlagMatches()
     */
    private array $flagAndWidthIndexes = array(
        1 => 'flags',
        2 => 'width',
        3 => 'width',
        4 => 'flags'
    );

    public function __construct(string $arguments)
    {
        $this->workString = $arguments;

        $this->parseArguments();
    }

    public function isValid() : bool
    {
        return $this->valid;
    }

    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }

    public function getFlags() : array
    {
        return $this->flags;
    }

    public function hasWidth() : bool
    {
        return $this->hasWidth;
    }

    public function getWidth() : int
    {
        return $this->width;
    }

    public function hasNumber() : bool
    {
        return $this->hasNumber;
    }

    public function getNumber() : int
    {
        return $this->number;
    }

    public function hasPrecision() : bool
    {
        return $this->hasPrecision;
    }

    public function getPrecision() : int
    {
        return $this->precision;
    }

    public function getPaddingChar() : string
    {
        return $this->paddingChar;
    }

    private function makeError(string $message) : void
    {
        $this->valid = false;
        $this->errorMessage = $message;
    }

    private function parseArguments() : void
    {
        $this->detectNumber();

        if (!$this->valid)
        {
            return;
        }

        $this->detectPrecision();

        if (!$this->valid)
        {
            return;
        }

        $this->detectPaddingChar();

        $this->detectFlagsAndWidth();
    }

    private function detectRegexMatch(string $checkChar, string $regex, string $errorMessage) : string
    {
        if (strstr($this->workString, $checkChar) === false)
        {
            return '';
        }

        $matches = array();
        preg_match_all($regex, $this->workString, $matches, PREG_PATTERN_ORDER);

        if (empty($matches[0]))
        {
            $this->makeError($errorMessage);
            return '';
        }

        $this->workString = str_replace($matches[0][0], '', $this->workString);

        return $matches[1][0];
    }

    private function detectNumber() : void
    {
        $match = $this->detectRegexMatch(
            '$',
            '/\A([0-9])+\$/sU',
            'Invalid placeholder number syntax.'
        );

        if (!$this->valid || empty($match))
        {
            return;
        }

        $this->hasNumber = true;
        $this->number = intval($match);
    }

    private function detectPrecision() : void
    {
        $match = $this->detectRegexMatch(
            '.',
            '/\.([0-9]+)\Z/sU',
            'Invalid precision information after the dot character.'
        );

        if (!$this->valid || empty($match))
        {
            return;
        }

        $this->hasPrecision = true;
        $this->precision = intval($match);
    }

    private function detectPaddingChar() : void
    {
        $match = $this->detectRegexMatch(
            "'",
            '/\'(\N)/sU',
            'Invalid padding character format.'
        );

        if (!$this->valid || mb_strlen($match) === 0)
        {
            return;
        }

        $this->paddingChar = $match;
    }

    /**
     * The flags and width can be distinguished by the fact
     * that while the flags can contain a zero (0), the width
     * may not be zero, or start with a zero.
     *
     * The regex solves this by using three alternatives:
     *
     * - flags + width
     * - width only
     * - flags only
     */
    private function detectFlagsAndWidth() : void
    {
        // The flags and width can be empty once all
        // the other parts have been stripped out.
        if (empty($this->workString))
        {
            return;
        }

        $matches = array();
        preg_match_all('/([-+ 0]+)([1-9][0-9]?)|([1-9][0-9]?)|([-+ 0]+)/s', $this->workString, $matches, PREG_PATTERN_ORDER);

        $result = $this->analyzeFlagMatches($matches);

        if (isset($result['flags']))
        {
            $this->detectFlags($result['flags']);
        }

        if (isset($result['width']))
        {
            $this->detectWidth($result['width']);
        }
    }

    /**
     * @param array<int,array<int,string>> $matches
     * @return array<string,string>
     */
    private function analyzeFlagMatches(array $matches) : array
    {
        $result = array();

        foreach ($matches as $index => $values)
        {
            if (!isset($this->flagAndWidthIndexes[$index]) || !isset($values[0]))
            {
                continue;
            }

            $length = mb_strlen($values[0]);
            if ($length !== false && $length > 0)
            {
                $result[$this->flagAndWidthIndexes[$index]] = $values[0];
            }
        }

        return $result;
    }

    private function string2chars(string $subject) : array
    {
        $length = strlen($subject);
        $result = array();

        for($i=0; $i<$length; $i++)
        {
            $result[] = substr($subject, $i, 1);
        }

        return array_unique($result);
    }

    private function detectFlags(string $flags) : void
    {
        $chars = $this->string2chars($flags);

        foreach($chars as $char)
        {
            switch($char)
            {
                case '+':
                    $this->flags[] = new PlusMinusPrefix();
                    break;

                case '-':
                    $this->flags[] = new LeftJustify();
                    break;

                case '0':
                    $this->flags[] = new LeftPadOnlyZero();
            }
        }
    }

    private function detectWidth(string $width) : void
    {
        $this->width = intval($width);
        $this->hasWidth = true;
    }
}
