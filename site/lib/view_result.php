<?php
/* ViewResult
** A kind of ActionResult that renders the provided View
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class ViewResult extends ActionResult {
	var $view = null;
	
	function __construct($view) {
		$this->view = $view;
	}
	
	function render() {
		e($this->returnRender());
	}
	
	function returnRender() {
		return $this->view->render();
	}
};

?>