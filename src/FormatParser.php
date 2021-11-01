<?php
/**
 * File containing the class {@see \Mistralys\SprintfParser\FormatParser}.
 *
 * @package SprintfParser
 * @subpackage FormatParser
 * @see \Mistralys\SprintfParser\FormatParser
 */

declare(strict_types=1);

namespace Mistralys\SprintfParser;

use Mistralys\SprintfParser\FormatParser\ParserException;
use Mistralys\SprintfParser\FormatParser\Placeholder;

/**
 * Main parser class used to detect `sprintf` format placeholders
 * in a string.
 *
 * @package SprintfParser
 * @subpackage FormatParser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FormatParser
{
    const ERROR_REGEX_FAILED = 96001;
    const ERROR_NO_PLACEHOLDERS_AVAILABLE = 96002;
    const ERROR_TOKEN_INDEX_NOT_FOUND = 96003;

    /**
     * @var string
     */
    private string $text;

    /**
     * @var bool 
     */
    private bool $parsed = false;

    /**
     * @var Placeholder[]
     */
    private array $placeholders = array();

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * Whether any `sprintf` format placeholders were detected
     * in the subject string.
     *
     * @return bool
     * @throws ParserException
     */
    public function hasPlaceholders() : bool
    {
        $this->parse();
        
        return !empty($this->placeholders);
    }

    /**
     * %[argnum$][flags][width][.precision]specifier
     *
     * @throws ParserException
     * @see FormatParser::ERROR_REGEX_FAILED
     */
    private function parse() : void
    {
        if($this->parsed === true)
        {
            return;
        }
        
        $this->parsed = true;

         $this->text = $this->prepareString($this->text);

        // All format strings contain the % character. If none is
        // present in the string, we can be certain there are no
        // placeholders.
        if(!strstr($this->text, '%'))
        {
            return;
        }

        $this->detectPlaceholders();

        // Free memory
        $this->text = '';
    }

    /**
     * Retrieves all placeholders that have been detected
     * in the subject string, in the order they appear in
     * the string.
     *
     * @return Placeholder[]
     * @throws ParserException
     */
    public function getPlaceholders() : array
    {
        $this->parse();

        return $this->placeholders;
    }

    /**
     * Retrieves the very first placeholder found in the
     * subject string.
     *
     * NOTE: Will throw an exception if no placeholders
     * have been found. Use {@see FormatParser::hasPlaceholders()}
     * first as applicable.
     *
     * @return Placeholder
     *
     * @throws ParserException
     * @see FormatParser::ERROR_NO_PLACEHOLDERS_AVAILABLE
     */
    public function getFirstPlaceholder() : Placeholder
    {
        $placeholders = $this->getPlaceholders();

        if(!empty($placeholders))
        {
            return array_shift($placeholders);
        }

        throw new ParserException(
            'No placeholders in text.',
            'Cannot get the first placeholder: there are no placeholders in the text.',
            self::ERROR_NO_PLACEHOLDERS_AVAILABLE
        );
    }

    /**
     * Finds all `sprintf` placeholder instances in the subject string.
     *
     * @return array<int,array<int,string>>
     *
     * @throws ParserException
     * @see FormatParser::ERROR_REGEX_FAILED
     */
    private function findMatches() : array
    {
        $matches = array();
        if (preg_match_all("/%(\N*)([bcdeEfFgGhHosuxX])/sU", $this->text, $matches, PREG_PATTERN_ORDER) === false)
        {
            throw new ParserException(
                'Regex error finding format strings.',
                '',
                self::ERROR_REGEX_FAILED
            );
        }

        return $matches;
    }

    private function prepareString(string $text) : string
    {
        return str_replace('%%', '__ESCAPED_PERCENT__', $text);
    }

    /**
     * Detects all placeholder details in the regex matches,
     * and populates the placeholders list.
     *
     * @throws ParserException
     * @see FormatParser::ERROR_REGEX_FAILED
     */
    private function detectPlaceholders() : void
    {
        $matches = $this->findMatches();

        foreach ($matches[0] as $idx => $matchedText)
        {
            $this->placeholders[] = new Placeholder($this, $matchedText, $matches[2][$idx], $matches[1][$idx]);
        }
    }
}
