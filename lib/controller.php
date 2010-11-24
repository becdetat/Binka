<?php
/* controller.php
** Base class for controllers
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class Controller extends Object {
	var $name = null;			// the name of the controller, override this in the implementation, eg 'TestController'
	var $models = array();		// models used in the controller, as ('[ModelName]' => '[table_name]'), eg ('MyModel' => 'my_model')
	var $components = array();	// components available to the controller, override this in the implementation, eg ('db', 'security', 'file')
	var $helpers = array();		// helpers available to the view, override this in the implementation, eg ('html', 'number')
	
	var $actionName = '';		// the name of the current action, this is set by the dispatcher
	var $params = array();		// the parameters passed to the action from the /c/a/p url, this is set by the dispatcher
	
	var $methods = array();			// methods in the controller, used to check actions in Dispatcher. Set in Controller::__construct().
	var $modelRefs = array();		// references to all models, set in Dispatcher::loadController()
	var $componentRefs = array();	// references to all components, set in Dispatcher::loadController()
	
	var $data = array();		// the incoming request values (combines $_REQUEST and $_FILES into one fruity cocktail)
	
	var $view = null;			// instance of a view
	
	var $actionResult = null;
	
	
	function __construct() {
		$this->view = new View($this);
	
		// get the methods used in the controller
		$childMethods = get_class_methods($this);
		foreach ($childMethods as $key => $value) {
			$childMethods[$key] = strtolower($value);
		}
		$parentMethods = get_class_methods('Controller');
		foreach ($parentMethods as $key => $value) {
			$parentMethods[$key] = strtolower($value);
		}
		$this->methods = array_diff($childMethods, $parentMethods);
	}
	
	// These get called immediately before and after the action is executed.
	// These can be overridden in the AppController however if they are also overridden
	// in a normal controller, the first statement _must_ be parent::beforeAction() or parent::afterAction()
	function beforeAction() {}
	function afterAction() {}
	
	// Helper methods
	function url($u) {
		return Dispatcher::url($u);
	}
	
	
	// set $this->view->data
	function set($key, $value) {
		$this->view->data[$key] = $value;
	}
	
	// These methods are used to generate the result of an action
	// Eg: to redirect to another action: $this->redirect('/c/a/p');
	// to render the default view (this is done by default): $this->render();
	// to render a given view: $this->view('/controller/view')
	// to render a given view and layout: $this->view('/controller/view', 'another_layout');
	// to render a view to a blank layout (partial view): $this->renderPartial();
	// to return some JSON: $this->json($model);
	// to return plain text: $this->text('a string');
	// to write a buffer as an inline file: $this->file($filename, $data); (or fileInline())
	// to write a file as an attachment: $this->fileAttachment($filename, $data);
	// to write a 200 OK response: $this->ajaxSuccess()
	// or a 500 internal error: $this->ajaxFailure()
	// or a 404 file not found: $this->fileNotFound()
	
	function view($view = null, $layout = null) {
		if (isset($view)) {
			$this->view->setView($view);
		}
		if (isset($layout)) {
			$this->view->setLayout($layout);
		}
		$this->actionResult = new ViewResult($this->view);
	}	
	function partial() {
		$this->actionResult = new PartialResult($this->view);
	}
	function redirect($u) {
		$this->actionResult = new RedirectResult($u);
	}
	function redirectRefresh($u) {
		$this->actionResult = new RedirectRefreshResult($u);
	}
	function text($s) {
		$this->actionResult = new TextResult($s);
	}	
	function json($o) {
		$this->actionResult = new JsonResult($o);
	}
	// file() is a synonym for fileInline()
	function file($filename, $data, $encoding='binary') { $this->fileInline($filename, $data, $encoding); }	
	function fileInline($filename, $data, $encoding='binary') {
		$this->actionResult = new FileResult($filename, $data, $encoding, 'inline');
	}
	function fileAttachment($filename, $data, $encoding='binary') {
		$this->actionResult = new FileResult($filename, $data, $encoding, 'attachment');
	}
	function ajax($statusCode, $data = null) {
		$this->actionResult = new AjaxResult($statusCode, $data);
	}
	function ajaxSuccess($data = null) {
		$this->actionResult = new AjaxResult(200, $data);
	}
	function ajaxFailure($data = null) {
		$this->actionResult = new AjaxResult(500, $data);
	}
	function ajaxError($data = null) {
		$this->ajaxFailure($data);
	}
	function fileNotFound() {
		$this->actionResult = new AjaxResult(404, null);
	}
	// excute another action and use the result of that action for this action (nested dispatch)
	function action($cap, $data = null) {
		$this->actionResult = Dispatcher::dispatch($cap, $data);
	}
	
	// This should only be used outside of a controller action as it is a dirty way of redirecting.
	// It dies after setting the header so cookies won't be saved etc
	// Preferred method is to "return redirect('url')" inside the action.
	function redirectImmediate($u) {
		header('Status: 200');
		header('Location: '.Dispatcher::url($u));
		die();
	}
};

?>