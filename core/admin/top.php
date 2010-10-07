<?php if(!defined('PLX_ROOT')) exit; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title><?php echo $plxAdmin->aConf['title']; ?> - Administration</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/admin.css" media="screen" />
	<script type="text/javascript" src="<?php echo PLX_CORE ?>lib/functions.js"></script>
	<?php if(method_exists($plxAdmin->editor, 'addHeader')) $plxAdmin->editor->addHeader(); ?>
	
</head>

<body>

<div id="page">

	<div id="header">
		<h1><?php echo $plxAdmin->aConf['title']; ?> - Administration</h1>
		<div class="subheader">
			<div class="left">
				Connect&eacute; en tant que : <strong><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']) ?></strong>
				&nbsp;<em>(<?php
				if($_SESSION['profil']==PROFIL_ADMIN) echo 'Administrateur';
				elseif($_SESSION['profil']==PROFIL_MODERATOR) echo 'R&eacute;dacteur avanc&eacute;';
				else echo 'R&eacute;dacteur';
				?>)</em>
			</div>
			<div class="right">
				<a href="auth.php?d=1" title="Quitter la session d'administration" id="logout">D&eacute;connexion</a>
				&nbsp;|&nbsp;
				<a href="<?php echo PLX_ROOT; ?>" class="back" title="Revenir au site">Retour</a>
			</div>
		</div>
	</div>

	<div id="menus">
		<div id="navigation">
			<ul>
			<li><a href="index.php?page=1" id="link_articles" title="Liste et modification d'articles">Articles</a></li>
			<li><a href="article.php" id="link_article-new" title="Cr&eacute;ation d'un nouvel article">Nouvel article</a></li>
			<?php if($_SESSION['profil'] == PROFIL_ADMIN) : ?>
			<li><a href="statiques.php" id="link_statiques" title="Liste et modification des pages statiques">Pages statiques</a></li>
			<?php endif; ?>
			<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>
			<li><a href="commentaires_online.php?page=1" id="link_commentaires" title="Liste et modification des commentaires">Commentaires</a></li>
			<?php endif; ?>
			<li><a href="medias.php" id="link_medias" title="Uploader et ins&eacute;rer un m&eacute;dia" onclick="openPopup('medias.php','M&eacute;dias','750','520');return false;">M&eacute;dias</a></li>
			<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>
			<li><a href="categories.php" id="link_categories" title="Cr&eacute;er, g&eacute;rer, &eacute;diter les cat&eacute;gories">Cat&eacute;gories</a></li>
			<?php endif; ?>
			<?php if($_SESSION['profil'] == PROFIL_ADMIN) : ?>
			<li><a href="parametres_base.php" id="link_config" title="Configurer PluXml">Param&egrave;tres</a></li>
			<?php endif; ?>
			<li><a href="profil.php" id="link_user" title="G&eacute;rer votre profil utilisateur">Profil</a></li>
			</ul>
		</div>

	<?php if(file_exists(plxUtils::getSousNav())) : ?>
		<div id="sous_navigation"><?php include(plxUtils::getSousNav()); ?></div>
	<?php endif; ?>

	</div>
	
	<div id="main">
	
<?php
	if(is_file(PLX_ROOT.'install.php')) {
		echo '<p class="error">Le fichier install.php est pr&eacute;sent &agrave; la racine de votre PluXml.<br />Pour des raisons de s&eacute;curit&eacute;, il est fortement conseill&eacute; de le supprimer.</p>';
	}
	plxMsg::Display(); 
?>