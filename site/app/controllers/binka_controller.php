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
		$linkIsPermalink = true;
		$matches = glob(Dispatcher::getFilename("/posts/{$link}_*.md"));
		if (count($matches) == 0) {
			$matches = glob(Dispatcher::getFilename("/posts/*_{$link}.md"));
			$linkIsPermalink = false;
		}

		if (count($matches) == 0) {
			return $this->redirect('/four_oh_four');
		}
		
		if (count($matches) > 1) {
			return $this->view('multiple_matches');
		}
		
		$filename = $matches[0];
		
		$title = '';
		$tags = array();
		$posted = time();
		$post = '';
		
		$permalink = '';
		$shortlink = '';		
		if ($linkIsPermalink) {
			$permalink = $link;
			$shortlink = basename($filename, '.md');
			$shortlink = str_replace("_{$permalink}", '', $shortlink);
		} else {
			$shortlink = $link;
			$permalink = basename($filename, '.md');
			$permalink = str_replace("{$shortlink}_", $permalink);
		}		
		
		$lines = explode("\n", $this->fileComponent->read($filename));		
		
		// TODO: strip out metadata
		$i = 0;
		for ($i = 0; $i < count($lines); $i ++) {
			$line = trim($lines[$i]);
			if ($line == '') break;
			if (strStartsWith($line, 'title:')) {
				$title = str_replace('title:', '', $line);
				$title = trim($title);
				continue;
			}
			if (strStartsWith($line, 'tags:')) {
				$tags = str_replace('tags:', '', $line);
				$tags = explode(',', $tags);
				for ($ii = 0; $ii < count($tags); $ii++) $tags[$ii] = trim($tags[$ii]);
				continue;
			}
			if (strStartsWith($line, 'posted:')) {
				$posted = str_replace('posted:', '', $line);
				$posted = trim($posted);
				$posted = strtotime($posted);
				continue;
			}
		}

		// TODO: process content
		$i ++;
		for (; $i < count($lines); $i ++) {
			$post .= $lines[$i]."\n";
		}
		
		$this->set('permalink', $permalink);
		$this->set('shortlink', $shortlink);
		$this->set('title', $title);
		$this->set('tags', $tags);
		$this->set('posted', $posted);
		$this->set('post', $post);
	}
	
	function four_oh_four() {
		// TODO: return an actual 404 and show a custom error page
	}
	function multiple_matches() {
	}
}
?>