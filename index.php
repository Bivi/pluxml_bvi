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
include(PLX_ROOT.'config.php');
include(PLX_CORE.'lib/class.plx.date.php');
include(PLX_CORE.'lib/class.plx.glob.php');
include(PLX_CORE.'lib/class.plx.utils.php');
include(PLX_CORE.'lib/class.plx.capcha.php');
include(PLX_CORE.'lib/class.plx.erreur.php');
include(PLX_CORE.'lib/class.plx.record.php');
include(PLX_CORE.'lib/class.plx.motor.php');
include(PLX_CORE.'lib/class.plx.feed.php');
include(PLX_CORE.'lib/class.plx.show.php');
include(PLX_CORE.'lib/class.plx.encrypt.php');
include(PLX_CORE.'lib/class.plx.plugins.php');

# Creation de l'objet principal et lancement du traitement
$plxMotor = new plxMotor(PLX_CONF);

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('Index'));

# Chargement du fichier de langue
loadLang(PLX_CORE.'lang/'.$plxMotor->aConf['default_lang'].'/core.php');

$plxMotor->prechauffage();
$plxMotor->demarrage();

# Creation de l'objet d'affichage
$plxShow = new plxShow($plxMotor);

# On démarre la bufferisation
ob_start();
ob_implicit_flush(0);

# Traitements du thème
if($plxMotor->style == '' or !is_dir(PLX_ROOT.'themes/'.$plxMotor->style)) {
	header('Content-Type: text/plain');
	echo L_ERR_THEME_NOTFOUND.' ('.PLX_ROOT.'themes/'.$plxMotor->style.') !';
} elseif(file_exists(PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template)) {
	# On impose le charset
	header('Content-Type: text/html; charset='.PLX_CHARSET);
	# Insertion du template
	include(PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template);
} else {
	header('Content-Type: text/plain');
	echo L_ERR_FILE_NOTFOUND.' ('.PLX_ROOT.'themes/'.$plxMotor->style.'/'.$plxMotor->template.') !';
}

# Récuperation de la bufférisation
$output = ob_get_clean();

# Hooks spécifiques au thème
$output = str_replace('</head>', $plxShow->callHook('ThemeEndHead', false).'</head>', $output);
$output = str_replace('</body>', $plxShow->callHook('ThemeEndBody', false).'</body>', $output);

# Hook Plugins
eval($plxMotor->plxPlugins->callHook('IndexEnd'));

# On applique la réécriture d'url si nécessaire
if($plxMotor->aConf['urlrewriting']) {
	$output = plxUtils::rel2abs($plxMotor->aConf['racine'], $output);
}

# On applique la compression gzip si nécessaire et disponible
if($plxMotor->aConf['gzip']) {
	if($encoding=plxUtils::httpEncoding()) {
		header('Content-Encoding: '.$encoding);
		echo("\x1f\x8b\x08\x00\x00\x00\x00\x00");
		$size = strlen($output);
		$output = gzcompress($output, 9);
		$output = substr($output, 0, $size);
	}
}

# Restitution écran
echo $output;
exit;
?>