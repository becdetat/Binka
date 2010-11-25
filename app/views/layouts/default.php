<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Binka Blog Engine</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<link href='http://fonts.googleapis.com/css?family=Droid+Serif&subset=latin' rel='stylesheet' type='text/css'>
		<link href="<?php e($html->url('/css/site.css')); ?>" rel="stylesheet"/>
	</head>
	<body>
		<header>
			<h1>Binka Blog Engine</h1>
		</header>
		<div>
			<nav>
				<div>
					<a href="<?php e($html->url('/')); ?>">Home</a>
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
		
		<style>
		</style>
	</body>
</html>