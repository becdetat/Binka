<?php
class BinkaController extends AppController {
	// Config:
	var $binka_post_extension = '.markdown';
	var $binka_posts_per_page = 10;
	var $binka_show_tweet_button = true;
	var $binka_twitter_username = 'belfryimages';
	
	var $fileComponent;
	
	function beforeAction() {
		parent::beforeAction();
		$this->fileComponent = Dispatcher::loadComponent('file');
		Dispatcher::loadThirdParty('markdown');
		
		$this->set('blogDomain', $_SERVER['SERVER_NAME']);
		$this->set('showTweetButton', $this->binka_show_tweet_button);
		$this->set('twitterUsername', $this->binka_twitter_username);
	}

	function page($p = 1) {
		$files = glob(Dispatcher::getFilename("/posts/*{$this->binka_post_extension}"));
		$files = array_reverse($files);
		
		$from = ($p - 1) * $this->binka_posts_per_page;
		$to = $from + $this->binka_posts_per_page;
		$fileCount = count($files);
		if ($to > $fileCount) $to = $fileCount;
		$files = array_slice($files, $from, $to - $from);
		
		$posts = $this->_getPosts($files);
		
		$this->set('posts', $posts);
		$this->set('page', $p);
		$this->set('showPreviousPostsLink', $to < $fileCount);
		$this->set('showNextPostsLink', $from > 0);
	}
	
	function tag($t) {
		$files = glob(Dispatcher::getFilename("/posts/*{$this->binka_post_extension}"));
		$files = array_reverse($files);
		
		$posts = $this->_getPosts($files);
		$filteredPosts = array();
		foreach ($posts as $post) {
			if (in_array($t, $post['tags'])) {
				$filteredPosts[] = $post;
			}
		}
		$posts = $filteredPosts;
		
		$this->set('tag', $t);
		$this->set('posts', $posts);
	}
	
	function _getPosts($files) {
		$posts = array();
		foreach ($files as $filename) {
			extract($this->_getPermalinkAndShortlink($filename));
			extract($this->_processPost($filename));
			
			array_push($posts, array(
				'shortlink' => $shortlink,
				'permalink' => $permalink,
				'title' => $title,
				'tags' => $tags,
				'posted' => $posted,
				'post' => $post
			));
		}
		return $posts;
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
		
		extract($this->_getPermalinkAndShortlink($filename));
		extract($this->_processPost($filename));		
		
		$this->set('post', array(
			'permalink' => $permalink,
			'shortlink' => $shortlink,
			'title' => $title,
			'tags' => $tags,
			'posted' => $posted,
			'post' => $post
		));
		
	}
	
	function _getPostMatches($link) {
		// Find the post. $link is either the permalink or the shortlink.
		$linkIsPermalink = true;
		$matches = glob(Dispatcher::getFilename("/posts/{$link}_*{$this->binka_post_extension}"));
		if (count($matches) == 0) {
			$matches = glob(Dispatcher::getFilename("/posts/*_{$link}{$this->binka_post_extension}"));
			$linkIsPermalink = false;
		}
		return array(
			'linkIsPermalink' => $linkIsPermalink,
			'matches' => $matches);
	}
	function _processPost($filename) {
		$lines = explode("\n", $this->fileComponent->read($filename));		

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
	function _getPermalinkAndShortlink($filename) {
		$permalink = '';
		$shortlink = '';	
		
		$shortlink = explode('_', basename($filename));
		$shortlink = $shortlink[0];
		$permalink = str_replace($shortlink.'_', '', basename($filename));
		$permalink = str_replace($this->binka_post_extension, '', $permalink);

		$result = array(
			'permalink' => $permalink,
			'shortlink' => $shortlink);
		return $result;
	}
	
	function four_oh_four() {
		// TODO: return an actual 404 and show a custom error page
	}
	function multiple_matches() {
	}
}
?>