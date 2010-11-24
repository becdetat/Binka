<?php
/* /lib/components/file.php
** FileComponent, encapsulates some methods for working with disk files
** Contains some inspiration from CodeIgniter
** BJS20090406
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
** Changes:
*/

class FileComponent extends Component {
	// Fields
	
	
	function init() {
	}		
	function beforeAction() {
	}	
	function afterAction() {
	}
	function shutdown() {
	}
	
	
	// wrapper for file_exists()
	function exists($filename) {
		return file_exists($filename);
	}

	// Reads the file
	function read($filename, $mode = 'rb') {
		if (!$this->exists($filename)) {
			throw new Exception('File does not exist');
		}
		
		$handle = @fopen($filename, $mode);
		if ($handle === FALSE) throw new Exception('File could not be opened. Please try again.');
		
		$size = filesize($filename);
		if ($size == 0) throw new Exception('File has zero length. Please try again.');
		
		$data = @fread($handle, $size);
		if ($data === FALSE) throw new Exception('File could not be read. Please try again.');
		
		fclose($handle);
		
		return $data;
	}
	function readText($filename, $mode = 'rb') {
		return $this->read($filename, $mode);
	}
	
	// write a string to a file as text
	function write($filename, $data, $mode = 'wb') {
		$f = fopen($filename, $mode);
		if (!$f) {
			throw new Exception('Could not open file for writing');;
		}
		
		flock($f, LOCK_EX);
		fwrite($f, $data);
		flock($f, LOCK_UN);
		fclose($f);
	}
	function writeText($filename, $data, $mode = 'wb') {
		$this->write($filename, $data, $mode);
	}
	
	// Writes a serialized object to a file. If the Security component is available, encrypts the serialized object first. Read the object
	// with FileComponent::readObject()
	function writeObject($filename, $data, $mode = 'wb', $useEncryption = true) {
		$data = serialize($data);
		if ($useEncryption && !empty($this->controller->Security)) {
			$data = $this->controller->Security->encrypt($data);
		}
		$this->write($filename, $data, $mode);
	}
	
	// Reads a serialized object from a file. If the Security component is available, decrypts the serialized data before unserializing. Write the object
	// with FileComponent::writeObject()
	function readObject($filename, $useEncryption = true) {
		$data = $this->read($filename);
		if ($useEncryption && !empty($this->controller->Security)) {
			$data = $this->controller->Security->decrypt($data);
		}
		$data = unserialize($data);
		return $data;
	}
	
	// Wrapper for unlink()
	function remove($filename) {
		return $this->delete($filename);
	}
	function delete($filename) {
		return unlink($filename);
	}
	
	// get a directory listing of the path. This includes any subdirectories and returns the full path to each entry
	function dir($path, $filesOnly = false) {
		// resolves the path to a canonicalized absolute path and make sure it ends with a directory separator
		$path = rtrim(realpath($path), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		
		$filenames = array_diff(scandir($path), array('.', '..'));
		
		foreach ($filenames as $k=>$v) {
			$filenames[$k] = $path.$v;
		}
		
		if ($filesOnly) {
			$strippedFilenames = array();
			foreach ($filenames as $fn) {
				if (!is_dir($fn)) {
					$strippedFilenames[] = $fn;
				}
			}
			$filenames = $strippedFilenames;
		}
		
		return $filenames;
	}
};

?>