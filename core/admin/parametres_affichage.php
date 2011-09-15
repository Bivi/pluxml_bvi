<?php

/**
 * Edition des paramètres d'affichage
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la configuration
if(!empty($_POST)) {
	$_POST['feed_footer']=$_POST['content'];
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_affichage.php');
	exit;
}

# On récupère les templates
$aStyles[''] = L_NONE1;
$files = plxGlob::getInstance(PLX_ROOT.'themes', true);
if($styles = $files->query("/[a-z0-9-_\.\(\)]+/i")) {
	foreach($styles as $k=>$v) {
		if(substr($v,0,7) != 'mobile.')	$aStyles[$v] = $v;
	}
}

# On récupère la lsite des pages statiques
$pages[''] = L_NONE2;
foreach($plxAdmin->aStats as $number => $static) {
	$pages[$number] = $static['name'];
}

# Tableau du tri
$aTriArts = array('desc'=>L_SORT_DESCENDING_DATE, 'asc'=>L_SORT_ASCENDING_DATE, 'alpha'=>L_SORT_ALPHABETICAL);
$aTriComs = array('desc'=>L_SORT_DESCENDING_DATE, 'asc'=>L_SORT_ASCENDING_DATE);

# On va tester les variables pour les images et miniatures
if(!is_numeric($plxAdmin->aConf['miniatures_l'])) $plxAdmin->aConf['miniatures_l'] = 200;
if(!is_numeric($plxAdmin->aConf['miniatures_h'])) $plxAdmin->aConf['miniatures_h'] = 100;

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_VIEW_FIELD ?></h2>

<div class="content-right">
	<p><?php echo L_CONFIG_VIEW_PLUXML_RESSOURCES ?></p>
</div>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayTop')) # Hook Plugins ?>

<form action="parametres_affichage.php" method="post" id="form_settings">
	<fieldset class="withlabel">
		<p class="field"><label for="id_style"><?php echo L_CONFIG_VIEW_SKIN_SELECT ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('style', $aStyles, $plxAdmin->aConf['style']); ?>
		<?php if(!empty($plxAdmin->aConf['style']) AND is_dir(PLX_ROOT.'themes/'.$plxAdmin->aConf['style'])) : ?>
			&nbsp;<a href="parametres_edittpl.php" title="<?php echo L_CONFIG_VIEW_FILES_EDIT_TITLE ?>"><?php echo L_CONFIG_VIEW_FILES_EDIT ?> &laquo;<?php echo $plxAdmin->aConf['style'] ?>&raquo;</a>
		<?php endif; ?>
		<p class="field"><label for="id_tri"><?php echo L_CONFIG_VIEW_SORT ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('tri', $aTriArts, $plxAdmin->aConf['tri']); ?>
		<p class="field"><label for="id_bypage"><?php echo L_CONFIG_VIEW_BYPAGE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage', $plxAdmin->aConf['bypage'], 'text', '10-10'); ?>
		<p class="field"><label for="id_bypage_archives"><?php echo L_CONFIG_VIEW_BYPAGE_ARCHIVES ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage_archives', $plxAdmin->aConf['bypage_archives'], 'text', '10-10'); ?>
		<p class="field"><label for="id_bypage_admin"><?php echo L_CONFIG_VIEW_BYPAGE_ADMIN ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage_admin', $plxAdmin->aConf['bypage_admin'], 'text', '10-10'); ?>
		<p class="field"><label for="id_tri_coms"><?php echo L_CONFIG_VIEW_SORT_COMS ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('tri_coms', $aTriComs, $plxAdmin->aConf['tri_coms']); ?>
		<p class="field"><label for="id_bypage_admin_coms"><?php echo L_CONFIG_VIEW_BYPAGE_ADMIN_COMS ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage_admin_coms', $plxAdmin->aConf['bypage_admin_coms'], 'text', '10-10'); ?>
		<p class="field"><label><?php echo L_CONFIG_VIEW_THUMBS ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('miniatures_l', $plxAdmin->aConf['miniatures_l'], 'text', '4-4'); ?>	 x
		<?php plxUtils::printInput('miniatures_h', $plxAdmin->aConf['miniatures_h'], 'text', '4-4'); ?>
		<p class="field"><label for="id_homestatic"><?php echo L_CONFIG_VIEW_HOMESTATIC ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('homestatic', $pages, $plxAdmin->aConf['homestatic']); ?>
		<?php if(isset($plxAdmin->aStats[$plxAdmin->aConf['homestatic']]) AND !$plxAdmin->aStats[$plxAdmin->aConf['homestatic']]['active']) : ?>
		&nbsp;<?php echo L_CONFIG_VIEW_HOMESTATIC_ACTIVE ?>
		<?php endif; ?>
		<p class="field"><label for="id_bypage_feed"><?php echo L_CONFIG_VIEW_BYPAGE_FEEDS ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('bypage_feed', $plxAdmin->aConf['bypage_feed'], 'text', '10-10'); ?>
		<p class="field"><label for="id_feed_chapo"><?php echo L_CONFIG_VIEW_FEEDS_HEADLINE ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('feed_chapo',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['feed_chapo']);?>
		<a class="help" title="<?php echo L_CONFIG_VIEW_FEEDS_HEADLINE_HELP ?>">&nbsp;</a>
		<p id="p_content"><label for="id_content"><?php echo L_CONFIG_VIEW_FEEDS_FOOTER ?>&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->aConf['feed_footer']),140,5); ?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplay')) # Hook Plugins ?>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" value="<?php echo L_CONFIG_VIEW_UPDATE ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsDisplayFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>