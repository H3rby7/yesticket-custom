<?php
/**
 * PHPUnit bootstrap file
 *
 * @package YesTicket
 */

$_tests_dir = \getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}
$_composer_vendor = \getenv( 'COMPOSER_VENDOR_DIR' );
if ( ! $_composer_vendor ) {
	$_composer_vendor = '/tmp/vendor';
}

\error_log(__FILE__ . ": Tests Lib dir is '$_tests_dir'");
\error_log(__FILE__ . ": Composer vendor is '$_composer_vendor'");

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Autoload
require_once $_composer_vendor . '/autoload.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require \dirname( \dirname( __FILE__ ) ) . '/yesticket.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
