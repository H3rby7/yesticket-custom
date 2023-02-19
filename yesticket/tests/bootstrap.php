<?php
/**
 * PHPUnit bootstrap file
 *
 * @package YesTicket
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
$_composer_home = getenv( 'COMPOSER_HOME' );
if ( ! $_composer_home ) {
	$_composer_home = '/tmp';
}
// $_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
// if ( ! $_polyfills_path ) {
// 	$_polyfills_path = "$_composer_home/vendor/yoast/phpunit-polyfills";
// }
// $_core_dir = getenv( 'WP_CORE_DIR' );
// if ( ! $_core_dir ) {
// 	$_core_dir = '/tmp/vendor/johnpbloch/wordpress-core';
// }

// set_include_path(get_include_path() . PATH_SEPARATOR . $_core_dir);

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// require_once "$_polyfills_path/phpunitpolyfills-autoload.php";

// Autoload
require_once $_composer_home . '/vendor/autoload.php';

// require_once $_tests_dir . '/includes/testcase.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/yesticket.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Bootstrap WP_Mock to initialize built-in features
WP_Mock::bootstrap();