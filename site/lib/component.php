<?php
/* component.php
** Base class for controller components
** BJS20090403
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

class Component extends Object {
	// The name of the component, eg 'SecurityComponent'
	var $name = null;
	
	// reference to the controller
	var $controller = null;

	
	// This gets called after dispatcher, controller, etc are passed but before beforeAction.
	// It is for initialisation that can't happen in the constructor but that doesn't rely on other components etc being set up yet
	// This is where configuration can be loaded
	function init() {}
	// This gets called after afterAction
	function shutdown() {}
	
	// These get called immediately before and after the action is executed.
	// When beforeAction() is called, all other components should be initialised and usable
	function beforeAction() {}
	function afterAction() {}
}
?>