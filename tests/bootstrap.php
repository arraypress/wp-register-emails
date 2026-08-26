<?php
/**
 * PHPUnit bootstrap.
 *
 * @package ArrayPress\RegisterEmails
 */

declare( strict_types=1 );

require_once __DIR__ . '/stubs.php';
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

/*
 * And wp-money's functions again. It is a Composer `files` entry, so it
 * already ran when the autoloader was required -- above, but also by phpunit
 * before this file -- and returned without declaring anything because ABSPATH
 * did not exist yet. `require`, not `require_once`: the path is already in the
 * included list, so require_once would do nothing at all.
 */
require dirname( __DIR__ ) . '/vendor/arraypress/wp-money/src/Functions.php';
