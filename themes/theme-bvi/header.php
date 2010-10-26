<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php $plxShow->pageTitle(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php $plxShow->charset(); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom articles" href="./feed.php?atom" />
	<link rel="alternate" type="application/rss+xml" title="Rss articles" href="./feed.php?rss" />
	<link rel="alternate" type="application/atom+xml" title="Atom commentaires" href="./feed.php?atom/commentaires" />
	<link rel="alternate" type="application/rss+xml" title="Rss commentaires" href="./feed.php?rss/commentaires" />
	<!-- style START -->
	<style type="text/css" media="screen">@import url( <?php $plxShow->template(); ?>/style.css );</style>
	<!--[if IE]>
		<link rel="stylesheet" href="<?php $plxShow->template(); ?>/ie.css" type="text/css" media="screen" />
	<![endif]-->
	<!-- style END -->

	<!-- script START -->
	<script type="text/javascript" src="<?php $plxShow->template(); ?>/js/base.js"></script>
	<script type="text/javascript" src="<?php $plxShow->template(); ?>/js/menu.js"></script>

	<!-- script END -->
</head>

<body>

<!-- container START -->
<div id="container">

	<!-- top START -->
	<div id="top">
		<ul>
			<li class="s"><a href="core/admin/">Administration</a></li>
		</ul>
	</div>
	<div class="fixed"></div>
	<!-- top END -->

	<!-- header START -->
	<div id="header">
		<div class="content">
			<div id="title">
				<h1><?php $plxShow->mainTitle('link'); ?></h1>
				<div id="tagline"><?php $plxShow->subTitle(); ?></div>
			</div>
			<div class="fixed"></div>
		</div>
		<div class="meta">
			<ul id="menubar">
				<?php $plxShow->staticList('Accueil','<li class="#static_status" id="#static_id"><a href="#static_url" title="#static_name">#static_name</a></li>'); ?>
			</ul>
			<div id="subscribe" class="feed">
				<a rel="external nofollow" title="Syndication Rss" class="feedlink" href="#"><abbr title="Syndication Rss">Flux</abbr> RSS</a>
					<ul>
						<li><a rel="external nofollow" title="Fil Atom des commentaires" href="./feed.php?atom/commentaires">Commentaires</a></li>
						<li><a rel="external nofollow" title="Fil Atom des articles" href="./feed.php?atom">Articles</a></li>
					</ul>
			</div>
			<div class="fixed"></div>
		</div>
	</div>
	<!-- header END -->

	<!-- content START -->
	<div id="content">

		<!-- main START -->
		<div id="main">