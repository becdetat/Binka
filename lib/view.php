<?php
/* view.php
** Given view data, a view, and a layout, renders the result
** BJS20091002
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class View extends Object {
	var $viewName = null;
	var $viewFilename = null;
	var $layoutName = 'default';
	var $layoutFilename = null;
	var $controller = null;
	var $data = array();		// this is set by the controller (either by $controller->view->data[] or $controller->set())
	var $pageTitle = '';		// used by the layout, set by the controller or the view (which will override anything set by the controller as it runs after the action method)
	
	var $helperRefs = array();
	
	
	function __construct($controller = null) {
		$this->controller = $controller;
	}
	
	
	function setView($viewName) { $this->viewName = $viewName; }
	function setLayout($layoutName) { $this->layoutName = $layoutName; }
	
	function render() {
		$this->__checkViewAndLayoutFilenames();
		
		// render the view
		$output = $this->__render($this->viewFilename);
		
		// render the layout
		if  (isset($this->layoutFilename)) {
			$this->data['pageContent'] = $output;
			$output = $this->__render($this->layoutFilename);
		}
		
		return $output;
	}
	
	function __checkViewAndLayoutFilenames() {
		// make sure the view file (and layout file if set) exist
		if (!isset($this->viewFilename)) {
			$this->viewFilename = SLAB_APP.'/views/'.$this->viewName.'.php';
		}
		if (!file_exists($this->viewFilename)) {
			e('<p>The <em>'.$this->viewName.'</em> view could not be found at <code>'.$this->viewFilename.'</code></p>');
			die();
		}
		if (!isset($this->layoutFilename)) {
			if (isset($this->layoutName)) {
				$this->layoutFilename = SLAB_APP.'/views/layouts/'.$this->layoutName.'.php';
				// special case: if the layout is 'blank' and no blank layout exists, just null the layout filename to avoid rendering any layout
				if ($this->layoutName == 'blank' && !file_exists($this->layoutFilename)) {
					$this->layoutFilename = null;
				}
			}
		}
		if (isset($this->layoutFilename) && !file_exists($this->layoutFilename)) {
			e('<p>The <em>'.$this->layoutName.'</em> layout could not be found at <code>'.$this->layoutFilename.'</code></p>');
			die();
		}
	}
	
	function __render($filename) {
		$output = '';

		// Extract the data array (so $data['aValue'] will be available to the view as $aValue)
		extract($this->data);

		// Extract the helpers
		extract($this->helperRefs);
		
		// manually put $this->pageTitle into the local scope (it will be assigned back after including the file)
		$pageTitle = $this->pageTitle;
		
		// include the file in the context of this method and store the output
		// ob_clean() issues a notice if no buffer to delete
		if (ob_get_contents() !== false) {
			$output = ob_get_contents();
			@ob_clean();
		}
		ob_start();
		include($filename);
		$output .= ob_get_contents();
		ob_end_clean();
		ob_start();
		
		// assign $pageTitle back into $this->pageTitle
		$this->pageTitle = $pageTitle;
		
		return $output;
	}
};

?>