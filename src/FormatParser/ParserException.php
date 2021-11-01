<?php

declare(strict_types=1);

namespace Mistralys\SprintfParser\FormatParser;

use Throwable;
use Exception;

class ParserException extends Exception
{
    private string $details = '';

    public function __construct(string $message, string $details = '', int $code = 0, ?Throwable $previous = null)
    {
        // When running tests, append the details to the message
        // so the full message is available in the PHPUnit exception
        // display.
        if(defined('TESTS_RUNNING') && TESTS_RUNNING === true)
        {
            $message .= ' '.$details;
        }

        $this->details = $details;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getDetails() : string
    {
        return $this->details;
    }
}
