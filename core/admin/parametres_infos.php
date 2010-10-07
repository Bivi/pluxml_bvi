<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Informations relatives &agrave; PluXml</h2>

<p>Ces informations vous renseignent sur le fonctionnement de votre PluXml et peuvent s'av&eacute;rer utiles pour son d&eacute;pannage.</p>

<ul>
	<li><strong>Version : <?php echo $plxAdmin->version; ?> (encodage <?php echo PLX_CHARSET; ?>)</strong></li>
	<li><?php plxUtils::testWrite(PLX_CONF); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['categories']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['statiques']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['users']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_articles']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_commentaires']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['racine_statiques']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['images']); ?></li>
	<li><?php plxUtils::testWrite(PLX_ROOT.$plxAdmin->aConf['documents']); ?></li>
	<li><?php echo function_exists('imagecreatetruecolor')?'Biblioth&egrave;que GD install&eacute;e':'<span class="alert">Bibliothèque GD non install&eacute;e</span>' ?></li>			
	<li>Nombre de cat&eacute;gories : <?php echo count($plxAdmin->aCats); ?></li>
	<li>Nombre de pages statiques : <?php echo count($plxAdmin->aStats); ?></li>
	<li>Nom du r&eacute;dacteur en session : <?php echo $plxAdmin->aUsers[$_SESSION['user']]['name'] ?></li>
</ul>

<ul>
	<li>Version de php : <?php echo phpversion(); ?></li>
	<li>Etat des "magic quotes" : <?php echo get_magic_quotes_gpc(); ?></li>
</ul>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>