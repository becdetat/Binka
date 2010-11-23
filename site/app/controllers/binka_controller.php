<?php
class BinkaController extends AppController {
	var $fileComponent;
	
	function beforeAction() {
		parent::beforeAction();
		$this->fileComponent = Dispatcher::loadComponent('file');
		Dispatcher::loadThirdParty('markdown');
	}

	function index() {
	}
	
	function post($link) {
		// Find the post. $link is either the permalink or the shortlink.
		$linkIsPermalink = true;
		$matches = glob(Dispatcher::getFilename("/posts/{$link}_*.md"));
		if (count($matches) == 0) {
			$matches = glob(Dispatcher::getFilename("/posts/*_{$link}.md"));
			$linkIsPermalink = false;
		}

		// Can't find the post. This should be changed to an actual 404
		if (count($matches) == 0) {
			return $this->redirect('/four_oh_four');
		}
		
		// Multiple matches. This can happen since the posts are stored as
		// physical files without an enforced naming convention.
		if (count($matches) > 1) {
			return $this->view('multiple_matches');
		}
		
		$filename = $matches[0];
		$lines = explode("\n", $this->fileComponent->read($filename));		
		
		// figure out the permalink and shortlink
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
		
		$post = '';
		
		
		// strip out metadata
		$title = '';
		$tags = array();
		$posted = time();
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

		// pull out and process content
		$i ++;
		for (; $i < count($lines); $i ++) {
			$post .= $lines[$i]."\n";
		}
		$post = Markdown($post);
		
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