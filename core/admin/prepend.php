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
define('PLX_ROOT', '../../');
define('PLX_CORE', '../');
define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');

define('PROFIL_ADMIN', 0);
define('PROFIL_MODERATOR', 1);
define('PROFIL_WRITER', 2);

# On verifie que PluXml est installé
if(!file_exists(PLX_CONF)) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include_once(PLX_ROOT.'config.php');
include_once(PLX_CORE.'lib/class.plx.date.php');
include_once(PLX_CORE.'lib/class.plx.utils.php');
include_once(PLX_CORE.'lib/class.plx.msg.php');
include_once(PLX_CORE.'lib/class.plx.glob.php');
include_once(PLX_CORE.'lib/class.plx.record.php');
include_once(PLX_CORE.'lib/class.plx.motor.php');
include_once(PLX_CORE.'lib/class.plx.admin.php');
include_once(PLX_CORE.'lib/class.plx.encrypt.php');
include_once(PLX_CORE.'lib/class.plx.medias.php');

# Echappement des caractères
if($_SERVER['REQUEST_METHOD'] == 'POST') $_POST = plxUtils::unSlash($_POST);

# On impose le charset
header('Content-Type: text/html; charset='.PLX_CHARSET);

# Creation de l'objet principal et premier traitement
$plxAdmin = new plxAdmin(PLX_CONF);

# Test sur l'identification
if((!defined('PLX_AUTHPAGE') or PLX_AUTHPAGE !== true) AND (!isset($_SESSION['user']) OR $_SESSION['user']=='')) {
	header('Location: auth.php?p='.$_SERVER['REQUEST_URI']);
	exit;
}

function a() { return $_SESSION['user']; };
?>