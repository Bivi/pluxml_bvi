<?php
# ------------------ BEGIN LICENSE BLOCK ------------------
#
# This file is part of PluXml : http://pluxml.org
#
# Copyright (c) 2010-2011 Stephane Ferrari and contributors
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

# On démarre la session
session_start();

# Chargement du fichier de configuration
include(PLX_ROOT.'config.php');

# Chargement des langues
$lang = DEFAULT_LANG;
if(isset($_POST['default_lang'])) $lang=$_POST['default_lang'];
loadLang(PLX_CORE.'lang/'.$lang.'/install.php');
loadLang(PLX_CORE.'lang/'.$lang.'/core.php');

# On vérifie que PHP 5 ou superieur soit installé
if(version_compare(PHP_VERSION, '5.0.0', '<')){
    header('Content-Type: text/plain charset=UTF-8');
    echo L_WRONG_PHP_VERSION;
    exit;
}

# On vérifie que PluXml n'est pas déjà installé
if(file_exists(PLX_CONF)) {
	header('Content-Type: text/plain charset=UTF-8');
	echo L_ERR_PLUXML_ALREADY_INSTALLED;
	exit;
}

# On inclut les librairies nécessaires
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.token.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Vérification de l'existence des dossiers data/images et data/documents
if(!is_dir('data/images')) {
	@mkdir('data/images',0755);
}
if(!is_dir('data/documents')) {
	@mkdir('data/documents',0755);
}

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	$_POST = plxUtils::unSlash($_POST);
}

# Configuration de base
$f = file(PLX_ROOT.'version');
$version = $f['0'];
$config = array('title'=>'PluXml',
				'description'=>plxUtils::strRevCheck(L_SITE_DESCRIPTION),
				'meta_description'=>'',
				'meta_keywords'=>'',
				'racine'=>plxUtils::getRacine(),
				'delta'=>'+00:00',
				'allow_com'=>1,
				'mod_com'=>0,
				'capcha'=>1,
				'style'=>'defaut',
				'clef'=>plxUtils::charAleatoire(15),
				'bypage'=>5,
				'bypage_archives'=>5,
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
				'plugins'=>'data/configuration/plugins.xml',
				'homestatic'=>'',
				'urlrewriting'=>0,
				'gzip'=>0,
				'feed_chapo'=>0,
				'feed_footer'=>'',
				'version'=>$version,
				'default_lang'=>$lang,
				'userfolders'=>0,
				);

function install($content, $config) {

	# Tableau des clés à mettre sous chaîne cdata
	$aCdata = array('title','description','racine','feed_footer','clef','meta_description','meta_keywords');
	# Tableau des clés à mettre sous forme de numérique
	$aNum = array('bypage','bypage_archives','bypage_admin','bypage_admin_coms','bypage_feed','miniatures_l','miniatures_h');

	# Création du fichier de configuration
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	foreach($config as $k=>$v) {
		if(in_array($k,$aCdata))
			$xml .= "\t<parametre name=\"$k\"><![CDATA[".plxUtils::cdataCheck($v)."]]></parametre>\n";
		elseif(in_array($k,$aNum))
			$xml .= "\t<parametre name=\"$k\">".intval($v)."</parametre>\n";
		else
			$xml .= "\t<parametre name=\"$k\">".plxUtils::cdataCheck($v)."</parametre>\n";
	}
	$xml .= '</document>';
	plxUtils::write($xml,PLX_CONF);

	# Création du fichier des utilisateurs
	$salt = plxUtils::charAleatoire(10);
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= "<document>\n";
	$xml .= "\t".'<user number="001" active="1" profil="0" delete="0">'."\n";
	$xml .= "\t\t".'<login><![CDATA['.trim($content['login']).']]></login>'."\n";
	$xml .= "\t\t".'<name><![CDATA['.trim($content['name']).']]></name>'."\n";
	$xml .= "\t\t".'<infos><![CDATA[]]></infos>'."\n";
	$xml .= "\t\t".'<password><![CDATA['.sha1($salt.md5(trim($content['pwd']))).']]></password>'."\n";
	$xml .= "\t\t".'<salt><![CDATA['.$salt.']]></salt>'."\n";
	$xml .= "\t\t".'<email><![CDATA[]]></email>'."\n";
	$xml .= "\t\t".'<lang><![CDATA['.$config['default_lang'].']]></lang>'."\n";
	$xml .= "\t</user>\n";
	$xml .= "</document>";
	plxUtils::write($xml,PLX_ROOT.$config['users']);

	# Création du fichier des categories
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<categorie number="001" tri="'.$config['tri'].'" bypage="'.$config['bypage'].'" menu="oui" url="'.L_DEFAULT_CATEGORY_URL.'" template="categorie.php"><name><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_CATEGORY_TITLE).']]></name><description><![CDATA[]]></description><meta_description><![CDATA[]]></meta_description><meta_keywords><![CDATA[]]></meta_keywords></categorie>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['categories']);

	# Création du fichier des pages statiques
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<statique number="001" active="1" menu="oui" url="'.L_DEFAULT_STATIC_URL.'" template="static.php"><group><![CDATA[]]></group><name><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_STATIC_TITLE).']]></name><meta_description><![CDATA[]]></meta_description><meta_keywords><![CDATA[]]></meta_keywords></statique>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['statiques']);
	$cs = '<p><?php echo \''.plxUtils::strRevCheck(L_DEFAULT_STATIC_CONTENT).'\'; ?></p>';
	plxUtils::write($cs,PLX_ROOT.$config['racine_statiques'].'001.'.L_DEFAULT_STATIC_URL.'.php');

	# Création du premier article
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>
	<title><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_ARTICLE_TITLE).']]></title>
	<allow_com>1</allow_com>
	<template><![CDATA[article.php]]></template>
	<chapo>
		<![CDATA[]]>
	</chapo>
	<content>
		<![CDATA[<p>'.plxUtils::strRevCheck(L_DEFAULT_ARTICLE_CONTENT).'</p>]]>
	</content>
	<tags>
		<![CDATA[PluXml]]>
	</tags>
	<meta_description>
		<![CDATA[]]>
	</meta_description>
	<meta_keywords>
		<![CDATA[]]>
	</meta_keywords>
</document>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_articles'].'0001.001.001.'.@date('YmdHi').'.'.L_DEFAULT_ARTICLE_URL.'.xml');

	# Création du fichier des tags servant de cache
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= "\t".'<article number="0001" date="'.@date('YmdHi').'" active="1"><![CDATA[PluXml]]></article>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['tags']);

	# Création du fichier des plugins
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<document>'."\n";
	$xml .= '</document>';
	plxUtils::write($xml,PLX_ROOT.$config['plugins']);

	# Création du premier commentaire
	$xml = '<?xml version="1.0" encoding="'.PLX_CHARSET.'"?>'."\n";
	$xml .= '<comment>
	<author><![CDATA[pluxml]]></author>
		<type>normal</type>
		<ip>127.0.0.1</ip>
		<mail><![CDATA[contact@pluxml.org]]></mail>
		<site><![CDATA[http://pluxml.org]]></site>
		<content><![CDATA['.plxUtils::strRevCheck(L_DEFAULT_COMMENT_CONTENT).']]></content>
	</comment>';
	plxUtils::write($xml,PLX_ROOT.$config['racine_commentaires'].'0001.'.@date('U').'-1.xml');

}

$msg='';
if(!empty($_POST['install'])) {

	if(trim($_POST['name']=='')) $msg = L_ERR_MISSING_USER;
	elseif(trim($_POST['login']=='')) $msg = L_ERR_MISSING_LOGIN;
	elseif(trim($_POST['pwd']=='')) $msg = L_ERR_MISSING_PASSWORD;
	elseif($_POST['pwd']!=$_POST['pwd2']) $msg = L_ERR_PASSWORD_CONFIRMATION;
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
plxUtils::cleanHeaders();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang ?>" lang="<?php echo $lang ?>">
<head>
	<title><?php echo L_PLUXML_INSTALLATION.' '.L_VERSION.' '.$version ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/base.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/theme/style.css" media="screen" />
</head>
<body onload="document.forms[0].name.focus();">

<div id="main">
	<div id="top">
		<div style="margin-left:200px"><h2><?php echo L_PLUXML_VERSION.' '.$version ?> - <?php echo L_INSTALL_TITLE ?></h2></p>
	</div>
	<div id="content">
		<?php if($msg!='') echo '<p class="error">'.$msg.'</p>'; ?>
		<form action="install.php" method="post">
		<fieldset class="panel">
			<p class="field"><label for="id_default_lang"><?php echo L_SELECT_LANG ?>&nbsp;:</label></p>
			<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $lang) ?>&nbsp;
			<input type="submit" name="select_lang" value="<?php echo L_INPUT_CHANGE ?>" />
			<?php echo plxToken::getTokenPostMethod() ?>
		</fieldset>
		<fieldset class="panel">
			<p class="field"><label for="id_name"><?php echo L_USERNAME ?>&nbsp;:</label></p>
			<?php plxUtils::printInput('name', $name, 'text', '20-255') ?>
			<p class="field"><label for="id_login"><?php echo L_LOGIN ?>&nbsp;:</label></p>
			<?php plxUtils::printInput('login', $login, 'text', '20-255') ?>
			<p class="field"><label for="id_pwd"><?php echo L_PASSWORD ?>&nbsp;:</label></p>
			<?php plxUtils::printInput('pwd', '', 'password', '20-255') ?>
			<p class="field"><label for="id_pwd2"><?php echo L_PASSWORD_CONFIRMATION ?>&nbsp;:</label></p>
			<?php plxUtils::printInput('pwd2', '', 'password', '20-255') ?>
			<?php plxUtils::printInput('version', $version, 'hidden') ?>
			<p><input type="submit" name="install" value="<?php echo L_INPUT_INSTALL ?>" /></p>
		</fieldset>
		</form>
		<div class="panel">
			<ul>
				<li><strong><?php echo L_PLUXML_VERSION; ?> <?php echo $version; ?> (<?php echo L_INFO_CHARSET ?> <?php echo PLX_CHARSET ?>)</strong></li>
				<li><?php echo L_INFO_PHP_VERSION.' : '.phpversion() ?></li>
				<li><?php echo L_INFO_MAGIC_QUOTES.' : '.get_magic_quotes_gpc() ?></li>
				<?php plxUtils::testWrite(dirname(PLX_CONF)) ?>
				<?php plxUtils::testWrite(PLX_ROOT.$config['racine_articles']) ?>
				<?php plxUtils::testWrite(PLX_ROOT.$config['racine_commentaires']) ?>
				<?php plxUtils::testWrite(PLX_ROOT.$config['racine_statiques']) ?>
				<?php plxUtils::testWrite(PLX_ROOT.$config['images']) ?>
				<?php plxUtils::testWrite(PLX_ROOT.$config['documents']) ?>
				<?php plxUtils::testModReWrite() ?>
				<?php plxUtils::testLibGD() ?>
				<?php plxUtils::testMail() ?>
			</ul>
		</div>
	</div>
</div>

</body>
</html>