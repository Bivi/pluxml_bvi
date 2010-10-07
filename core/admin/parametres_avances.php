<?php

/**
 * Edition des paramètres avancés
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_avances.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Edition des param&egrave;tres de PluXml</h2>

<form action="parametres_avances.php" method="post" id="change-cf-file">
	<fieldset class="withlabel">
		<legend>Configuration avanc&eacute;e (utilisateur averti) :</legend>
		<p class="field"><label>Activer la r&eacute;&eacute;criture d'urls&nbsp;:</label></p>
		<?php plxUtils::printSelect('urlrewriting',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['urlrewriting']);?>
		<?php if(is_file(PLX_ROOT.'.htaccess') AND $plxAdmin->aConf['urlrewriting']==0) { ?>
			&nbsp;<span class="alert">Attention un fichier .htaccess est d&eacute;jà pr&eacute;sent &agrave; la racine de votre PluXml. En activant la r&eacute;&eacute;criture d'url ce fichier sera &eacute;cras&eacute;</span>
		<?php } ?>
		<p class="field">
			<label>Activer la compression GZIP&nbsp;:</label>
			<a class="help" title="Permet de compresser les pages pour &eacute;conomiser de la bande passante, cependant cela peut augmenter la charge processeur">&nbsp;</a>
		</p>
		<?php plxUtils::printSelect('gzip',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['gzip']);?>		
		<p class="field"><label>Activer le capcha anti-spam&nbsp;:</label></p>
		<?php plxUtils::printSelect('capcha',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['capcha']);?>
		<p class="field">
			<label>Clef d'administration (URLs priv&eacute;s)&nbsp;:</label>
			<a class="help" title="Vider ce champs pour reg&eacute;n&eacute;rer la clef">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('clef', $plxAdmin->aConf['clef'], 'text', '30-30'); ?>
	</fieldset>
	<fieldset class="withlabel">
		<legend>Emplacements des dossiers et des fichiers&nbsp;:</legend>		
		<p class="field">
			<label>Emplacement des images (dossier)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('images', $plxAdmin->aConf['images']); ?>
		<p class="field">
			<label>Emplacement des documents (dossier)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>		
		<?php plxUtils::printInput('documents', $plxAdmin->aConf['documents']); ?>
		<p class="field">
			<label>Emplacement des articles (dossier)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('racine_articles', $plxAdmin->aConf['racine_articles']); ?>
		<p class="field">
			<label>Emplacement des commentaires (dossier)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('racine_commentaires', $plxAdmin->aConf['racine_commentaires']); ?>
		<p class="field">
			<label>Emplacement des pages statiques (dossier)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('racine_statiques', $plxAdmin->aConf['racine_statiques']); ?>
		<p class="field"><label>Emplacement du fichier des cat&eacute;gories (fichier xml)&nbsp;:</label></p>
		<?php plxUtils::printInput('categories', $plxAdmin->aConf['categories']); ?>
		<p class="field"><label>Emplacement du fichier des pages statiques (fichier xml)&nbsp;:</label></p>
		<?php plxUtils::printInput('statiques', $plxAdmin->aConf['statiques']); ?>
		<p class="field"><label>Emplacement du fichier des mots de passe (fichier xml)&nbsp;:</label></p>
		<?php plxUtils::printInput('users', $plxAdmin->aConf['users']); ?>
		<p class="field"><label>Emplacement du fichier des tags (fichier xml)&nbsp;:</label></p>
		<?php plxUtils::printInput('tags', $plxAdmin->aConf['tags']); ?>		
	</fieldset>
	<p class="center"><input type="submit" value="Modifier la configuration avanc&eacute;e" /></p>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>