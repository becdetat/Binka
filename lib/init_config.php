<?php
/* init_config.php
** Initial (default) configuration
** This is overwritten by the application config.php
** BJS20091001
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

// Application
Config::set('app.default_route', '');
Config::set('app.default_components', array('db', 'cookie', 'session', 'file'));
Config::set('app.default_helpers', array('html', 'number'));
Config::set('app.load_model_schemas', true);
Config::set('app.url_rewriting', false);

// Database
Config::set('db.driver', 'mysql');
Config::set('db.host', '');
Config::set('db.port', '');
Config::set('db.login', '');
Config::set('db.password', '');
Config::set('db.database', '');
Config::set('db.tablePrefix', '');

// Debugging/logging
Config::set('debug.show_execution_time', false);

// Security
Config::set('security.encryption_key', 'a94a8fe5ccb19ba61c4c0873d391e987982fbbd3');
// This should be set to true for better encryption/decryption, but I'm having trouble with loading the mcrypt module
Config::set('security.use_mcrypt', false);

// Cookies
Config::set('cookie.default_cookie_name', 'slab_data');
Config::set('cookie.expire', 0);
Config::set('cookie.domain', false);
Config::set('cookie.secure', false);
Config::set('cookie.httponly', false);
Config::set('cookie.use_encryption', true);

// Sessions
Config::set('session.cookie_name', 'session');
Config::set('session.timeout', 60*60);
Config::set('session.type', 'file');	// 'cookie'
Config::set('session.id_type', 'cookie');
Config::set('session.database_table', 'slab_sessions');
Config::set('session.filename_prefix', 'session_');

?>