<?php
/* RedirectRefreshResult
** A kind of ActionResult that redirects to the provided url using the Refresh header pragma rather then the Location header
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class RedirectRefreshResult extends ActionResult {
	var $url = null;
	
	function __construct($url) {
		$this->url = $url;
	}
	
	function render() {
		header('Status: 200');
		header('Refresh: 0; '.Dispatcher::url($this->url));
	}
	
	function returnRender() {
		return $this->url;
	}
};

?>