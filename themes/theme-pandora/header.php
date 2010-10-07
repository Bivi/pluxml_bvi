<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php $plxShow->pageTitle(); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php $plxShow->charset(); ?>" />
	<link rel="icon" href="<?php $plxShow->template(); ?>/img/favicon.png" />
	<link rel="stylesheet" type="text/css" href="<?php $plxShow->template(); ?>/style.css" media="screen" />
	<?php $plxShow->templateCss() ?>
	<link rel="alternate" type="application/atom+xml" title="Atom articles" href="<?php $plxShow->urlRewrite('feed.php?atom') ?>" />
	<link rel="alternate" type="application/rss+xml" title="Rss articles" href="<?php $plxShow->urlRewrite('feed.php?rss') ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom commentaires" href="<?php $plxShow->urlRewrite('feed.php?atom/commentaires') ?>" />
	<link rel="alternate" type="application/rss+xml" title="Rss commentaires" href="<?php $plxShow->urlRewrite('feed.php?rss/commentaires') ?>" />

<script src="<?php $plxShow->template(); ?>/js/javascript.js" type="text/javascript"></script>
<!--[if IE 7]><style type="text/css">
.topbars { padding-top: 12px; }
</style>
<![endif]-->
</head>
<body>

<div style="width: 0; height: 0;">
<img src="<?php $plxShow->template(); ?>/images/recent-comments-active.gif" style="display: none;" alt="preload" />
<img src="<?php $plxShow->template(); ?>/images/recent-posts-inactive.gif" style="display: none;" alt="preload" />
<img src="<?php $plxShow->template(); ?>/images/tags-active.gif" style="display: none;" alt="preload" />
<img src="<?php $plxShow->template(); ?>/images/widget-hover.gif" style="display: none;" alt="preload" />
</div>

<div class="banner">
<div class="marginauto">
<div id="top" class="logo">
<a href="<?php $plxShow->urlRewrite('index.php') ?>" title="<?php $plxShow->mainTitle(); ?>"></a>
<span class="slogan"><?php $plxShow->subTitle(); ?></span>

<form method="get" id="searchform-header" action="<?php $plxShow->urlRewrite('/static4/recherche') ?>">
<div><input type="hidden" name="search" value="search"  /><input type="text" value="Rechercher..." onblur="if(this.value=='') this.value='Rechercher...';" onfocus="if(this.value=='Rechercher...') this.value='';" name="searchfield" id="s-header" /></div>
<div><input type="submit" id="searchsubmit-header" value="" /></div>
</form>

</div>

<div class="topbars">
<ul class="topnav">
	<?php $plxShow->staticList('Accueil','<li class="page_item" id="#static_id"><a class="#static_status" href="#static_url" title="#static_name">#static_name</a></li>'); ?>
	<?php $plxShow->pageBlog('<li class="page_item" id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>'); ?>
</ul>
</div><!-- end.topbars -->

</div><!-- end.marginauto -->
</div><!-- end.banner -->

<div class="wrap"><!-- ends in footer.php -->
<div class="contentwrap">
<div class="posts-wrap"><!-- ends in sidebar.php -->