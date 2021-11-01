<?php
/**
 * File containing global utility functions for the format parser.
 *
 * @package SprintfParser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see \Mistralys\SprintfParser\Functions\parseString()
 * @see \Mistralys\SprintfParser\Functions\parseFile()
 */

declare(strict_types=1);

namespace Mistralys\SprintfParser\Functions;

use Mistralys\SprintfParser\FormatParser;
use Mistralys\SprintfParser\FormatParser\ParserException;
use Mistralys\SprintfParser\FormatParser\Placeholder;

const ERROR_FILE_NOT_FOUND = 96201;
const ERROR_CANNOT_READ_FILE_CONTENTS = 96202;

/**
 * Parses a single `sprintf` format string to access
 * information on it.
 *
 * @param string $format A format string, e.g. "%s"
 * @return Placeholder
 * @throws ParserException
 */
function parseFormat(string $format) : Placeholder
{
    return parseString($format)->getFirstPlaceholder();
}

/**
 * Parses the subject string and returns the parser
 * instance to access information on all `sprintf`
 * formatting placeholders found in the string.
 *
 * @param string $subject
 * @return FormatParser
 */
function parseString(string $subject) : FormatParser
{
    return new FormatParser($subject);
}

/**
 * Parses the specified file and returns the parser
 * instance to access the placeholder information for
 * the contents of the file.
 *
 * @param string $filePath
 * @return FormatParser
 *
 * @throws ParserException
 * @see ERROR_FILE_NOT_FOUND
 * @see ERROR_CANNOT_READ_FILE_CONTENTS
 */
function parseFile(string $filePath) : FormatParser
{
    $path = realpath($filePath);

    if($path === false)
    {
        throw new ParserException(
            'File not found.',
            sprintf(
                'The file [%s] could not be found in the filesystem.',
                $filePath
            ),
            ERROR_FILE_NOT_FOUND
        );
    }

    $content = file_get_contents($path);

    if($content !== false)
    {
        return parseString($content);
    }

    throw new ParserException(
        'Could not read file contents',
        sprintf(
            'The file [%s] exists in the filesystem, but its contents could not be read.',
            $filePath
        ),
        ERROR_CANNOT_READ_FILE_CONTENTS
    );
}
