<?php
/**
 * PHPUnit bootstrapping file
 *
 * @copyright Copyright (c) 2015, final gene <info@final-gene.de>
 * @author    Frank Giesecke <frank.giesecke@final-gene.de>
 */

/**
 * Prevent polluting $GLOBALS
 * Try to find init_application file to run tests in an application
 */
function findInitApplication()
{
    $path = new \SplFileInfo(__DIR__);
    do {
        $intiFile = new \SplFileInfo($path . '/init_application.php');
        if ($intiFile->isReadable()) {
            /** @noinspection PhpIncludeInspection */
            return require $intiFile->getPathname();
        }

        $path = $path->getPathInfo();
    } while (!empty($path->getPath()));

    return false;
}

if (false == findInitApplication()) {
    error_reporting(-1);

    ini_set('default_charset', 'utf-8');
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    /**
     * Files will be created as -rw-rw-r--
     * Directories will be creates as drwxrwxr-x
     */
    umask(0002);

    chdir(__DIR__);

    require __DIR__ . '/../vendor/autoload.php';
}
