<?php
/* ActionResult
** Base class for all results returned by an action
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class ActionResult extends Object {
	// Subclasses of ActionResult that create output should override render() and returnRender()
	function render() {}
	// renderToString() is intended when doing an inner dispatch and isn't always appropriate
	function renderToString() { return ''; }
};

?>