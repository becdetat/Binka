<?php
/* bootstrap.php
** Loads the various libraries and support files
** BJS20091001
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

// Disabling this as the changes I needed to make to get Slab working on PHP4 broke PHP5's strict standards...
//error_reporting(-1);

// Global helpers and third party libraries:
require_once(SLAB_LIB.'/global_functions.php');
// Slab targets PHP4 so to work on PHP5 the DOMXML functions need to be restored. This uses
// a GPLed library by Alexandre Alapetite (http://alexandre.alapetite.fr/doc-alex/domxml-php4-php5/)
if (PHP_VERSION >= '5') {
	require_once(SLAB_LIB.'/third_party/domxml-php4-to-php5.php');
}

$SLAB_EXECUTION_TIME_START = getMicrotime();

require_once(SLAB_LIB.'/object.php');

// load the Config class, then the initial configuration, then attempt to load the app config over the top
require_once(SLAB_LIB.'/config.php');
require_once(SLAB_LIB.'/init_config.php');
if (file_exists(SLAB_APP.'/config.php')) {
	require_once(SLAB_APP.'/config.php');
} else {
	e('<p>The application configuration file must be created at <code>'.SLAB_APP.'/config.php</code></p>');
	die();
}

// include classes
require_once(SLAB_LIB.'/inflector.php');
require_once(SLAB_LIB.'/dispatcher.php');
require_once(SLAB_LIB.'/model.php');
require_once(SLAB_LIB.'/view.php');
require_once(SLAB_LIB.'/controller.php');
require_once(SLAB_LIB.'/component.php');
require_once(SLAB_LIB.'/helper.php');
require_once(SLAB_LIB.'/database.php');
require_once(SLAB_LIB.'/security.php');
require_once(SLAB_LIB.'/unit_test_case.php');
require_once(SLAB_LIB.'/unit_test_suite.php');
// ActionResult and subclasses
require_once(SLAB_LIB.'/action_result.php');
require_once(SLAB_LIB.'/view_result.php');
require_once(SLAB_LIB.'/partial_result.php');
require_once(SLAB_LIB.'/redirect_result.php');
require_once(SLAB_LIB.'/redirect_refresh_result.php');
require_once(SLAB_LIB.'/text_result.php');
require_once(SLAB_LIB.'/json_result.php');
require_once(SLAB_LIB.'/ajax_result.php');
require_once(SLAB_LIB.'/file_result.php');

// init Security
Security::init();

// attempt to load the app AppController, otherwise fall back on the placeholder
if (file_exists(SLAB_APP.'/app_controller.php')) {
	require_once(SLAB_APP.'/app_controller.php');
} else {
	require_once(SLAB_LIB.'/app_controller.php');
}

?>