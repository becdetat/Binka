<?php
/* dispatcher.php
** Originally based on CakePHP's Dispatcher class but with a lot less magic
** The dispatcher is (almost) stateless and used across the app via static methods.
** The $inDispatch state is used to distinguish between the top-level call to Dispatch() and nested calls to Dispatch()
** (which can be done within views and actions) so that the component shutdown only happens after the top-level dispatch
** completes.
** This class has now also been defaced by making it not really static as PHP4 doesn't support static class members.
** Die already PHP4.
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

$Dispatcher_componentRefs = array();
$Dispatcher_helperRefs = array();
$Dispatcher_baseUrl = '';
$Dispatcher_inDispatch = false;

class Dispatcher extends Object {
	//var $componentRefs = array();
	//var $helperRefs = array();
	//var $baseUrl = '';
	//var $inDispatch = false;

	//static
	function getBaseUrl() {
		//return Dispatcher::$baseUrl;
		global $Dispatcher_baseUrl;
		return $Dispatcher_baseUrl;
	}
	
	function getFilename($filename) {
		return dirname(SLAB_ROOT).$filename;
	}

	// returns a working url for the given /c/a/p triad
	// TODO: this will need to be extended for mod_rewrite support
	//static
	function url($cap) {
		$root = dirname(env('PHP_SELF'));
		if ($root == '/') {
			$root = '';
		}
	
		// if $cap is actually a request for a physical file, return the correct url (remembering $html->url() returns
		// a url relative to the path of the init file)		
		if (file_exists(dirname(SLAB_ROOT).$cap)) {
			return $root.$cap;
		}

		if (Config::get('app.url_rewriting')) {
			$newUrl = $root.$cap;
		} else {
			$newUrl = env('PHP_SELF').'?slab_url='.str_replace('?', '&amp;', substr($cap, 1, strlen($cap)-1));
		}
	
		// If the Session component is loaded, 
		// and the session id is persisted via the url, and there is an active
		// session, include the session id in the url:
global $Dispatcher_componentRefs;
		if (
			//!empty(self::$componentRefs['session']) 
!empty($Dispatcher_componentRefs['session'])
			//&& self::$componentRefs['session']->sessionIDType == 'url' 
&& $Dispatcher_componentRefs['session']->sessionIDType == 'url' 
			//&& self::$componentRefs['session']->inSession
&& $Dispatcher_componentRefs['session']->inSession
			) {
			
			$newUrl .= '&amp;session_id=';
			
			// if possible, encrypt the session id
//if (empty(self::$componentRefs['security'])) {
if (empty($Dispatcher_componentRefs['security'])) {
				//$newUrl .= self::$componentRefs['security']->sessionID;
$newUrl .= $Dispatcher_componentRefs['security']->sessionID;
			} else {
				//$newUrl .= self::$componentRefs['security']->encode(self::$componentRefs['session']->sessionID);
$newUrl .= $Dispatcher_componentRefs['security']->encode($Dispatcher_componentRefs['session']->sessionID);
			}
		}

		return $newUrl;
	}
	
	// url() returns a URL absolute to /, absoluteUrl() includes the scheme and host name (like 'http://www.example.com/c/a/p')
	//static
	function absoluteUrl($cap) {
		return 'http://'.env('HTTP_HOST').Dispatcher::url($cap);
	}
	
	// Parses the given /c/a/p triad, finds loads and executes the appropriate controller, and returns the result of rendering the view
	// This has an optional $data param, this is an assoc array that is merged into the controller's data. This lets an action dispatch and
	// return another action like: $this->actionResult = Dispatcher::dispatch('/c/a/p', array('key'=>'value'));
	//static
	function dispatch($cap = null, $data = null) {
		// The first time this is called (by the bootstrapping file like /slab.php), $inDispatch is false.
		//if (self::$inDispatch) {
		global $Dispatcher_inDispatch;
		if ($Dispatcher_inDispatch) {
			// This is being called within an action (while dispatching), just run the dispatch without
			// shutting down the components
			$controller = Dispatcher::__innerDispatch($cap, $data);
			return $controller->actionResult;
		}
		
		//self::$inDispatch = true;
$Dispatcher_inDispatch = true;
		$controller = Dispatcher::__innerDispatch($cap);
		//self::$inDispatch = false;
		$Dispatcher_inDispatch = false;
		
		// shutdown components
		//foreach (array_values(self::$componentRefs) as $c) {
global $Dispatcher_componentRefs;
foreach (array_values($Dispatcher_componentRefs) as $c) {
			$c->shutdown();
		}
		
		return $controller->actionResult;
	}

	//static
	function __innerDispatch($cap = null, $data = null) {
		$controllerName = '';
		$actionName = '';
		$params = array();
		
		//self::$baseUrl = dirname(env('PHP_SELF'));
		global $Dispatcher_baseUrl;
		$Dispatcher_baseUrl = dirname(env('PHP_SELF'));

		// If the cap triad is empty, fall back to the REQUEST url, then to the default route
		if (empty($cap)) {
			$cap = isset($_REQUEST['slab_url']) ? $_REQUEST['slab_url'] : Config::get('app.default_route');
		}
		if (empty($cap)) {
			e('No valid route was found. Make sure that the app.default_route setting is properly configured.');
			die();
		}

		// get rid of the preceding '/'
		if (strpos($cap, '/') === 0) {
			$cap = substr($cap, 1);
		}

		// Extract the controller name, action name, and parameters from the cap
		$cap = explode('/', $cap);
		if (count($cap) >= 1) {
			$controllerName = lowercase($cap[0]);
		}
		if (count($cap) >= 2) {
			$actionName = lowercase($cap[1]);
		}
		if (empty($actionName)) {
			$actionName = 'index';
		}
		if (count($cap) >= 3) {
			$params = array_slice($cap, 2);
		}

		// Load and create an instance of the controller
		$controller =& Dispatcher::loadController($controllerName, $actionName, $params, $data);
		if (!is_object($controller)) {
			e('Error loading controller');
			die();
		}

		// if the Cookie component is loaded, call initCookie() (as the cookie must be initialised before 
		// Session::beforeAction() is called below)
		if (isset($controller->Cookie)) {
			$controller->Cookie->initCookie();
		}
				
		// call the components beforeAction
		//foreach (array_values($controller->componentRefs) as $c) {
		//	$c->beforeAction();
		//}
		foreach (array_keys($controller->componentRefs) as $k) {
			$controller->componentRefs[$k]->beforeAction();
		}
		// Execute the action. The action should result in $controller->actionResult being set, usually via 
		// a call to Controller::render().
		// Nothing should have been output yet, as $actionResult is an instance of a subclass of ActionResult
		// which has a render() function (this is executed by the entry point file, eg slab.php)
		// Any uncaught exceptions are translated into an AJAX failure, which just looks like a 500 response
		// including the exception text
		$controller->beforeAction();
		try {
			$controller->dispatchMethod($actionName, $params);
		} catch (Exception $ex) {
			$controller->ajaxError($ex->getMessage());
		}
		if (empty($controller->actionResult)) {
			// if the controller's actionResult isn't set, this means that the action didn't execute a view method,
			// so just default to $controller->view()
			$controller->view();
		}
		$controller->afterAction();
		
		// call the components afterAction
		foreach (array_values($controller->componentRefs) as $c) {
			$c->afterAction();
		}
		
		return $controller;
	}
		
	// Find, create and set up an instance of the specified controller
	// The controllerName is underscored, ie 'shopping_cart' will load the ShoppingCartController class from /controllers/shopping_cart_controller.php
	// TODO add support for controller domains, eg 'admin.log_in' will load the LogInController class from /controllers/admin/log_in_controller.php
	//static
	function &loadController($controllerName, $actionName, $params, $data = null) {
		$className = Inflector::camelize($controllerName).'Controller';

		// try to load from the app
		$filename = SLAB_APP.'/controllers/'.$controllerName.'_controller.php';
		if (!file_exists($filename)) {
			e("<p>The <em>$className</em> controller could not be found at <code>$filename</code><br/>");
			die();
		}
		
		// TODO: fall back on plugins
		
		require_once($filename);
		
		if (!class_exists($className)) {
			e('The <em>'.$className.'</em> controller could not be loaded<br/>');
			e("Make sure the <em>$className</em> controller is defined at <code>$filename</code><br/>");
			die();
		}
		
		$controller = new $className();

		// Check the validity of the action
		Dispatcher::__checkAction($controller, $actionName);
		
		$controller->actionName = $actionName;
		$controller->params = $params;
		
		// copy $_REQUEST into $controller->data
		$controller->data = array();
		if (!empty($data)) {
			$controller->data = array_merge($controller->data, $data);
		}
		$controller->data = array_merge($controller->data, $_REQUEST);
		if (isset($controller->data['data'])) {
			$controller->data = array_merge($controller->data, $controller->data['data']);
			unset($controller->data['data']);
		}
		// merge $_FILES into $controller->data (uploaded files)
		if (isset($_FILES['data'])) {
			$_FILES = array_merge($_FILES, $_FILES['data']);
			unset($_FILES['data']);
		}
		// file inputs can _either_ be named like 'field_name' or 'data[Model][field_name]', but the two formats _cannot be mixed_ in one request
		// When data[model][field_name] foramt is used, can't just array_merge(), have to remap $_FILES (see http://au2.php.net/manual/en/features.file-upload.multiple.php#53240)
		if (!empty($_FILES['tmp_name'])) {
			// if $_FILES['tmp_name'] exists this is the data[Model][field_name] format
			foreach ($_FILES as $el=>$models) {
				foreach ($models as $modelName=>$elArr) {
					foreach ($elArr as $fieldName=>$val) {
						$controller->data[$modelName][$fieldName][$el] = $val;
					}
				}
			}
		} else {
			// this is the field_name format, just merge into $controller->data
			$controller->data = array_merge($controller->data, $_FILES);
		}

		// load and configure components
		$controller->components = array_merge($controller->components, Config::get('app.default_components'));
		foreach ($controller->components as $componentName) {
			$component = &Dispatcher::loadComponent($componentName);
			$component->controller =& $controller;
			$controller->componentRefs[$componentName] =& $component;
			// add as both $controller->ComponentName and $controller->componentName
			$componentName = Inflector::camelize($componentName);
			$controller->$componentName =& $component;
			$componentName = Inflector::camelback(Inflector::underscore($componentName));
			$controller->$componentName =& $component;
		}
		
		// load and configure models
		foreach ($controller->models as $modelName => $tableName) {
			$model =& Dispatcher::loadModel($modelName, $tableName);
			$controller->modelRefs[$modelName] =& $model;
			// add as both $controller->ModelName (not as $controller->modelName, I used to and it causes problems when having things like $this->user set in the AppController)
			$controller->$modelName =& $model;
			//$modelName = Inflector::camelback(Inflector::underscore($modelName));
			//$controller->$modelName =& $model;
		}
		

		// set up view
		$controller->view->viewName = $controllerName.'/'.$actionName;
		// load helpers into view
		$controller->helpers = array_merge($controller->helpers, Config::get('app.default_helpers'));
		foreach ($controller->helpers as $helperName) {
			$helper =& Dispatcher::loadHelper($helperName);
			// add (as HelperName and helperName) to both the view's helperRefs array and the controller
			$helperName = Inflector::camelize($helperName);
			$controller->$helperName =& $helper;
			$controller->view->helperRefs[$helperName] =& $helper;
			$helperName = Inflector::camelback(Inflector::underscore($helperName));
			$controller->helperName =& $helper;
			$controller->view->helperRefs[$helperName] =& $helper;
		}

		return $controller;
	}

	//static
	function __checkAction(&$controller, $actionName) {
		if ($actionName == 'beforeAction' || $actionName == 'afterAction' || $actionName == 'url' ||
			$actionName == 'render' || $actionName == 'renderPartial' || $actionName == 'redirect' ||
			$actionName == 'set' || $actionName == 'text' || $actionName == 'json' ||
			$actionName == 'renderFileInline' || $actionName == 'renderFileAttachment' || $actionName == 'renderFile' ||
			$actionName == 'ajaxSuccess' || $actionName == 'ajaxFailure'
			) {
			e('Reserved action names are not permitted');
			die();
		}
	
		// if the action starts with an underscore, a private/protected method is being attempted, which is not allowed
		if (strpos($actionName, '_', 0) === 0) {
			e('Private/protected actions are not permitted - TODO better error handling ;-)');
			die();
		}
		
		// make sure the method exists
		$methods = array_flip($controller->methods);
		if (!isset($methods[$actionName])) {
			$controllerClass = get_class($controller);
			e("The <em>{$actionName}</em> action could not be found in the <em>{$controllerClass}</em> controller");
			die();
		}
	}
	
	//static
	function &loadModel($modelName, $tableName) {
		// the file name is the underscored model name
		// the class name is the model name
		
		$m = null;
		
		// try to find the model in the app
		$modelFilename = SLAB_APP.'/models/'.Inflector::underscore($modelName).'.php';
		if (file_exists($modelFilename)) {
			require_once($modelFilename);
			if (!class_exists($modelName)) {
				e("<p>The <em>$modelName</em> model could not be found at <code>$modelFilename</code></p>");
				die();
			}
			$m = new $modelName();
		} else {
			// fall back on a vanilla Model instance
			$m = new Model();
			$m->modelName = $modelName;
			$m->tableName = $tableName;
		}
		
		// if the db component is loaded, add it to the model
global $Dispatcher_componentRefs;
		//if (isset(self::$componentRefs['db'])) {
if (isset($Dispatcher_componentRefs['db'])) {
			//$m->db =& self::$componentRefs['db'];
$m->db =& $Dispatcher_componentRefs['db'];

			// if configured, load model schemas
			if (Config::get('app.load_model_schemas')) {
				$m->loadSchema();
			}
		}
		


		return $m;
	}

	//static
	function &loadComponent($componentName) {
		//if (!empty(self::$componentRefs[$componentName])) {
		global $Dispatcher_componentRefs;
		if (!empty($Dispatcher_componentRefs[$componentName])) {
			//return self::$componentRefs[$componentName];
			return $Dispatcher_componentRefs[$componentName];
		}
	
		$componentClass = Inflector::camelize($componentName).'Component';
		
		// first try to find the component in the app
		$componentFilename = SLAB_APP.'/components/'.$componentName.'.php';
		if (!file_exists($componentFilename)) {
			// fall back to the core components
			$componentFilename = SLAB_LIB.'/components/'.$componentName.'.php';
			if (!file_exists($componentFilename)) {
				$componentFilename = SLAB_APP.'/components/'.$componentName.'.php';
				e("<p>The <em>$componentClass</em> component could not be found at <code>$componentFilename</code></p>");
				die();
			}
		}
		
		require_once($componentFilename);
		
		if (!class_exists($componentClass)) {
			e("<p>The <em>$componentClass</em> component does not exist in <code>$componentFilename</code></p>");
			die();
		}
		
		$component = new $componentClass;
		
		// init the component here, so that it only gets called once
		$component->init();
		
//		self::$componentRefs[$componentName] =& $component;
$Dispatcher_componentRefs[$componentName] =& $component;
		
		return $component;
	}
	
	//static
	function &loadHelper($helperName) {
		//if (!empty(self::$helperRefs[$helperName])) {
		//	return self::$helperRefs[$helperName];
global $Dispatcher_helperRefs;
if (!empty($Dispatcher_helperRefs[$helperName])) {
return $Dispatcher_helperRefs[$helperName];
		}
		
		$helperClass = Inflector::camelize($helperName).'Helper';
		
		// first try to find the component in the app
		$helperFilename = SLAB_APP.'/helpers/'.$helperName.'.php';
		if (!file_exists($helperFilename)) {
			// fall back to the core helpers
			$helperFilename = SLAB_LIB.'/helpers/'.$helperName.'.php';
			if (!file_exists($helperFilename)) {
				$helperFilename = SLAB_APP.'/helpers/'.$helperName.'.php';
				e("<p>The <em>$helperClass</em> helper could not be found at <code>$helperFilename</code></p>");
				die();
			}
		}
		
		require_once($helperFilename);
		
		if (!class_exists($helperClass)) {
			e("<p>The <em>$helperClass</em> helper does not exist in <code>$helperFilename</code></p>");
			die();
		}
		
		$helper = new $helperClass;
		
		//self::$helperRefs[$helperName] = $helper;
$Dispatcher_helperRefs[$helperName] = $helper;
		
		return $helper;
	}
	
		
	static function loadThirdParty($filename) {
		$thirdPartyFilename = SLAB_APP.'/third_party/'.$filename.'.php';
		if (!file_exists($thirdPartyFilename)) {
			// fall backk to core third party files
			$thirdPartyFilename = SLAP_LIB.'/third_party/'.$filename.'.php';
			if (!file_exists($thirdPartyFilename)) {
				throw new Exception('The third party extension could not be found: '.$filename);
			}
		}
		
		require_once($thirdPartyFilename);
	}
};

?>