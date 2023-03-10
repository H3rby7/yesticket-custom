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
$_composer_home = \getenv( 'COMPOSER_HOME' );
if ( ! $_composer_home ) {
	$_composer_home = '/tmp';
}

\error_log("Tests Lib dir is '$_tests_dir'");
\error_log("Composer home is '$_composer_home'");

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Autoload
require_once $_composer_home . '/vendor/autoload.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require \dirname( \dirname( __FILE__ ) ) . '/yesticket.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
