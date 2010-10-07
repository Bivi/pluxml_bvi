<?php
# ------------------ BEGIN LICENSE BLOCK ------------------
#
# This file is part of PluXml : http://pluxml.org
#
# Copyright (c) 2010 Stephane Ferrari and contributors
# Copyright (c) 2008-2009 Florent MONTHEL and contributors
# Copyright (c) 2006-2008 Anthony GUERIN
# Licensed under the GPL license.
# See http://www.gnu.org/licenses/gpl.html
#
# ------------------- END LICENSE BLOCK -------------------

# Configuration avançée #
define('PLX_ROOT', './');
define('PLX_CORE', PLX_ROOT.'core/');
define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');

# On verifie que PluXml n'est pas déjà installé
if(file_exists(PLX_CONF)) {
	header('Content-Type: text/plain charset=UTF-8');
	echo 'PluXml est déjà configuré !';
	exit;
}

# On inclut les librairies nécessaires
include_once(PLX_ROOT.'config.php');
include_once(PLX_CORE.'lib/class.plx.utils.php');

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}
	
# Configuration de base
$f = file(PLX_ROOT.'version');
$version = $f['0'];
$config = array('title'=>'PluXml', 
				'description'=>'le blog full XML',
				'racine'=>plxUtils::getRacine(),
				'delta'=>'+00:00',
				'allow_com'=>1,
				'mod_com'=>0,
				'capcha'=>1,
				'style'=>'defaut',
				'style_mobile'=>'mobile.defaut',
				'clef'=>plxUtils::charAleatoire(15),
				'bypage'=>5,
				'bypage_admin'=>10,
				'bypage_admin_coms'=>10,
				'bypage_feed'=>8,
				'tri'=>'desc',
				'tri_coms'=>'asc',
				'miniatures_l'=>'200',
				'miniatures_h'=>'100',
				'images'=>'data/images/',
				'documents'=>'data/documents/',
				'racine_articles'=>'data/articles/',
				'racine_commentaires'=>'data/commentaires/',
				'racine_statiques'=>'data/statiques/',
				'statiques'=>'data/configuration/statiques.xml',
				'categories'=>'data/configuration/categories.xml',
				'users'=>'data/configuration/users.xml',
				'tags'=>'data/configuration/tags.xml',
				'homestatic'=>'',
				'urlrewriting'=>0,
				'gzip'=>0,
				'feed_chapo'=>0,
				'feed_footer'=>'',
				'editor'=>'plxtoolbar',
				'version'=>$version
				);

function install($content, $config) {

	# Tableau des clés à mettre sous chaîne cdata
	$aCdata = array('title','description','racine');
	
	# Création du fichier de configuration
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	foreach($config as $k=>$v) {
		if(in_array($k,$aCdata))
			$xml .= "\t<parametre name=\"$k\"><![CDATA[".$v."]]></parametre>\n";
		else
			$xml .= "\t<parametre name=\"$k\">".$v."</parametre>\n";
	}
	$xml .= '</document>';
	plxUtils::write($xml,PLX_CONF);
	
	# Création du fichier des utilisateurs
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= "<document>\n";
	$xml .= "\t".'<user number="001" active="1" profil="0" delete="0">'."\n";
	$xml .= "\t\t".'<login><![CDATA['.trim($content['login']).']]></login>'."\n";
	$xml .= "\t\t".'<name><![CDATA['.trim($content['name']).']]></name>'."\n";
	$xml .= "\t\t".'<infos><![CDATA[]]></infos>'."\n";
	$xml .= "\t\t".'<password><![CDATA['.md5(trim($content['pwd'])).']]></password>'."\n";
	$xml .= "\t</user>\n";
	$xml .= "</document>";
	plxUtils::write($xml,PLX_ROOT.$config['users']);
	
	# Création du fichier des categories
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>';
	$xml .= '<document>'."\n";
	$xml .= "\t".'<categorie number="001" tri="'.$config['tri'].'" bypage="'.$config['bypage'].'" menu="oui" url="rubrique-1" template="categorie.php"><![CDATA[Rubrique 1]]></categorie>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['categories']);
	
	# Création du fichier des pages statiques
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<statique number="001" active="1" menu="oui" url="statique-1" template="static.php"><group><![CDATA[]]></group><name><![CDATA[Statique 1]]></name></statique>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['statiques']);
	$cs = '<p><?php echo \'Ma premi&egrave;re page statique !\'; ?></p>';
	plxUtils::write($cs,PLX_ROOT.$config['racine_statiques'].'001.statique-1.php');
	
	# Création du premier article
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>
	<title><![CDATA[Premier article]]></title>
	<allow_com>1</allow_com>
	<template><![CDATA[article.php]]></template>	
	<chapo>
		<![CDATA[]]>
	</chapo>
	<content>
		<![CDATA[<p>Ceci est un article cr&eacute;&eacute; lors de l\'installation de PluXml. Editez-le depuis la zone d\'administration.</p>]]>
	</content>
	<tags>
		<![CDATA[PluXml]]>
	</tags>
</document>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_articles'].'0001.001.001.'.@date('YmdHi').'.premier-article.xml');

	# Création du fichier des tags servant de cache
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>';
	$xml .= '<document>'."\n";
	$xml .= "\t".'<article number="0001" date="'.@date('YmdHi').'" active="1"><![CDATA[PluXml]]></article>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['tags']);

	# Création du premier commentaire
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<comment>
	<author><![CDATA[pluxml]]></author>
		<type>normal</type>
		<ip>127.0.0.1</ip>
		<mail><![CDATA[contact@pluxml.org]]></mail>
		<site><![CDATA[http://pluxml.org]]></site>
		<content><![CDATA[Ceci est un premier commentaire !]]></content>
	</comment>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_commentaires'].'0001.'.@date('U').'-1.xml');
	
}

$msg='';
if(!empty($_POST['install'])) {

	if(trim($_POST['name']=='')) $msg = 'Veuillez renseigner le nom du r&eacute;dacteur !';
	elseif(trim($_POST['login']=='')) $msg = 'Veuillez renseigner le login de connexion !';
	elseif(trim($_POST['pwd']=='')) $msg = 'Veuillez renseigner un mot de passe !';
	elseif($_POST['pwd']!=$_POST['pwd2']) $msg = 'Confirmation du mot de passe incorrecte !';
	else {
		install($_POST, $config);
		header('Location: '.plxUtils::getRacine());
		exit;
	}
	$name=$_POST['name'];
	$login=$_POST['login'];
}
else {
	$name='';
	$login='';
}
?>
<?php header('Content-Type: text/html; charset='.PLX_CHARSET); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>PluXml <?php echo $version; ?> - Installation</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo PLX_ROOT ?>core/admin/admin.css" media="screen" />
</head>

<body>

<div id="main">
	<div id="header">
		<h1>Installation de PluXml</h1>
		<h2>version <?php echo $version; ?></h2>
		<br />
	</div>
	<div id="content">
		<?php if($msg!='') echo '<p class="error">'.$msg.'</p>'; ?>
		<form action="install.php" method="post">
		<fieldset>
			<p class="field"><label>Nom du r&eacute;dacteur&nbsp;<a class="help" title="Nom qui apparaitra comme auteur des articles">&nbsp;</a>&nbsp;:</label></p>
			<?php plxUtils::printInput('name', $name, 'text', '20-255') ?>
			<p class="field"><label>Login de connexion à l'administration&nbsp;:</label></p>
			<?php plxUtils::printInput('login', $login, 'text', '20-255') ?>			
			<p class="field"><label>Mot de passe&nbsp;:</label></p>
			<?php plxUtils::printInput('pwd', '', 'password', '20-255') ?>
			<p class="field"><label>Confirmation du mot de passe&nbsp;:</label></p>
			<?php plxUtils::printInput('pwd2', '', 'password', '20-255') ?>
			<?php plxUtils::printInput('version', $version, 'hidden') ?>
			<p><input type="submit" name="install" value="Installer" /></p>
		</fieldset>
		</form>
		<br />
		<ul>
			<li>Pluxml version : <?php echo $version; ?></li>
			<li><strong><?php plxUtils::testWrite(dirname(PLX_CONF)); ?></strong></li>
			<li><strong><?php plxUtils::testWrite(PLX_ROOT.$config['racine_articles']); ?></strong></li>
			<li><strong><?php plxUtils::testWrite(PLX_ROOT.$config['racine_commentaires']); ?></strong></li>
			<li><strong><?php plxUtils::testWrite(PLX_ROOT.$config['racine_statiques']); ?></strong></li>
			<li><strong><?php echo function_exists("imagecreatetruecolor") ? "Biblioth&egrave;que GD install&eacute;e" : '<span class="alert">Bibliothèque GD non install&eacute;e</span>' ?></strong></li>			
			<li>Version de php : <?php echo phpversion(); ?></li>
			<li>Etat des "magic quotes" : <?php echo get_magic_quotes_gpc(); ?></li>
		</ul>		
	</div>
</div>

</body>
</html>