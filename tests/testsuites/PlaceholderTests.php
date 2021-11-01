<?php

declare(strict_types=1);

use Mistralys\SprintfParser\FormatParser\AbstractFlag\PlusMinusPrefix;
use PHPUnit\Framework\TestCase;
use function Mistralys\SprintfParser\Functions\parseFormat;
use function Mistralys\SprintfParser\Functions\parseString;

class PlaceholderTests extends TestCase
{
    public function test_isNumbered() : void
    {
        $this->assertFalse(parseFormat('%s')->isNumbered());
        $this->assertTrue(parseFormat('%1$s')->isNumbered());
        $this->assertSame(4, parseFormat('%4$s')->getNumber());
    }

    public function test_getSpecifier() : void
    {
        $this->assertSame('s', parseFormat('%s')->getSpecifier());
        $this->assertSame('d', parseFormat('%d')->getSpecifier());
    }

    public function test_getWidth() : void
    {
        $this->assertFalse(parseFormat('%s')->hasWidth());
        $this->assertSame(0, parseFormat('%d')->getWidth());

        $this->assertTrue(parseFormat('%2d')->hasWidth());
        $this->assertSame(2, parseFormat('%2d')->getWidth());
    }

    public function test_getPrecision() : void
    {
        $this->assertFalse(parseFormat('%s')->hasPrecision());
        $this->assertSame(0, parseFormat('%d')->getPrecision());

        $this->assertTrue(parseFormat('%02.2f')->hasPrecision());
        $this->assertSame(2, parseFormat('%02.2f')->getPrecision());
    }

    public function test_getFlags() : void
    {
        $this->assertTrue(parseFormat('%+4d')->hasFlags());

        $flags = parseFormat('%+4d')->getFlags();

        $this->assertNotEmpty($flags);
        $this->assertInstanceOf(PlusMinusPrefix::class, $flags[0]);
    }

    public function test_everythingAtOnce() : void
    {
        $placeholder = parseFormat("%1$+-0'*4.3d");

        $this->assertTrue($placeholder->isValid());
        $this->assertTrue($placeholder->isNumbered());
        $this->assertEquals('d', $placeholder->getSpecifier());
        $this->assertSame(1, $placeholder->getNumber());
        $this->assertEquals('*', $placeholder->getPaddingChar());
        $this->assertSame(3, $placeholder->getPrecision());
        $this->assertSame(4, $placeholder->getWidth());
        $this->assertTrue($placeholder->hasPlusMinusPrefix());
        $this->assertTrue($placeholder->hasLeftJustification());
        $this->assertTrue($placeholder->hasOnlyZeroLeftPadding());
    }

    public function test_parseString() : void
    {
        $string = 'The price of product %s is %1$.2d EUR with a rebate of 20%%.';

        $parser = parseString($string);

        $this->assertCount(2, $parser->getPlaceholders());
    }
}
