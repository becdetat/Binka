<?php
/* config.php
** Static object for managing the application config
** Well it's meant to be static but I had to defile it for PHP4
** BJS20091002
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

// redefine a global for the former static fields
$Config_config = array();

class Config extends Object {
	//static $config = array();
	
	//static 
	function set($k, $v) {
global $Config_config;
		//self::$config[$k] = $v;
$Config_config[$k] = $v;
	}
	
	//static 
	function get($k) {
		//return self::$config[$k];
global $Config_config;
return $Config_config[$k];
	}
};

?>