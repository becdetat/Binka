<article>
	<header>
		<h1><a rel="bookmark" href="<?php e($html->url("/post/{$post['permalink']}")); ?>"><?php e($post['title']); ?></a></h1>
	</header>
	<?php e($post['post']); ?>
	<footer>
		Posted on <time pubdate datetime="<?php e(date('Y-m-d', $post['posted'])); ?>"><?php e(date('d F Y', $post['posted'])); ?></time>
		and tagged under
		<nav>
			<?php for($i = 0; $i < count($post['tags']); $i ++) { ?>
				<a href="<?php e(urlencode($html->url("/tag/{$post['tags'][$i]}"))); ?>"><?php eh($post['tags'][$i]); ?></a><?php if ($i < count($post['tags']) - 1) e(','); ?>
			<?php } ?>
		</nav>.
		<?php $shortlinkUrl = "http://{$blogDomain}".$html->url("/p/{$post['shortlink']}"); ?>
		Short link: <a rel="shortlink" href="<?php e($shortlinkUrl); ?>"><?php e($shortlinkUrl); ?></a>
	</footer>
</article>