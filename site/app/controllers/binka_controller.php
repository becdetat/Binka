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
		extract($this->_getPostMatches($link));

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
		
		extract($this->_getPermalinkAndShortlink($link, $filename, $linkIsPermalink));		
		extract($this->_processPost($lines));		
		
		$this->set('permalink', $permalink);
		$this->set('shortlink', $shortlink);
		$this->set('title', $title);
		$this->set('tags', $tags);
		$this->set('posted', $posted);
		$this->set('post', $post);
	}
	
	function _getPostMatches($link) {
		// Find the post. $link is either the permalink or the shortlink.
		$linkIsPermalink = true;
		$matches = glob(Dispatcher::getFilename("/posts/{$link}_*.md"));
		if (count($matches) == 0) {
			$matches = glob(Dispatcher::getFilename("/posts/*_{$link}.md"));
			$linkIsPermalink = false;
		}
		return array(
			'linkIsPermalink' => $linkIsPermalink,
			'matches' => $matches);
	}
	function _processPost($lines) {
		$result = array(
			'title' => '',
			'tags' => array(),
			'posted' => time(),
			'post' => '');
			
		// strip out metadata
		$i = 0;
		for ($i = 0; $i < count($lines); $i ++) {
			$line = trim($lines[$i]);
			if ($line == '') break;
			if (strStartsWith($line, 'title:')) {
				$result['title'] = str_replace('title:', '', $line);
				$result['title'] = trim($result['title']);
				continue;
			}
			if (strStartsWith($line, 'tags:')) {
				$result['tags'] = str_replace('tags:', '', $line);
				$result['tags'] = explode(',', $result['tags']);
				for ($ii = 0; $ii < count($result['tags']); $ii++) $result['tags'][$ii] = trim($result['tags'][$ii]);
				continue;
			}
			if (strStartsWith($line, 'posted:')) {
				$result['posted'] = str_replace('posted:', '', $line);
				$result['posted'] = trim($result['posted']);
				$result['posted'] = strtotime($result['posted']);
				continue;
			}
		}

		// pull out and process content
		$i ++;
		for (; $i < count($lines); $i ++) {
			$result['post'] .= $lines[$i]."\n";
		}
		$result['post'] = Markdown($result['post']);
		
		return $result;
	}
	function _getPermalinkAndShortlink($link, $filename, $linkIsPermalink) {
		$permalink = '';
		$shortlink = '';	
		
		// figure out the permalink and shortlink
		if ($linkIsPermalink) {
			$permalink = $link;
			$shortlink = basename($filename, '.md');
			$shortlink = str_replace("_{$permalink}", '', $shortlink);
		} else {
			$shortlink = $link;
			$permalink = basename($filename, '.md');
			$permalink = str_replace("{$shortlink}_", $permalink);
		}

		return array(
			'permalink' => $permalink,
			'shortlink' => $shortlink);
	}
	
	function four_oh_four() {
		// TODO: return an actual 404 and show a custom error page
	}
	function multiple_matches() {
	}
}
?>