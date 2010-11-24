<?php
/* /lib/components/cookie.php
** CookieComponent, encapsulates cookie management
** BJS20090404
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class CookieComponent extends Component {
	// Fields
	var $cookieName = null;
	var $expire = 0;	// the time that the cookie will expire. eg time()+60*60*24*30 will expire in 30 days. Set to 0 for a session cookie.
	var $path = '';		// the path for the cookie is available at. Set to '/' to be available site-wide. By default it is accessible from the base url (usually '/')
	var $domain = '';
	var $secure = false;
	var $httponly = false;	// only in PHP 5.2 and above
	var $useEncryption = true;
	var $data = null;
	
	function init() {
		$this->cookieName = Config::get('cookie.default_cookie_name');
		$this->expire = Config::get('cookie.expire');
		$this->path = '/'; //Dispatcher::getBaseUrl();
		$this->domain = Config::get('cookie.domain');
		$this->secure = Config::get('cookie.secure');
		$this->httponly = Config::get('cookie.httponly');
		$this->useEncryption = Config::get('cookie.use_encryption');
	}
	
	function initCookie() {
		// copy and decrypt the current cookie
		// This function is called by the dispatcher between init()ing and beforeFilter()ing the controllers
		// as the Session component requires the cookie to be loaded before its beforeFilter() methods executes.
		// Putting this in beforeFilter() (which would otherwise be the case) will silently kill sessions if
		// Session::beforeFilter() were to execute before Cookie::beforeFilter()
		$this->data = isset($_COOKIE[$this->cookieName]) ? $_COOKIE[$this->cookieName] : array();
		if ($this->useEncryption) {
			$this->__decryptData();
		}
	}
		
	function beforeAction() {
	}
	
	function afterAction() {
	}
	
	// $value is either an array or a string
	// Note that this method does not affect the extracted cookie values in $this->data
	// The cookie that gets is named data[$this->cookieName][$name]
	// If $value is an array, setCookie() recursively processes $value into data[$this->cookieName][$name]
	// When the cookie is returned, the value will be accessible via $this->data[$name]
	function set($name, $value='', $prefix=null) {
		if (empty($prefix)) {
			$prefix = $this->cookieName;
		}

		if (!is_array($value)) {
			if ($this->useEncryption) {
				$value = Security::encode($value);
			}
		
			if (version_compare(PHP_VERSION, '5.2', '>=')) {
				// using at least PHP 5.2, include the $httponly argument
				setcookie(
					$prefix.'['.$name.']', 
					$value,
					$this->expire,
					$this->path,
					$this->domain,
					$this->secure,
					$this->httponly
					);
			} else {
				setcookie(
					$prefix.'['.$name.']', 
					$value,
					$this->expire,
					$this->path,
					$this->domain,
					$this->secure
					);
			}
			return;
		}
		
		// use recursion to set arrays to the cookie. The prefix is built up, so the cookie name becomes something like data[$this->cookieName][myArray][myKey]
		foreach ($value as $key=>$val) {
			$newPrefix = $prefix.'['.$name.']';
			$this->set($key, $val, $newPrefix);
		}
	}
	
	// Note that this method does not affect the extracted cookie values in $this->data
	// It recursively removes any cookie values stored under and including data[$this->cookieName][$name]
	// If you want to be tricky and delete a nested value, do something like this:
	//		deleteCookie('', "['myCookie'][''myNestedValue']");
	function remove($name, $idx=null) {
		if (empty($idx)) {
			$idx = "['".$name."']";
		}

		if (!eval('return isset($this->data'.$idx.');')) {
			return;
		}
		$a = eval('return $this->data'.$idx.';');
		if (!is_array($a)) {
			// remove the cookie by setting the expire time well in the past
			setcookie(
				$this->cookieName.str_replace("'",'',$idx), 
				'', 
				time()-100000, 
				$this->path, 
				$this->domain);
			return;
		}
		
		foreach ($a as $k=>$v) {
			$newIdx = $idx."['".$k."']";
			$this->remove($k, $newIdx);
		}
	}
	
	// Remove all of the cookies
	function removeAll() {
		foreach ($this->data as $k=>$v) {
			$this->remove($k);
		}
	}
	
	
	// Private method used by beforeFilter() to recursively decrypt $this->data
	function __decryptData($arr = null) {
		if (empty($arr)) {
			$arr =& $this->data;
		}
		if (empty($arr)) {
			return null;
		}
		
		foreach ($arr as $k=>$v) {
			if (!is_array($v)) {
				$arr[$k] = Security::decrypt($v);
			} else {
				$arr[$k] = $this->__decryptData($v);
			}
		}
		
		return $arr;
	}
}
?>