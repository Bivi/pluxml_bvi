<?php

/**
 * Edition des paramètres de base
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
	$plxAdmin->editConfiguration($plxAdmin->aConf,$_POST);
	header('Location: parametres_base.php');
	exit;
}

# Tableau des fuseaux horaires
$delta["-12:00"] = "(GMT -12:00) Eniwetok, Kwajalein";
$delta["-11:00"] = "(GMT -11:00) Midway Island, Samoa";
$delta["-10:00"] = "(GMT -10:00) Hawaii";
$delta["-09:00"] = "(GMT -9:00) Alaska";
$delta["-08:00"] = "(GMT -8:00) Pacific Time (US &amp; Canada)";
$delta["-07:00"] = "(GMT -7:00) Mountain Time (US &amp; Canada)";
$delta["-06:00"] = "(GMT -6:00) Central Time (US &amp; Canada), Mexico City";
$delta["-05:00"] = "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima";
$delta["-04:50"] = "(GMT -4:30) Caracas";
$delta["-04:00"] = "(GMT -4:00) Atlantic Time (Canada), La Paz, Santiago";
$delta["-03:50"] = "(GMT -3:30) Newfoundland";
$delta["-03:00"] = "(GMT -3:00) Brazil, Buenos Aires, Georgetown";
$delta["-02:00"] = "(GMT -2:00) Mid-Atlantic";
$delta["-01:00"] = "(GMT -1:00 hour) Azores, Cape Verde Islands";
$delta["+00:00"] = "(GMT) Western Europe Time, London, Lisbon, Casablanca";
$delta["+01:00"] = "(GMT +1:00) Brussels, Copenhagen, Madrid, Paris";
$delta["+02:00"] = "(GMT +2:00) Kaliningrad, South Africa";
$delta["+03:00"] = "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg";
$delta["+03:50"] = "(GMT +3:30) Tehran";
$delta["+04:00"] = "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi";
$delta["+04:50"] = "(GMT +4:30) Kabul";
$delta["+05:00"] = "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent";
$delta["+05:50"] = "(GMT +5:30) Mumbai, Kolkata, Chennai, New Delhi";
$delta["+05:75"] = "(GMT +5:45) Kathmandu";
$delta["+06:00"] = "(GMT +6:00) Almaty, Dhaka, Colombo";
$delta["+06:50"] = "(GMT +6:30) Yangon, Cocos Islands";
$delta["+07:00"] = "(GMT +7:00) Bangkok, Hanoi, Jakarta";
$delta["+08:00"] = "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong";
$delta["+09:00"] = "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk";
$delta["+09:50"] = "(GMT +9:30) Adelaide, Darwin";
$delta["+10:00"] = "(GMT +10:00) Eastern Australia, Guam, Vladivostok";
$delta["+11:00"] = "(GMT +11:00) Magadan, Solomon Islands, New Caledonia";
$delta["+12:00"] = "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka";

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_BASE_CONFIG_TITLE ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseTop')) # Hook Plugins ?>

<form action="parametres_base.php" method="post" id="form_settings">
	<fieldset class="config">
		<p class="field"><label for="id_title"><?php echo L_CONFIG_BASE_SITE_TITLE ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?>
		<p class="field"><label for="id_description"><?php echo L_CONFIG_BASE_SITE_SLOGAN ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?>
		<p class="field"><label for="id_racine"><?php echo L_CONFIG_BASE_SITE_URL ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('racine', $plxAdmin->racine);?>
		<a class="help" title="<?php echo L_CONFIG_BASE_URL_HELP ?>">&nbsp;</a>
		<p class="field"><label for="id_meta_description"><?php echo L_CONFIG_META_DESCRIPTION ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('meta_description', plxUtils::strCheck($plxAdmin->aConf['meta_description'])); ?>
		<p class="field"><label for="id_meta_keywords"><?php echo L_CONFIG_META_KEYWORDS ?>&nbsp;:</label></p>
		<?php plxUtils::printInput('meta_keywords', plxUtils::strCheck($plxAdmin->aConf['meta_keywords'])); ?>
		<p class="field"><label for="id_default_lang"><?php echo L_CONFIG_BASE_DEFAULT_LANG ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('default_lang', plxUtils::getLangs(), $plxAdmin->aConf['default_lang']) ?>
		<p class="field"><label for="id_delta"><?php echo L_CONFIG_BASE_TIMEZONE ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('delta', $delta, $plxAdmin->aConf['delta']); ?>
		<p class="field"><label for="id_allow_com"><?php echo L_CONFIG_BASE_ALLOW_COMMENTS ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('allow_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['allow_com']); ?>
		<p class="field"><label for="id_mod_com"><?php echo L_CONFIG_BASE_MODERATE_COMMENTS ?>&nbsp;:</label></p>
		<?php plxUtils::printSelect('mod_com',array('1'=>L_YES,'0'=>L_NO), $plxAdmin->aConf['mod_com']); ?>
	</fieldset>
	<?php eval($plxAdmin->plxPlugins->callHook('AdminSettingsBase')) # Hook Plugins ?>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" value="<?php echo L_CONFIG_BASE_UPDATE ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminSettingsBaseFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>