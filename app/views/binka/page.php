<?php foreach ($intros as $intro) e($intro); ?>

<nav>
	<?php if ($showPreviousPostsLink) { ?>
		<a href="<?php e($html->url('/page/'.($page+1))); ?>">Previous posts</a>
	<?php } ?>
	<?php if ($showNextPostsLink) { ?>
		<a href="<?php e($html->url('/page/'.($page-1))); ?>">Next posts</a>
	<?php } ?>
</nav>
