<?php

/**
 * Test bootstrap: uses the Silverstripe test bootstrap of the project the
 * module is installed in (vendor/atwx/<module> or a path repository under
 * packages/<module>), or of the module's own vendor folder when standalone.
 */
$candidates = [
    __DIR__ . '/../../../../vendor/silverstripe/framework/tests/bootstrap.php',
    __DIR__ . '/../../../vendor/silverstripe/framework/tests/bootstrap.php',
    __DIR__ . '/../vendor/silverstripe/framework/tests/bootstrap.php',
];
foreach ($candidates as $bootstrap) {
    if (file_exists($bootstrap)) {
        // The phpunit phar has no project autoloader of its own
        require dirname($bootstrap, 4) . '/autoload.php';
        require $bootstrap;
        return;
    }
}
throw new RuntimeException('No Silverstripe test bootstrap found. Run `composer install` first.');
