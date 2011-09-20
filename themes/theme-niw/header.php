<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php $plxShow->pageTitle(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php $plxShow->charset(); ?>" />
	<?php $plxShow->meta('description') ?>
	<?php $plxShow->meta('keywords') ?>
<!--	<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />-->
	<link rel="stylesheet" type="text/css" href="<?php $plxShow->template(); ?>/style.css" media="screen" />
	<?php $plxShow->templateCss() ?>
	<link rel="alternate" type="application/rss+xml" title="Rss articles" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
	<link rel="alternate" type="application/rss+xml" title="Rss commentaires" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />
<!--  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shCore.js" type="text/javascript"/>
  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shBrushJScript.js" type="text/javascript"/>
  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shBrushCss.js" type="text/javascript"/>
  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shBrushPlain.js" type="text/javascript"/>
  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shBrushXml.js" type="text/javascript"/>
  <script src="<?php $plxShow->template(); ?>/syntax/scripts/shBrushXQuery.js" type="text/javascript"/>
	<link  href="<?php $plxShow->template(); ?>/syntax/styles/shCoreDefault.css" type="text/css" rel="stylesheet" />
  <link  href="<?php $plxShow->template(); ?>/syntax/styles/shCore.css" rel="stylesheet" type="text/css"/>
  <link    id="theme" href="<?php $plxShow->template(); ?>/syntax/styles/shThemeDefault.css" rel="Stylesheet" type="text/css"/>
	<script type="text/javascript">SyntaxHighlighter.all();</script>-->
</head>
<body>
<div id="top">
	<ul id="access">
		<li><a href="<?php $plxShow->urlRewrite('#content') ?>" title="Aller au contenu">Aller au contenu</a></li>
		<li><a href="<?php $plxShow->urlRewrite('#menu') ?>" title="Aller au menu">Aller au menu</a></li>
	</ul>
	<div id="header">
    <img src="<?php $plxShow->template(); ?>/images/logo_h100.png" style="float:left;padding-left: 20px; padding-top: 30px;"/>
		<h1><?php $plxShow->mainTitle('link'); ?></h1>
		<p><?php $plxShow->subTitle(); ?></p>
	</div>
	<div id="menu">
		<ul class="left">
			<?php $plxShow->staticList('Accueil','<li id="#static_id"><a href="#static_url" class="#static_status" title="#static_name">#static_name</a></li>'); ?>
			<?php $plxShow->pageBlog('<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>'); ?>
		</ul>
		<ul class="right">
			<li><a class="feed" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" title="Fil RSS des commentaires">Commentaires</a></li>
			<li><a class="feed" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" title="Fil RSS des articles">Articles</a></li>
		</ul>
		<div class="clearer"></div>
	</div>
</div>