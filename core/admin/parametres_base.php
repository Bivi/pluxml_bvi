<?php

/**
 * Edition des paramètres de base
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

# On récupère la liste des éditeurs de texte
$editors[''] = 'aucun';
$editors['plxtoolbar'] = 'plxToolbar';
$folders = plxGlob::getInstance(PLX_ROOT.'addons', true);
if($list = $folders->query("/editor.[a-z0-9-_\.\(\)]+/i")) {
	foreach($list as $k=>$v) {
		$name = explode('.',$v);
		$editors[$name[1]]=$name[1];
	}
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Edition des param&egrave;tres de PluXml</h2>

<form action="parametres_base.php" method="post" id="change-cf-file">
	<fieldset class="withlabel">
		<legend>Configuration de base :</legend>	
		<p class="field"><label>Titre du site&nbsp;:</label></p>
		<?php plxUtils::printInput('title', plxUtils::strCheck($plxAdmin->aConf['title'])); ?>
		<p class="field"><label>Sous-titre/description du site&nbsp;:</label></p>
		<?php plxUtils::printInput('description', plxUtils::strCheck($plxAdmin->aConf['description'])); ?>
		<p class="field">
			<label>Racine du site (ex : http://pluxml.org/pluxml/)&nbsp;:</label>
			<a class="help" title="Ne pas oublier le slash &agrave; la fin">&nbsp;</a>
		</p>
		<?php plxUtils::printInput('racine', $plxAdmin->racine);?>
		<p class="field"><label>Fuseau horaire&nbsp;:</label></p>
		<?php plxUtils::printSelect('delta', $delta, $plxAdmin->aConf['delta']); ?>
		
		<p class="field"><label>Site priv&eacute;&nbsp;:</label></p>
		<?php plxUtils::printSelect('private_site',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['private_site']); ?>
		<p class="field"><label>Autoriser les commentaires&nbsp;:</label></p>
		<?php plxUtils::printSelect('allow_com',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['allow_com']); ?>
		<p class="field"><label>Mod&eacute;rer les commentaires &agrave; la cr&eacute;ation&nbsp;:</label></p>
		<?php plxUtils::printSelect('mod_com',array('1'=>'Oui','0'=>'Non'), $plxAdmin->aConf['mod_com']); ?>
		<p class="field">
			<label>&Eacute;diteur de texte&nbsp;:</label>
		</p>
		<?php plxUtils::printSelect('editor', $editors, $plxAdmin->aConf['editor']); ?>
	</fieldset>
	<p class="center"><input type="submit" value="Modifier la configuration de base" /></p>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>