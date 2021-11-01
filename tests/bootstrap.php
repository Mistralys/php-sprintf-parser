<?php
/**
 * Main bootstrapper used to set up the test-suites environment.
 * 
 * @package SprintfParser
 * @subpackage Tests
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

    declare(strict_types=1);

    const TESTS_ROOT = __DIR__;
    const TESTS_RUNNING = true;

    $autoloader = realpath(TESTS_ROOT.'/../vendor/autoload.php');
    if($autoloader === false) {
        die('Cannot run tests: the autoload is not present. Please run composer update first.');
    }
