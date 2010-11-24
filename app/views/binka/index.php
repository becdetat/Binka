<h1>Landing page</h1>

<?php foreach ($posts as $post) { ?>
	<h2><?php eh($post['title); ?></h2>
	<h3><?php e(date('d F Y', $posted)); ?></h3>
	<?php pr($tags); ?>
	<h4>
		<?php for($i = 0; $i < count($tags); $i ++) {?>
			|<?php eh($tags[$i]); ?>|<?php if ($i < count($tags) - 1) e(','); ?>
		<?php } ?>
	</h4>
	<?php e($post); ?>
	<hr/>
<?php } ?>