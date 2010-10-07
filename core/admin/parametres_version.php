<?php

/**
 * Page pour vérifier la version officielle
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');
# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Contrôle du hash
if(isset($_GET['hash']) AND $_GET['hash'] == $_SESSION['hash'])
	$plxAdmin->checkMaj();
else
	plxMsg::Error('Variable de s&eacute;curit&eacute; invalide !');
# Inclusion du header et du footer
include(dirname(__FILE__).'/top.php');
include(dirname(__FILE__).'/foot.php');
?>