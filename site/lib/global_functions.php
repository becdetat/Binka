<?php
/* global_functions.php
** Defines global support functions
** Some of these are copied from CakePHP's basics.php
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

// combined h() and e(), echos the html encoded $s
function eh($s) {
	e(h($s));
}

// Alias for count
function length($a) {
	return count($a);
}

// Aliases for htmlspecialchars
function h($s) {
	return htmlspecialchars($s);
}
function html($s) {
	return htmlspecialchars($s);
}

// Returns the microtime, this is used for execution time checking
function getMicrotime() {
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

// Alias for echo()
function e($s) {
	echo($s);
}

// Wrapper for print_r() which also wraps the output in <pre> tags
function pr($v) {
	e('<pre>');
	print_r($v);
	e('</pre>');
}

// Alias for strtolower/upper
function lowercase($s) { return strtolower($s); }
function uppercase($s) { return strtoupper($s); }
function toLower($s) { return strtolower($s); }
function toUpper($s) { return strtoupper($s); }
function uc($s) { return strtoupper($s); }
function lc($s) { return strtolower($s); }
function up($s) { return strtoupper($s); }
function low($s) { return strtolower($s); }

// Returns if a given string $source contains the specified search string $search
// If $search is an array, returns true if any of the items in $search is contained in $source
function strContains($source, $search) {
	if (is_array($search)) {
		foreach ($search as $s) {
			if (strContains($source, $s)) {
				return true;
			}
		}
		return false;
	}

	return strpos($source, $search) !== FALSE;
}
// returns if a given string $source starts with the specified search string $search
// If $search is an array, returns true if $source starts with any of the items in $search
function strStartsWith($source, $search) {
	if (is_array($search)) {
		foreach ($search as $s) {
			if (strStartsWith($source, $s)) {
				return true;
			}
		}
		return false;
	}

	return strpos($source, $search) === 0;
}

// Gets an environment variable, from CakePHP:
/**
 * Gets an environment variable from available sources, and provides emulation
 * for unsupported or inconsistent environment variables (i.e. DOCUMENT_ROOT on
 * IIS, or SCRIPT_NAME in CGI mode).  Also exposes some additional custom
 * environment information.
 *
 * @param  string $key Environment variable name.
 * @return string Environment variable setting.
 * @link http://book.cakephp.org/view/701/env
 */
function env($key) {
	if ($key == 'HTTPS') {
		if (isset($_SERVER) && !empty($_SERVER)) {
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
		}
		return (strpos(env('SCRIPT_URI'), 'https://') === 0);
	}

	if ($key == 'SCRIPT_NAME') {
		if (env('CGI_MODE') && isset($_ENV['SCRIPT_URL'])) {
			$key = 'SCRIPT_URL';
		}
	}

	$val = null;
	if (isset($_SERVER[$key])) {
		$val = $_SERVER[$key];
	} elseif (isset($_ENV[$key])) {
		$val = $_ENV[$key];
	} elseif (getenv($key) !== false) {
		$val = getenv($key);
	}

	if ($key === 'REMOTE_ADDR' && $val === env('SERVER_ADDR')) {
		$addr = env('HTTP_PC_REMOTE_ADDR');
		if ($addr !== null) {
			$val = $addr;
		}
	}

	if ($val !== null) {
		return $val;
	}

	switch ($key) {
		case 'SCRIPT_FILENAME':
			if (defined('SERVER_IIS') && SERVER_IIS === true) {
				return str_replace('\\\\', '\\', env('PATH_TRANSLATED'));
			}
		break;
		case 'DOCUMENT_ROOT':
			$name = env('SCRIPT_NAME');
			$filename = env('SCRIPT_FILENAME');
			$offset = 0;
			if (!strpos($name, '.php')) {
				$offset = 4;
			}
			return substr($filename, 0, strlen($filename) - (strlen($name) + $offset));
		break;
		case 'PHP_SELF':
			return str_replace(env('DOCUMENT_ROOT'), '', env('SCRIPT_FILENAME'));
		break;
		case 'CGI_MODE':
			return (PHP_SAPI === 'cgi');
		break;
		case 'HTTP_BASE':
			$host = env('HTTP_HOST');
			if (substr_count($host, '.') !== 1) {
				return preg_replace('/^([^.])*/i', null, env('HTTP_HOST'));
			}
		return '.' . $host;
		break;
	}
	return null;
}


// From http://au.php.net/manual/en/function.uniqid.php#88023 (sean at seancolombo dot com)
/**
* @brief Generates a Universally Unique IDentifier, version 4.
*
* This function generates a truly random UUID. The built in CakePHP String::uuid() function
* is not cryptographically secure. You should uses this function instead.
*
* @see http://tools.ietf.org/html/rfc4122#section-4.4
* @see http://en.wikipedia.org/wiki/UUID
* @return string A UUID, made up of 32 hex digits and 4 hyphens.
*/
function uuidSecure() {
	$pr_bits = null;
	$fp = @fopen('/dev/urandom','rb');
	if ($fp !== false) {
		$pr_bits .= @fread($fp, 16);
		@fclose($fp);
	} else {
		// If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
		$pr_bits = "";
		for($cnt=0; $cnt < 16; $cnt++){
			$pr_bits .= chr(mt_rand(0, 255));
		}
	}

	$time_low = bin2hex(substr($pr_bits,0, 4));
	$time_mid = bin2hex(substr($pr_bits,4, 2));
	$time_hi_and_version = bin2hex(substr($pr_bits,6, 2));
	$clock_seq_hi_and_reserved = bin2hex(substr($pr_bits,8, 2));
	$node = bin2hex(substr($pr_bits,10, 6));

	/**
	* Set the four most significant bits (bits 12 through 15) of the
	* time_hi_and_version field to the 4-bit version number from
	* Section 4.1.3.
	* @see http://tools.ietf.org/html/rfc4122#section-4.1.3
	*/
	$time_hi_and_version = hexdec($time_hi_and_version);
	$time_hi_and_version = $time_hi_and_version >> 4;
	$time_hi_and_version = $time_hi_and_version | 0x4000;

	/**
	* Set the two most significant bits (bits 6 and 7) of the
	* clock_seq_hi_and_reserved to zero and one, respectively.
	*/
	$clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
	$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
	$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

	return sprintf('%08s-%04s-%04x-%04x-%012s',
	$time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
}


function getMimeType($ext) {
	global $mimeTypes;
	
	$mimeType = 'application/octet-stream';
	if (isset($mimeTypes[$ext])) {
		$mimeType = $mimeTypes[$ext];
		if (is_array($mimeType)) {
			$mimeType = $mimeType[0];
		}
	}
	return $mimeType;
}

function getMimeTypeFromFilename($filename) {
	$mimeType = 'application/octet-stream';
	if (strpos($filename, '.') !== false) {
		$parts = explode('.', $filename);
		$ext = end($parts);
		$mimeType = getMimeType($ext);
	}
	return $mimeType;
}


// This is copied from CodeIgniter. It is used to determine the mime type of a given filename.
$mimeTypes = array(	
	'hqx'	=>	'application/mac-binhex40',
	'cpt'	=>	'application/mac-compactpro',
	'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
	'bin'	=>	'application/macbinary',
	'dms'	=>	'application/octet-stream',
	'lha'	=>	'application/octet-stream',
	'lzh'	=>	'application/octet-stream',
	'exe'	=>	'application/octet-stream',
	'class'	=>	'application/octet-stream',
	'psd'	=>	'application/x-photoshop',
	'so'	=>	'application/octet-stream',
	'sea'	=>	'application/octet-stream',
	'dll'	=>	'application/octet-stream',
	'oda'	=>	'application/oda',
	'pdf'	=>	array('application/pdf', 'application/x-download'),
	'ai'	=>	'application/postscript',
	'eps'	=>	'application/postscript',
	'ps'	=>	'application/postscript',
	'smi'	=>	'application/smil',
	'smil'	=>	'application/smil',
	'mif'	=>	'application/vnd.mif',
	'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
	'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
	'wbxml'	=>	'application/wbxml',
	'wmlc'	=>	'application/wmlc',
	'dcr'	=>	'application/x-director',
	'dir'	=>	'application/x-director',
	'dxr'	=>	'application/x-director',
	'dvi'	=>	'application/x-dvi',
	'gtar'	=>	'application/x-gtar',
	'gz'	=>	'application/x-gzip',
	'php'	=>	'application/x-httpd-php',
	'php4'	=>	'application/x-httpd-php',
	'php3'	=>	'application/x-httpd-php',
	'phtml'	=>	'application/x-httpd-php',
	'phps'	=>	'application/x-httpd-php-source',
	'js'	=>	'application/x-javascript',
	'swf'	=>	'application/x-shockwave-flash',
	'sit'	=>	'application/x-stuffit',
	'tar'	=>	'application/x-tar',
	'tgz'	=>	'application/x-tar',
	'xhtml'	=>	'application/xhtml+xml',
	'xht'	=>	'application/xhtml+xml',
	'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
	'mid'	=>	'audio/midi',
	'midi'	=>	'audio/midi',
	'mpga'	=>	'audio/mpeg',
	'mp2'	=>	'audio/mpeg',
	'mp3'	=>	array('audio/mpeg', 'audio/mpg'),
	'aif'	=>	'audio/x-aiff',
	'aiff'	=>	'audio/x-aiff',
	'aifc'	=>	'audio/x-aiff',
	'ram'	=>	'audio/x-pn-realaudio',
	'rm'	=>	'audio/x-pn-realaudio',
	'rpm'	=>	'audio/x-pn-realaudio-plugin',
	'ra'	=>	'audio/x-realaudio',
	'rv'	=>	'video/vnd.rn-realvideo',
	'wav'	=>	'audio/x-wav',
	'bmp'	=>	'image/bmp',
	'gif'	=>	'image/gif',
	'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
	'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
	'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
	'png'	=>	array('image/png',  'image/x-png'),
	'tiff'	=>	'image/tiff',
	'tif'	=>	'image/tiff',
	'css'	=>	'text/css',
	'html'	=>	'text/html',
	'htm'	=>	'text/html',
	'shtml'	=>	'text/html',
	'txt'	=>	'text/plain',
	'text'	=>	'text/plain',
	'log'	=>	array('text/plain', 'text/x-log'),
	'rtx'	=>	'text/richtext',
	'rtf'	=>	'text/rtf',
	'xml'	=>	'text/xml',
	'xsl'	=>	'text/xml',
	'mpeg'	=>	'video/mpeg',
	'mpg'	=>	'video/mpeg',
	'mpe'	=>	'video/mpeg',
	'qt'	=>	'video/quicktime',
	'mov'	=>	'video/quicktime',
	'avi'	=>	'video/x-msvideo',
	'movie'	=>	'video/x-sgi-movie',
	'doc'	=>	'application/msword',
	'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
	'word'	=>	array('application/msword', 'application/octet-stream'),
	'xl'	=>	'application/excel',
	'eml'	=>	'message/rfc822'
);


// json_encode doesn't exist in < PHP 5.2, so...
// Adapted from http://au.php.net/manual/en/function.json-encode.php#82904
// *************************************************************************** This could be dropped as I'm not targeting PHP < 5.2 now
if (!function_exists('json_encode')) {
  function json_encode($a = false) {
    if (is_null($a)) {
			return 'null';
		}
    if ($a === false) {
			return 'false';
		}
    if ($a === true) {
			return 'true';
		}
    if (is_scalar($a)) {
      if (is_float($a)) {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a)) {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      } else {
        return $a;
			}
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v) {
				$result[] = json_encode($v);
			}
      return '[' . join(',', $result) . ']';
    } else {
      foreach ($a as $k => $v) {
				$result[] = json_encode($k).':'.json_encode($v);
			}
      return '{' . join(',', $result) . '}';
    }
  }
}

// Adapted from http://www.php.net/manual/en/function.hexdec.php#99478
// Method to convert a hex color string to an array of (r,g,b),
// eg hex2rgb('#050505') == array(5,5,5),
// hex2rgb('#fff') == array(255,255,255)
function hex2rgb($hex) {
	$hex = preg_replace("/[^0-9A-Fa-f]/", '', $hex);
	if (strlen($hex) == 6) {
		$val = hexdec($hex);
		return array(
			0xff & ($val >> 0x10),
			0xff & ($val >> 0x8),
			0xff & $val
		);
	} else if (strlen($hex) == 3) {
		return array(
			hexdec(str_repeat(substr($hex, 0, 1), 2)),
			hexdec(str_repeat(substr($hex, 1, 1), 2)),
			hexdec(str_repeat(substr($hex, 2, 1), 2))
		);
	} else throw new Exception("Invalid hex color string");
}

?>