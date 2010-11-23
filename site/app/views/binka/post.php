<h1><?php eh($title); ?></h1>
<h2><?php e(date('d F Y', $posted)); ?></h2>
<?php pr($tags); ?>
<h3>
	<?php for($i = 0; $i < count($tags); $i ++) {?>
		|<?php eh($tags[$i]); ?>|<?php if ($i < count($tags) - 1) e(','); ?>
	<?php } ?>
</h3>

<?php e($post); ?>