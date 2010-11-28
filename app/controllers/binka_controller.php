<?php
class BinkaController extends AppController {
	// Config:
	var $binka_post_extension = '.markdown';
	var $binka_posts_per_page = 10;
	var $binka_show_tweet_button = true;
	var $binka_twitter_username = 'belfryimages';
	
	var $fileComponent;
	
	var $posts;
	
	function beforeAction() {
		parent::beforeAction();
		$this->fileComponent = Dispatcher::loadComponent('file');
		Dispatcher::loadThirdParty('markdown');
		
		$files = glob(Dispatcher::getFilename("/posts/*{$this->binka_post_extension}"));
		$files = array_reverse($files);
		$this->posts = $this->__getPosts($files);
		
		$this->set('blogDomain', $_SERVER['SERVER_NAME']);
		$this->set('showTweetButton', $this->binka_show_tweet_button);
		$this->set('twitterUsername', $this->binka_twitter_username);
		$this->set('tags', $this->__getTags($this->posts));
	}
	
	
	function four_oh_four() {
		// TODO: return an actual 404 and show a custom error page
	}
	
	function multiple_matches() {
	}

	function page($p = 1) {		
		$from = ($p - 1) * $this->binka_posts_per_page;
		$to = $from + $this->binka_posts_per_page;
		$postCount = count($this->posts);
		if ($to > $postCount) $to = $postCount;
		$posts = array_slice($this->posts, $from, $to - $from);
		
		
		$this->set('intros', $this->__getIntroViews($posts));
		$this->set('page', $p);
		$this->set('showPreviousPostsLink', $to < $postCount);
		$this->set('showNextPostsLink', $from > 0);
	}
	
	function tag($t) {
		$tagPosts = array();
		foreach ($this->posts as $post) {
			if (in_array($t, $post['tags'])) {
				$tagPosts[] = $post;
			}
		}
		
		$this->set('tag', $t);
		$this->set('intros', $this->__getIntroViews($tagPosts));
	}
	
	function post($link) {
		$posts = array();
		foreach ($this->posts as $post) {
			if ($post['shortlink'] == $link || $post['permalink'] == $link) {
				$posts[] = $post;
			}
		}

		// Can't find the post. This should be changed to an actual 404
		if (count($posts) == 0) {
			return $this->redirect('/four_oh_four');
		}
		
		// Multiple matches. This can happen since the posts are stored as
		// physical files without an enforced naming convention.
		if (count($posts) > 1) {
			return $this->view('multiple_matches');
		}
		
		$this->set('post', $posts[0]);
		
	}
	
	function partial_post_intro() {
		$this->set('post', $this->data['post']);
		return $this->partial();
	}
	
	
	function __getTags($posts) {
		$tags = array();
		foreach ($posts as $post) {
			foreach ($post['tags'] as $tag) {
				if (empty($tags[$tag])) {
					$tags[$tag] = 1;
				} else {
					$tags[$tag]++;
				}
			}
		}
		ksort($tags);
		return $tags;
	}
	
	function __getPosts($files) {
		$posts = array();
		foreach ($files as $filename) {
			$posts[] = $this->__processPost($filename);
		}
		return $posts;
	}

	function __processPost($filename) {
		$shortlink = explode('_', basename($filename));
		$shortlink = $shortlink[0];
		$permalink = str_replace($shortlink.'_', '', basename($filename));
		$permalink = str_replace($this->binka_post_extension, '', $permalink);

		$result = array(
			'shortlink' => $shortlink,
			'permalink' => $permalink,
			'title' => '',
			'tags' => array(),
			'posted' => time(),
			'post' => '',
			'intro' => '',
			'hasMore' => false);
			
		$lines = explode("\n", $this->fileComponent->read($filename));		

		// strip out metadata. Stops processing on first blank link.
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
		$result['intro'] = $result['post'];
		$foldNeedle = '<p>@@FOLD</p>';
		if (strContains($result['intro'], $foldNeedle)) {
			$result['intro'] = explode($foldNeedle, $result['intro']);
			$result['intro'] = $result['intro'][0];
			$result['hasMore'] = true;
			$result['post'] = str_replace($foldNeedle, '', $result['post']);
		}
		
		return $result;
	}
	
	function __getIntroViews($posts) {
		$intros = array();
		foreach ($posts as $post) {
			$intros[] = 
				Dispatcher::dispatch('/binka/partial_post_intro', array('post' => $post))->returnRender();
		}
		return $intros;
	}
}
?>