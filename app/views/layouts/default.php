<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Binka Blog Engine</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<header>
			<h1>Binka Blog Engine</h1>
		</header>
		<div>
			<nav>
				<a href="<?php e($html->url('/')); ?>">Home</a>
			</nav>
			<section>
				<?php e($pageContent); ?>
			</section>
		</div>
		<footer>
			<a href="https://github.com/belfryimages/Binka">Binka Blog Engine</a> by <a href="http://blog.belfryimages.com.au">Belfry Images</a>
		</footer>
		
		<style>
		</style>
	</body>
</html>