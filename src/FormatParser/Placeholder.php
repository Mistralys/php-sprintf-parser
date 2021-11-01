<?php

declare(strict_types=1);

namespace Mistralys\SprintfParser\FormatParser;

use Mistralys\SprintfParser\FormatParser;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\LeftJustify;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\LeftPadOnlyZero;
use Mistralys\SprintfParser\FormatParser\AbstractFlag\PlusMinusPrefix;
use Mistralys\SprintfParser\FormatParser\Placeholder\ArgumentsParser;

class Placeholder
{
    /**
     * @var string
     */
    private string $formatString;

    private string $specifier;

    private ArgumentsParser $arguments;

    /**
     * @param FormatParser $info
     * @param string $formatString
     * @param string $specifier
     * @param string $args
     * @see FormatParser::ERROR_REGEX_FAILED
     */
    public function __construct(FormatParser $info, string $formatString, string $specifier, string $args)
    {
        $this->formatString = $formatString;
        $this->specifier = $specifier;
        $this->arguments = new ArgumentsParser($args);
    }

    /**
     * Gets the full matched format string.
     * @return string
     */
    public function getFormatString() : string
    {
        return $this->formatString;
    }

    /**
     * Whether this is a numbered placeholder, e.g, `%1$s`.
     * @return bool
     */
    public function isNumbered() : bool
    {
        return $this->arguments->hasNumber();
    }

    /**
     * Retrieves the placeholder's number (if it is numbered).
     * @return int The placeholder number, or `0` if it's not numbered.
     */
    public function getNumber() : int
    {
        return $this->arguments->getNumber();
    }

    /**
     * The data type format specifier, e.g. `s` for `string` formatting.
     * @return string
     * @link https://www.php.net/manual/en/function.sprintf.php
     */
    public function getSpecifier() : string
    {
        return $this->specifier;
    }

    public function hasWidth() : bool
    {
        return $this->arguments->hasWidth();
    }

    public function getWidth() : int
    {
        return $this->arguments->getWidth();
    }

    public function hasPrecision() : bool
    {
        return $this->arguments->hasPrecision();
    }

    public function getPrecision() : int
    {
        return $this->arguments->getPrecision();
    }

    public function getFlags() : array
    {
        return $this->arguments->getFlags();
    }

    public function hasFlags() : bool
    {
        return count($this->getFlags()) !== 0;
    }

    public function getPaddingChar() : string
    {
        return $this->arguments->getPaddingChar();
    }

    public function isValid()
    {
        return $this->arguments->isValid();
    }

    public function hasPlusMinusPrefix() : bool
    {
        return $this->hasFlagClass(PlusMinusPrefix::class);
    }

    public function hasOnlyZeroLeftPadding() : bool
    {
        return $this->hasFlagClass(LeftPadOnlyZero::class);
    }

    public function hasLeftJustification() : bool
    {
        return $this->hasFlagClass(LeftJustify::class);
    }

    private function hasFlagClass(string $className) : bool
    {
        $flags = $this->getFlags();

        foreach($flags as $flag)
        {
            if($flag instanceof $className)
            {
                return true;
            }
        }

        return false;
    }
}
