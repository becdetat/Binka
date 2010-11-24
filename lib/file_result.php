<?php
/* FileResult
** A kind of ActionResult that renders the provided file (actually a filename and data, since
** most of the time the data will be coming from the database)
** (CC A-SA) 2009 Belfry Images [http://www.belfryimages.com.au | ben@belfryimages.com.au]
*/

class FileResult extends ActionResult {
	var $filename;
	var $data;
	var $encoding;
	var $contentDisposition;
	
	function __construct($filename, $data, $encoding, $contentDisposition) {
		$this->filename = $filename;
		$this->data = $data;
		$this->encoding = $encoding;
		$this->contentDisposition = $contentDisposition;
	}
	
	function render() {
		$mimeType = getMimeTypeFromFilename($this->filename);

		// Generate the headers
		header('Content-Type: '.$mimeType);
		header('Content-Length: '.strlen($this->data));
		header('Content-Disposition: '.$this->contentDisposition.'; filename='.$this->filename);
		header('Content-Transfer-Encoding: '.$this->encoding);

		e($this->data);
	}
	
	function returnRender() {
		return $this->data;
	}
};

?>