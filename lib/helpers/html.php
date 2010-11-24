<?php
/* HtmlHelper
** Some parts are from CodeIgniter
** BJS20091004
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class HtmlHelper extends Helper {
	var $name = 'HtmlHelper';
	
	function select($name, $id, $options, $current = null) {
		$s = '';
		
		foreach ($options as $k => $v) {
			$s .= "<option value='{$k}'";
			if (isset($current) && $k == $current) {
				$s .= ' selected="selected" ';
			}
			$s .= ">{$v}</option>";
		}
		
		return $this->__selectWrapper($name, $id, $s);;
	}
	
	function selectIntFromRange($name, $id, $from, $to, $current) {
		$s = '';
		
		for ($i = $from; $i < $to + 1; $i ++) {
			$s .= "<option";
			if ($i == $current) $s .= ' selected="selected" ';
			$s .= ">{$i}</option>";
		}
		
		return $this->__selectWrapper($name, $id, $s);
	}
	
	function __selectWrapper($name, $id, $options) {
		return "<select name=\"{$name}\" id=\"{$id}\">{$options}</select>";
	}


	// Wrapper for Dispatcher::url()
	function url($u) {
		return Dispatcher::url($u);
	}	
	
	// This is adapted from CodeIgniter
	function headerStatus($code, $reason = null) {
		// check the code
		if ($code == '' || !is_numeric($code)) {
			e('In HtmlHelper::headerStatus(), status codes must be numeric');
			die();
		}
		
		// get the reason
		if (empty($reason) && isset($this->headerStatusCodes[$code])) {
			$reason = $this->headerStatusCodes[$code];
		}
		if ($reason == '') {
			e('In HtmlHeader::headerStatus(), no status text is available for code '.$code);
			die();
		}
		
		// CGI clients don't receive the HTTP/1.X header
		if (substr(php_sapi_name(), 0, 3) == 'cgi') {
			header('Status: '.$code.' '.$reason);
			return;
		}
		
		$serverProtocol = 'HTTP/1.1';
		if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') {
			$serverProtocol = 'HTTP/1.0';
		}
		
		header("$serverProtocol $code $reason", true, $code);
	}

	// Write the headers to trigger no-cache for IE (used by some AJAX-y View subclasses)
	function headerNoCache() {
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: 0');
	}

	
	// Copied from CodeIgniter
	var $headerStatusCodes = array(
		'200'	=> 'OK',
		'201'	=> 'Created',
		'202'	=> 'Accepted',
		'203'	=> 'Non-Authoritative Information',
		'204'	=> 'No Content',
		'205'	=> 'Reset Content',
		'206'	=> 'Partial Content',		
		'300'	=> 'Multiple Choices',
		'301'	=> 'Moved Permanently',
		'302'	=> 'Found',
		'304'	=> 'Not Modified',
		'305'	=> 'Use Proxy',
		'307'	=> 'Temporary Redirect',		
		'400'	=> 'Bad Request',
		'401'	=> 'Unauthorized',
		'403'	=> 'Forbidden',
		'404'	=> 'Not Found',
		'405'	=> 'Method Not Allowed',
		'406'	=> 'Not Acceptable',
		'407'	=> 'Proxy Authentication Required',
		'408'	=> 'Request Timeout',
		'409'	=> 'Conflict',
		'410'	=> 'Gone',
		'411'	=> 'Length Required',
		'412'	=> 'Precondition Failed',
		'413'	=> 'Request Entity Too Large',
		'414'	=> 'Request-URI Too Long',
		'415'	=> 'Unsupported Media Type',
		'416'	=> 'Requested Range Not Satisfiable',
		'417'	=> 'Expectation Failed',
		'500'	=> 'Internal Server Error',
		'501'	=> 'Not Implemented',
		'502'	=> 'Bad Gateway',
		'503'	=> 'Service Unavailable',
		'504'	=> 'Gateway Timeout',
		'505'	=> 'HTTP Version Not Supported'
	);

};

?>