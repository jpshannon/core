<!DOCTYPE html>
<html>
<head>
	<title><?= $this->page_title ?></title>
	<?= $this->section('header'); ?>
</head>
<body>
	<section>
		<header>
			<h1>Werx App Example</h1>
		</header>
		<article>
			<?= $this->section('content'); ?>
		</article>
	</section>


	<?= $this->section('footer'); ?>
</body>
</html>