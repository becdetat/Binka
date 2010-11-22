<?php
/* object.php
** Base class for all objects in the framework. Inspired by / based on CakePHP.
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class Object {
	function __construct() {}

	// Override this for own implementation
	function toString() {
		return get_class($this);
	}
	
	// From CakePHP:
	//   Calls a method on this object with the given parameters. Provides an OO wrapper
	//   for call_user_func_array, and improves performance by using straight method calls
	//   in most cases.
	function dispatchMethod($method, $params = array()) {
		switch (count($params)) {
			case 0: return $this->{$method}();
			case 1: return $this->{$method}($params[0]);
			case 2: return $this->{$method}($params[0], $params[1]);
			case 3: return $this->{$method}($params[0], $params[1], $params[2]);
			case 4: return $this->{$method}($params[0], $params[1], $params[2], $params[3]);
			case 5: return $this->{$method}($params[0], $params[1], $params[2], $params[3], $params[4]);
			default: return call_user_func_array(array(&$this, $method), $params); break;
		}
	}
};


?>