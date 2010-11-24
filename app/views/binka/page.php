<h1>Landing page</h1>

<?php foreach ($posts as $post) { ?>
	<article>
		<header>
			<h1><a href="<?php e($html->url("/post/{$post['permalink']}")); ?>"><?php eh($post['title']); ?></a></h1>
			<p><time pubdate datetime="<?php e(date('Y-m-d', $post['posted'])); ?>"><?php e(date('d F Y', $post['posted'])); ?></time></p>
		</header>
		<?php e($post['post']); ?>
		<footer>
			<nav>
				<?php for($i = 0; $i < count($post['tags']); $i ++) { ?>
					<a href="<?php e(urlencode($html->url("/tag/{$post['tags'][$i]}"))); ?>"><?php eh($post['tags'][$i]); ?></a><?php if ($i < count($post['tags']) - 1) e(','); ?>
				<?php } ?>
			</nav>
		</footer>
	</article>
<?php } ?>

<nav>
	<?php if ($showPreviousPostsLink) { ?>
		<a href="<?php e($html->url('/page/'.($page+1))); ?>">Previous posts</a>
	<?php } ?>
	<?php if ($showNextPostsLink) { ?>
		<a href="<?php e($html->url('/page/'.($page-1))); ?>">Next posts</a>
	<?php } ?>
</nav>
