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

# On verifie que PluXml est installé
if(!file_exists(PLX_CONF)) {
	header('Location: '.PLX_ROOT.'install.php');
	exit;
}

# On inclut les librairies nécessaires
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.capcha.php');
include(PLX_CORE.'lib/class.plx.erreur.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.feed.php');
include(PLX_CORE.'lib/class.plx.show.php');
include(PLX_CORE.'lib/class.plx.encrypt.php');

# Creation de l'objet principal et lancement du traitement
$plxMotor = new plxMotor(PLX_CONF);
$plxMotor->prechauffage();
$plxMotor->demarrage();

# Test sur l'identification pour site privé
if(($plxMotor->aConf['private_site']==1) AND (!defined('PLX_AUTHPAGE') or PLX_AUTHPAGE !== true) AND (!isset($_SESSION['user']) OR $_SESSION['user']=='')) {
	header('Location: core/admin/auth.php?p='.$_SERVER['REQUEST_URI']);
	exit;
}

# Creation de l'objet d'affichage
$plxShow = new plxShow($plxMotor);

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# On charge les fichiers du thème
if($plxMotor->style == '' or !is_dir(PLX_ROOT.'themes/'.$plxMotor->style)) {
	header('Content-Type: text/plain');
	echo 'Le theme principal PluXml est introuvable ('.PLX_ROOT.'themes/'.$plxMotor->style.') !';
} elseif(file_exists(PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template)) {
	# On impose le charset
	header('Content-Type: text/html; charset='.PLX_CHARSET);
	# Insertion du template
	include(PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template);
} else {
	header('Content-Type: text/plain');
	echo 'Le fichier cible PluXml est introuvable ('.PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template.') !';
}

# Affichage
if($plxMotor->aConf['urlrewriting']) {
	if($plxMotor->aConf['gzip'])
		plxUtils::ob_gzipped_page($plxMotor->aConf['racine']);
	else
		echo plxUtils::rel2abs($plxMotor->aConf['racine'], ob_get_clean());
} else {
	if($plxMotor->aConf['gzip'])
		plxUtils::ob_gzipped_page(false);
	else
		echo ob_get_clean();
}

?>