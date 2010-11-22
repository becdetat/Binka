<?php
class BinkaController extends AppController {
	var $fileComponent;
	
	function beforeAction() {
		parent::beforeAction();
		$this->fileComponent = Dispatcher::loadComponent('file');
	}

	function index() {
	}
	
	function post($link) {
		$matches = glob(Dispatcher::getFilename("/posts/{$link}_*.md"));
		if (count($matches) == 0) {
			$matches = glob(Dispatcher::getFilename("/posts/*_{$link}.md"));
		}

		if (count($matches) == 0) {
			return $this->redirect('/four_oh_four');
		}
		
		if (count($matches) > 1) {
			return $this->view('multiple_matches');
		}
		
		$filename = $matches[0];
		
		$post = $this->fileComponent->read($filename);
		// TODO: strip out metadata
		// TODO: process content
		
		$this->set('post', $post);
	}
	
	function four_oh_four() {
		// TODO: return an actual 404 and show a custom error page
	}
	function multiple_matches() {
	}
}
?>