<?php

// Application
Config::set('app.default_route', '/pages/index');
Config::set('app.default_components', array('db', 'cookie', 'session', 'file', 'image'));
Config::set('app.default_helpers', array('html', 'number'));
Config::set('app.load_model_schemas', true);
Config::set('app.url_rewriting', true);

// Session
Config::set('session.type', 'file');
Config::set('session.cookie_name', 'MYAPP_session');

// Database
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	// development settings
	Config::set('db.driver', 'mysql');
	Config::set('db.host', 'localhost');
	Config::set('db.login', 'root');
	Config::set('db.password', 'password');
	Config::set('db.database', 'DATABASE_NAME');
	Config::set('db.tablePrefix', '');
} else {
	// live settings
	Config::set('db.driver', 'mysql');
	Config::set('db.host', 'mysql.server');
	Config::set('db.port', '3306');
	Config::set('db.login', 'LIVE_USERNAME');
	Config::set('db.password', 'LIVE_PASSWORD');
	Config::set('db.database', 'LIVE_DATABASE_NAME');
	Config::set('db.tablePrefix', '');
}

// Debugging/logging
Config::set('debug.show_execution_time', true);


?>