<?php
/* /lib/components/session.php
** SessionComponent, allows persistent sessions
** Sessions can be persisted to disk in a temp file, to a database table, or to the cookie.
** At the moment only cookie and file based sessions are supported.
** Disk and database sessions will have a session id saved to the cookie (recommended), or possibly to the url
** BJS20090405
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
** BJS20090830 added check()
*/

class SessionComponent extends Component {
	// Config:
	var $sessionCookieName = null;	// the name used for the session cookie
	var $sessionTimeout = 0;	// the time that a session will last for from the last activity (in seconds). Note that the cookie expire time may be less than this.
	var $sessionType = null;	// 'file' | 'database' | 'cookie'
	var $sessionIDType = null;	// 'cookie' | 'url', if sessionType is 'cookie' this is ignored (everything gets saved to the cookie anyway)
	var $sessionDatabaseTable = null;	// the name of the datbase table for database-persisted sessions
	var $sessionFilenamePrefix = null;	// file-based session will be saved to /app/temp/$sessionFilenamePrefix_$sessionID.txt ($sessionID is base64 encoded first)
	
	var $data = array();
	var $inSession = false;
	var $sessionID = null;
	
	
	function init() {
		$this->sessionCookieName = Config::get('session.cookie_name');
		$this->sessionTimeout = Config::get('session.timeout');
		$this->sessionType = Config::get('session.type');
		$this->sessionIDType = Config::get('session.id_type');
		$this->sessionDatabaseTable = Config::get('session.database_table');
		$this->sessionFilenamePrefix = Config::get('session.filename_prefix');		
	}
		
	function beforeAction() {
		// check that the required controllers are loaded
		if ($this->sessionType == 'cookie' || $this->sessionIDType == 'cookie') {
			if (empty($this->controller->Cookie)) {
				e('Cookie-based sessions require the Cookie component to be loaded');
				die();
			}
		}
		if ($this->sessionType == 'database') {
			if (empty($this->controller->Db)) {
				e('Database-persisted sessions require the Db component to be loaded');
				die();
			}
		} else if ($this->sessionType == 'file') {
			if (empty($this->controller->File)) {
				e('File-persisted sessions require the File component to be loaded');
				die();
			}
		}
		
		$this->clearExpiredSessions();
		
		// Load the session if available
		$this->inSession = false;
		
		// get the session id
		$this->sessionID = null;
		if ($this->sessionType == 'cookie' || $this->sessionIDType == 'cookie') {
			// Load from the cookie:
			if (!(empty($this->controller->Cookie->data[$this->sessionCookieName]))) {
				$this->sessionID = $this->controller->Cookie->data[$this->sessionCookieName]['session_id'];
			}
		} else if ($this->sessionIDType == 'url') {
			$this->sessionID = $this->controller->data['session_id'];
			// the session ID is encrypted, so decrypt it before use
			$this->sessionID = Security::decrypt($this->sessionID);
		}
		
		if (empty($this->sessionID)) {
			return;
		}
		
		// get the session
		if ($this->sessionType == 'file') {
			$sessionFilename = $this->__getSessionFilename();
			if ($this->controller->File->exists($sessionFilename)) {
				$this->data = $this->controller->File->readObject($sessionFilename);
				if (empty($this->data)) {
					$this->data = array();
				}
				$this->inSession = true;
			}
		} else if ($this->sessionType == 'database') {
		} else if ($this->sessionType == 'cookie') {
			if (!empty($this->controller->Cookie->data[$this->sessionCookieName]) && !empty($this->controller->Cookie->data[$this->sessionCookieName]['session_data'])) { 
				$this->data = $this->controller->Cookie->data[$this->sessionCookieName]['session_data'];
				if (empty($this->data)) {
					$this->data = array();
				}
				$this->inSession = true;
			}
		}
	}
	
	function afterAction() {
		$this->save();
	}
	
	
	function get($name) {
		return $this->data[$name];
	}
	function read($name) {
		return $this->data[$name];
	}
	
	
	function set($name, $value) {
		$this->data[$name] = $value;
	}
	function write($name, $value) {
		$this->data[$name] = $value;
	}
	
	function remove($name) {
		unset($this->data[$name]);
		if ($this->sessionType == 'cookie') {
			$this->controller->Cookie->remove('', "[{$this->sessionCookieName}][session_data][{$name}]");
		}
	}
	function delete($name) {
		$this->remove($name);
	}
	
	function removeAll() {
		foreach (array_keys($this->data) as $n) {
			$this->remove($n);
		}
		$this->data = array();
	}
	
	function check($name) {
		return !empty($this->data[$name]);
	}
	
	function start() {
		if ($this->inSession) {
			$this->end();
		}
		
		$this->sessionID = uuidSecure();
		$this->inSession = true;
		$this->save();
	}
	
	function end() {
		if (!$this->inSession) {
			return;
		}
		
		if ($this->sessionIDType == 'cookie' || $this->sessionType == 'cookie') {
			// delete the session id (and data if it's there) from the cookie
			$this->controller->Cookie->remove($this->sessionCookieName);
		}
		if ($this->sessionType == 'database') {
			// delete the session data from the database
		} else if ($this->sessionType == 'file') {
			// delete the temp file containing the session data
			$this->controller->File->remove($this->__getSessionFilename());
		}
		
		$this->data = array();
		$this->sessionID = null;
		$this->inSession = false;
	}

	// Saves the session. This is called in SessionComponent::afterFilter(), so doesn't need to explicitly called.
	function save() {	
		if (!$this->inSession) {
			return;
		}
		
		if ($this->sessionType == 'cookie' || $this->sessionIDType == 'cookie') {
			// save the session id to the cookie
			$cookieData = array('session_id' => $this->sessionID);
			$this->controller->Cookie->data[$this->controller->Cookie->cookieName]['session_id'] = $this->sessionID;
			// also save the session data itself to the cookie if the session type is cookie
			if ($this->sessionType == 'cookie') {
				$cookieData['session_data'] = $this->data;
				$this->controller->Cookie->data[$this->controller->Cookie->cookieName]['session_data'] = $this->data;
			}
			$this->controller->Cookie->set($this->sessionCookieName, $cookieData);
		}
		if ($this->sessionType == 'database') {
			// save the session data to the database
		} else if ($this->sessionType == 'file') {
			// save the session data to a temp file
			$this->controller->File->writeObject($this->__getSessionFilename(), $this->data);
		}
		
		// If the session id type is 'url', it gets rendered in the view whenever HtmlHelper::url() is called (actually in Dispatcher::url())
	}
	
	
	// Private method for getting the filename used to store file-based session data
	function __getSessionFilename() {
		return SLAB_APP.'/temp/'.$this->sessionFilenamePrefix.base64_encode($this->sessionID).'.txt';
	}
	
	// Clears all sessions that have expired. This is called in beforeFilter(), before any current session is attempted to be loaded.
	function clearExpiredSessions() {
		if ($this->sessionType == 'cookie') {
		} else if ($this->sessionType == 'database') {
		} else if ($this->sessionType == 'file') {
			foreach ($this->controller->File->dir(SLAB_APP.'/temp/', true) as $fn) {
				if (strpos(basename($fn), $this->sessionFilenamePrefix) !== 0) {
					continue;
				}
				
				// get the last modified time
				$fileModTime = filemtime($fn);
				if ($fileModTime !== false) {
					// if the session file was last modified more than (now minus the session timeout), delete the session file
					if ($fileModTime < time() - $this->sessionTimeout) {
						$this->controller->File->delete($fn);
					}
				}
			}
		}
	}
}
?>