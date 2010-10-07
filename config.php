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

# Définition de l'encodage => PLX_CHARSET : UTF-8 (conseillé) ou ISO-8859-1
define('PLX_CHARSET', 'UTF-8');

# Gestion des erreurs PHP
error_reporting(E_ALL ^ E_NOTICE);

if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get"))
	@date_default_timezone_set(@date_default_timezone_get());

# On démarre la session
session_start();

# On sécurise notre environnement si dans php.ini: register_globals = On
if (ini_get('register_globals')) {
	$array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
	foreach ($array as $value) {
		foreach ($GLOBALS[$value] as $key => $var) {
			if ($var === $GLOBALS[$key]) {
				unset($GLOBALS[$key]);
			}
		}
	}
}

?>