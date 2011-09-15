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
define('PLX_ROOT', '../../');
define('PLX_CORE', PLX_ROOT.'core/');
define('PLX_PLUGINS', PLX_ROOT.'plugins/');
define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');

# On démarre la session
session_start();

# On verifie que PluXml est installé
if(!file_exists(PLX_CONF)) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include_once(PLX_ROOT.'config.php');
include_once(PLX_CORE.'lib/class.plx.date.php');
include_once(PLX_CORE.'lib/class.plx.glob.php');
include_once(PLX_CORE.'lib/class.plx.utils.php');
include_once(PLX_CORE.'lib/class.plx.msg.php');
include_once(PLX_CORE.'lib/class.plx.record.php');
include_once(PLX_CORE.'lib/class.plx.motor.php');
include_once(PLX_CORE.'lib/class.plx.admin.php');
include_once(PLX_CORE.'lib/class.plx.encrypt.php');
include_once(PLX_CORE.'lib/class.plx.medias.php');
include_once(PLX_CORE.'lib/class.plx.plugins.php');
include_once(PLX_CORE.'lib/class.plx.token.php');

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') $_POST = plxUtils::unSlash($_POST);

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Test sur l'identification
if((!defined('PLX_AUTHPAGE') or PLX_AUTHPAGE !== true) AND (!isset($_SESSION['user']) OR $_SESSION['user']=='')) {
	header('Location: auth.php?p='.htmlentities($_SERVER['REQUEST_URI']));
	exit;
}

# Creation de l'objet principal et premier traitement
$plxAdmin = new plxAdmin(PLX_CONF);

# Chargement des fichiers de langue en fonction du profil de l'utilisateur connecté
if(isset($_SESSION['user']) AND !empty($_SESSION['user'])) {
	$profil = $plxAdmin->aUsers[$_SESSION['user']];
	$lang = $profil['lang'];
}
if(empty($lang)) $lang = $plxAdmin->aConf['default_lang'];

loadLang(PLX_CORE.'lang/'.$lang.'/core.php');
loadLang(PLX_CORE.'lang/'.$lang.'/admin.php');

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminPrepend'));

?>