<article>
	<header>
		<h1><a rel="bookmark" href="<?php e($html->url("/post/{$post['permalink']}")); ?>"><?php e($post['title']); ?></a></h1>
	</header>
	<?php e($post['post']); ?>
	<footer>
		Posted on <time pubdate datetime="<?php e(date('Y-m-d', $post['posted'])); ?>"><?php e(date('d F Y', $post['posted'])); ?></time>
		and tagged under
		<nav><?php for($i = 0; $i < count($post['tags']); $i ++) { ?><a href="<?php e($html->url('/tag/'.urlencode($post['tags'][$i]))); ?>"><?php eh($post['tags'][$i]); ?></a><?php if ($i < count($post['tags']) - 2) e(', '); else if ($i == count($post['tags']) - 2) e(' and '); ?><?php } ?></nav>.
		<?php $shortlinkUrl = 'http://'.$blogDomain.$html->url("/p/{$post['shortlink']}"); ?>
		
		<?php if ($showTweetButton) { ?>
			<a href="http://twitter.com/share" class="twitter-share-button" 
				data-count="none" 
				data-via="<?php e($twitterUsername); ?>"
				data-url="<?php e($shortlinkUrl); ?>"
			>Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
		<?php } ?>
	</footer>
</article>