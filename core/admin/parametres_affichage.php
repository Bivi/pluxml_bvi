<?php

/**
 * Edition des paramètres d'affichage
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
	header('Location: parametres_affichage.php');
	exit;
}

# On récupère les templates
$tpl = plxGlob::getInstance(PLX_ROOT.'themes', true);
$b_style[''] = 'aucun';
$b_style_mobile[''] = 'aucun';
if($a_style = $tpl->query("/[a-z0-9-_\.\(\)]+/i")) {
	foreach($a_style as $k=>$v) {
		if(substr($v,0,7) != 'mobile.') $b_style[ $v ] = $v;
		else $b_style_mobile[ $v ] = $v;	
	}
}

# On récupère la lsite des pages statiques
$pages[''] = 'aucune';
foreach($plxAdmin->aStats as $number => $static) {
	$pages[$number] = $static['name'];
}

# Tableau du tri
$aTri = array('desc'=>'d&eacute;croissant', 'asc'=>'croissant');

# On va tester les variables pour les images et miniatures
if(!is_numeric($plxAdmin->aConf['miniatures_l'])) $plxAdmin->aConf['miniatures_l'] = 200;
if(!is_numeric($plxAdmin->aConf['miniatures_h'])) $plxAdmin->aConf['miniatures_h'] = 100;

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Edition des param&egrave;tres de PluXml</h2>

<form action="parametres_affichage.php" method="post" id="change-cf-file">
	<fieldset class="withlabel">
		<legend>Options d'affichage :</legend>
		<div style="float:left">
			<p class="field"><label>Choix du th&egrave;me&nbsp;:</label></p>
			<?php plxUtils::printSelect('style', $b_style, $plxAdmin->aConf['style']); ?>
			<?php if(!empty($plxAdmin->aConf['style']) AND is_dir(PLX_ROOT.'themes/'.$plxAdmin->aConf['style'])) : ?>
				&nbsp;<a href="parametres_edittpl.php" title="&Eacute;diter les fichiers du th&egrave;me">&Eacute;diter les fichiers du th&egrave;me &laquo;<?php echo $plxAdmin->aConf['style'] ?>&raquo;</a>
			<?php endif; ?>
			<p class="field"><label>Choix du th&egrave;me pour mobile&nbsp;:</label></p>
			<?php plxUtils::printSelect('style_mobile', $b_style_mobile, $plxAdmin->aConf['style_mobile']); ?>
			<?php if(!empty($plxAdmin->aConf['style_mobile']) AND is_dir(PLX_ROOT.'themes/'.$plxAdmin->aConf['style_mobile'])) : ?>
				&nbsp;<a href="parametres_edittpl.php?mobile" title="&Eacute;diter les fichiers du th&egrave;me mobile">&Eacute;diter les fichiers du th&egrave;me mobile &laquo;<?php echo $plxAdmin->aConf['style_mobile'] ?>&raquo;</a>
			<?php endif; ?>
			<p class="field"><label>Tri des articles&nbsp;:</label></p>
			<?php plxUtils::printSelect('tri', $aTri, $plxAdmin->aConf['tri']); ?>
			<p class="field"><label>Nombre d'articles affich&eacute;s par page&nbsp;:</label></p>
			<?php plxUtils::printInput('bypage', $plxAdmin->aConf['bypage'], 'text', '10-10'); ?>
			<p class="field"><label>Nombre d'articles affich&eacute;s par page dans l'administration&nbsp;:</label></p>
			<?php plxUtils::printInput('bypage_admin', $plxAdmin->aConf['bypage_admin'], 'text', '10-10'); ?>
			<p class="field"><label>Tri des commentaires&nbsp;:</label></p>
			<?php plxUtils::printSelect('tri_coms', $aTri, $plxAdmin->aConf['tri_coms']); ?>
			<p class="field"><label>Nombre de commentaires affich&eacute;s par page dans l'administration&nbsp;:</label></p>
			<?php plxUtils::printInput('bypage_admin_coms', $plxAdmin->aConf['bypage_admin_coms'], 'text', '10-10'); ?>
			<p class="field"><label>Taille des miniatures (largeur x hauteur)&nbsp;:</label></p>
			<?php plxUtils::printInput('miniatures_l', $plxAdmin->aConf['miniatures_l'], 'text', '4-4'); ?>	 x 
			<?php plxUtils::printInput('miniatures_h', $plxAdmin->aConf['miniatures_h'], 'text', '4-4'); ?>
			<p class="field"><label>Utiliser une page statique comme page d'accueil&nbsp;:</label></p>
			<?php plxUtils::printSelect('homestatic', $pages, $plxAdmin->aConf['homestatic']); ?>
			<?php if(isset($plxAdmin->aStats[$plxAdmin->aConf['homestatic']]) AND !$plxAdmin->aStats[$plxAdmin->aConf['homestatic']]['active']) : ?>
			&nbsp;<span class="alert">Attention cette page est inactive</span>
			<?php endif; ?>			
		</div>
		<div class="encart">
			<p class="field"><label>T&eacute;l&eacute;charger d'autres th&egrave;mes sur <a href="http://ressources.pluxml.org">ressources.pluxml.org</a>.</label></p>		
		</div>	
	</fieldset>
	<fieldset class="withlabel">	
		<legend>Flux Rss/Atom :</legend>
		<p class="field"><label>Nombre d'articles/commentaires affich&eacute;s sur les fils Rss/Atom&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage_feed', $plxAdmin->aConf['bypage_feed'], 'text', '10-10'); ?>
		<p class="field"><label>Afficher que le chap&ocirc;&nbsp; dans les flux Rss/Atom des articles <a class="help" title="Si le chap&ocirc; est vide, le contenu est affich&eacute;">&nbsp;</a>&nbsp;:</label></p>
		<?php plxUtils::printSelect('feed_chapo',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['feed_chapo']);?>
		<p class="field"><label>Texte &agrave; ajouter comme signature au bas de chaque flux Rss/Atom des articles&nbsp;:</label></p>		
		<?php plxUtils::printArea('feed_footer',plxUtils::strCheck($plxAdmin->aConf['feed_footer']),140,5); ?>
	</fieldset>
	<p class="center"><input type="submit" value="Modifier les options d'affichage" /></p>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>