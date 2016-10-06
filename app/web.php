<?php
/*
 * This could be loaded on a very old version of PHP so no syntax/methods over 5.2 in this file.
 */
use Bolt\Exception\BootException;

if (version_compare(PHP_VERSION, '5.5.9', '<')) {
    require_once dirname(dirname(__FILE__)) . '/src/Exception/BootException.php';

    BootException::earlyExceptionVersion();
}

require_once dirname(__FILE__) . '/../src/Bootstrap.php';

return Bolt\Bootstrap::web();
