<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Binka Blog Engine</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link href='http://fonts.googleapis.com/css?family=Droid+Serif&subset=latin' rel='stylesheet' type='text/css'>
		<link href="<?php e($html->url('/css/site.css')); ?>" rel="stylesheet"/>
		<?php if ($enableSyntaxHighlighter) { ?>
			<link rel="stylesheet" href="<?php e($html->url('/syntaxhighlighter_3.0.83/styles/shCoreDefault.css')); ?>" />
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shCore.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushCSharp.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushCss.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushJScript.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushPhp.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushRuby.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushSql.js')); ?>"></script>
			<script src="<?php e($html->url('/syntaxhighlighter_3.0.83/scripts/shBrushXml.js')); ?>"></script>
			<script>
				SyntaxHighlighter.all();
			</script>
		<?php } ?>
	</head>
	<body>
		<header>
			<h1>Binka Blog Engine</h1>
		</header>
		<div>
			<nav>
				<div>
					<h1>Pages</h1>
					<p>
						<a href="<?php e($html->url('/')); ?>">Home</a>
					</p>
					<h1>Tags</h1>
					<p>
						<?php foreach ($tags as $tag => $tagCount) { ?>
							<a href="<?php e($html->url('/tag/'.urlencode($tag))); ?>">
								<?php eh($tag); ?> (<?php e($tagCount); ?>)
							</a><br/>
						<?php } ?>
					</p>
						
				</div>
			</nav>
			<section>
				<?php e($pageContent); ?>
			</section>
		</div>
		<footer>
			<div>
				Powered by <a href="https://github.com/belfryimages/Binka">Binka Blog Engine</a> by <a href="http://blog.belfryimages.com.au">Belfry Images</a>
			</div>
		</footer>
	</body>
</html>