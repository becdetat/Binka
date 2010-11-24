<h1>Landing page</h1>

<?php foreach ($posts as $post) { ?>
	<h2><?php e($post['shortlink']); ?> - <?php eh($post['title']); ?></h2>
	<h3><?php e(date('d F Y', $post['posted'])); ?></h3>
	<?php pr($post['tags']); ?>
	<h4>
		<?php for($i = 0; $i < count($post['tags']); $i ++) {?>
			|<?php eh($post['tags'][$i]); ?>|<?php if ($i < count($post['tags']) - 1) e(','); ?>
		<?php } ?>
	</h4>
	<?php e($post['post']); ?>
	<hr/>
<?php } ?>

<?php if ($showPreviousPostsLink) { ?>
	<a href="<?php e($html->url('/page/'.($page+1))); ?>">Previous posts</a><br />
<?php } ?>
<?php if ($showNextPostsLink) { ?>
	<a href="<?php e($html->url('/page/'.($page-1))); ?>">Next posts</a>
<?php } ?>