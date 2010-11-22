<?php
/* TextResult
** A kind of ActionResult that just renders the provided string
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class TextResult extends ActionResult {
	var $s = null;
	
	function __construct($s) {
		$this->s = $s;
	}
	
	function render() {
		e($this->s);
	}
	
	function returnRender() {
		return $this->s;
	}
};

?>