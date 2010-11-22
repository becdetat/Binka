<?php
/* RedirectResult
** A kind of ActionResult that redirects to the provided url
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class RedirectResult extends ActionResult {
	var $url = null;
	
	function __construct($url) {
		$this->url = $url;
	}
	
	function render() {
		header('Status: 302');
		header('Location: '.Dispatcher::url($this->url));
	}
	
	function returnRender() {
		return $this->url;
	}
};

?>