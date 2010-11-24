<?php
/* /lib/security.php
** Encapsulates crypt and other security related methods
** Parts are based on CodeIgniter's CI_Encrypt class
** Should be static but I've had to screw it up for PHP4
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:

Public functions:
init()			used in bootstrap.php to configure
md5($data)		wrapper for md5 for consistency
hash($data)		(preferred) one-way hash using sha1, mhash or md5 depending on availability
encrypt($data)	wrapper for encode()
encode($data)	encrypts using the security.encryption_key
decrypt($data)	wrapper for decode()
decode($data)	decrypts using the security.encryption_key
*/

$_Security__encryptionKey = null;	// the encryption key (md5 of the security.encryption_key configuration setting)
$_Security__mcryptExists = false;
$_Security__mcryptCipher = 'MCRYPT_RIJNDAEL_256';
$_Security__mcryptMode = 'MCRYPT_MODE_ECB';
$_Security__sha1Exists = false;
$_Security__mhashExists = false;


class Security extends Object {
	// Fields
	//static $__encryptionKey = null;	// the encryption key (md5 of the security.encryption_key configuration setting)
	//static $__mcryptExists = false;
	//static $__mcryptCipher = 'MCRYPT_RIJNDAEL_256';
	//static $__mcryptMode = 'MCRYPT_MODE_ECB';
	//static $__sha1Exists = false;
	//static $__mhashExists = false;

	
	/*static*/ function init() {
	global $_Security__encryptionKey;
		$_Security__encryptionKey = md5(Config::get('security.encryption_key'));
	global $_Security__mcryptExists;
		$_Security__mcryptExists = function_exists('mcrypt_encrypt');
	global $_Security__sha1Exists;
		$_Security__sha1Exists = function_exists('sha1');
	global $_Security__mhashExists;
		$_Security__mhashExists = function_exists('mhash');
	}	
	
		
	// Just a wrapper for md5 for consistency and backward compat
	/*static*/ function md5($data) {
		return md5($data);
	}

	/*static*/ function hash($data) {
	global $_Security__sha1Exists;
	global $_Security__mhashExists;
		if ($_Security__sha1Exists) {
			return sha1($data);
		} else if ($_Security__mhashExists) {
			return bin2hex(mhash(MHASH_SHA1, $data));
		}
		return md5($data);
	}
	
	// Encodes some data using the encryption key
	// This is based on CodeIgniter's CI_Encrypt::encode()
	/*static*/ function encrypt($data) { return Security::encode($data); }
	/*static*/ function encode($data) {
global $_Security__encryptionKey;
global $_Security__mcryptExists;
		// xor and encode the data and the encryption key
		$result = Security::__xorEncode($data, $_Security__encryptionKey);

		// if configured and supported, mcrypt encode the data as well (using Rijndael256)
		if (Config::get('security.use_mcrypt') && $_Security__mcryptExists) {
			$result = Security::__mcryptEncode($result, $_Security__encryptionKey);
		}
		
		return base64_encode($result);
	}
	
	// Decodes the result of encode() (given the same encryption key) into the original string
	/*static*/ function decrypt($data) { return Security::decode($data); }
	/*static*/ function decode($data) {
global $_Security__mcryptExists;
global $_Security__encryptionKey;
		// check for invalid chars in the data
		if (preg_match('/[^a-zA-Z0-9\/\+=]/', $data)) {
			return false;
		}

		$result = base64_decode($data);

		if (Config::get('security.use_mcrypt') && $_Security__mcryptExists) {
			$result = Security::__mcryptDecode($result, $_Security__encryptionKey);
			if ($result === false) {
				return false;
			}
		}

		$result = Security::__xorDecode($result, $_Security__encryptionKey);
	
		return $result;
	}




	// Encrypt some data using mcrypt
	// This is based on CodeIgniter's CI_Encrypt::mcrypt_encode()
	/*static*/ function __mcryptEncode($data, $key) {
global $_Security__mcryptCipher;
global $_Security__mcryptMode;
		//mcrypt_module_open($_Security__mcryptCipher, '', $_Security__mcryptMode, '');
		// TODO: mcrypt is giving errors re not warning, I've disabled it for now
		$initSize = mcrypt_get_iv_size($_Security__mcryptCipher, $_Security__mcryptMode);
		$initVect = mcrypt_create_iv($initSize, MCRYPT_RAND);
		return Security::__addCipherNoise($initVect.mcrypt_encrypt($_Security__mcryptCipher, $key, $data, $_Security__mcryptMode, $initVect), $key);
	}
	
	// Decrypt some data using mcrypt
	// This is based on CodeIgniter's CI_Encrypt::mcrypt_decode()
	/*static*/ function __mcryptDecode($data, $key) {
global $_Security__mcryptCipher;
global $_Security__mcryptMode;
		$data = Security::__removeCipherNoise($data, $key);
		$initSize = mcrypt_get_iv_size($_Security__mcryptCipher, $_Security__mcryptMode);

		if ($initSize > strlen($data)) {
			return false;
		}

		$initVect = substr($data, 0, $initSize);
		$data = substr($data, $initSize);
		return rtrim(mcrypt_decrypt($_Security__mcryptCipher, $key, $data, $_Security__mcryptMode, $initVect), "\0");
	}
	
	
	// Based on CodeIgniter's CI_Encrypt::_xor_encode()
	/*static*/ function __xorEncode($data, $key) {
		// Generate a random hash
		$rand = '';
		while (strlen($rand) < 32) {
			$rand .= mt_rand(0, mt_getrandmax());
		}
		$rand = Security::hash($rand); //self::hash($rand);

		// encode the data
		$result = '';
		for ($i = 0; $i < strlen($data); $i++) {			
			$result .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($data, $i, 1));
		}

		return Security::__xorMerge($result, $key); //self::__xorMerge($result, $key);
	}

	// Based on CodeIgniter's CI_Encrypt::_xor_decode()
	/*static*/ function __xorDecode($data, $key) {
		$data = Security::__xorMerge($data, $key);

		$result = '';
		for ($i = 0; $i < strlen($data); $i++) {
			$result .= (substr($data, $i++, 1) ^ substr($data, $i, 1));
		}

		return $result;
	}

	// Merge a string with a key using XOR
	// Based on CodeIgniter's CI_Encrypt::_xor_merge()
	/*static*/ function __xorMerge($data, $key) {
		$hash = Security::hash($key); //self::hash($key);
		$result = '';
		for ($i = 0; $i < strlen($data); $i++) {
			$result .= substr($data, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}

		return $result;
	}

	// Based on CodeIgniter's CI_Encrypt::_add_cypher_noise():
	// Adds permuted noise to the IV + encrypted data to protect against Man-in-the-middle attacks on CBC mode ciphers
	// http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	/*static*/ function __addCipherNoise($data, $key) {
		$keyhash = Security::hash($key); //self::hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
			if ($j >= $keylen) {
				$j = 0;
			}

			$str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
		}

		return $str;
	}

	// Based on CodeIgniter's CI_Encrypt::_add_cipher_noise():
	// Removes permuted noise from the IV + encrypted data, reversing _add_cipher_noise()
	/*static*/ function __removeCipherNoise($data, $key) {
		$keyhash = Security::$hash($key); //self::hash($key);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j) {
			if ($j >= $keylen) {
				$j = 0;
			}

			$temp = ord($data[$i]) - ord($keyhash[$j]);

			if ($temp < 0) {
				$temp = $temp + 256;
			}
			
			$str .= chr($temp);
		}

		return $str;
	}
}
?>