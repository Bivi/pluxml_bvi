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

define('PLX_ROOT', '../');
define('PLX_CORE', PLX_ROOT.'core/');
define('PLX_CONF', PLX_ROOT.'data/configuration/parametres.xml');
define('PLX_UPDATER', true);

# On inclut les librairies nécessaires
include(PLX_ROOT.'config.php');
include_once(PLX_CORE.'lib/class.plx.date.php');
include_once(PLX_CORE.'lib/class.plx.utils.php');
include_once(PLX_CORE.'lib/class.plx.msg.php');
include_once(PLX_CORE.'lib/class.plx.glob.php');
include_once(PLX_CORE.'lib/class.plx.record.php');
include_once(PLX_CORE.'lib/class.plx.motor.php');
include_once(PLX_CORE.'lib/class.plx.admin.php');
include_once(PLX_CORE.'lib/class.plx.encrypt.php');
include_once(PLX_ROOT.'update/class.plx.updater.php');

# Creation de l'objet principal et lancement du traitement
$plxUpdater = new plxUpdater();
foreach($plxUpdater->updateList as $num_version => $infos) {
	if($num_version!=$plxUpdater->newVersion) {
		$versions[$num_version] = 'PluXml '.$num_version;
	}
}
?>
<?php
header("Pragma: no-cache");
header("Cache: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header('Content-Type: text/html; charset='.PLX_CHARSET);
$_SESSION = array();
session_destroy();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>PluXml - Mise &agrave; jour</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET) ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo PLX_CORE ?>admin/admin.css" media="screen" />
</head>

<body>

<div id="main">
	<div id="header">
		<br />
		<h1>Mise &agrave; jour PluXml <?php echo $plxUpdater->newVersion ?></h1>
	</div>
	<div id="content">
	<h2>&nbsp;</h2>
	<?php if(empty($_POST['submit'])) : ?>
		<?php if($plxUpdater->oldVersion==$plxUpdater->newVersion) : ?>
			<p><strong>Votre PluXml est d&eacute;j&agrave; &agrave; jour.</strong></p>
			<p>Aucune mise &agrave; jour n'est disponible.</p>
			<p><a href="<?php echo PLX_ROOT; ?>" title="Revenir au site">Retour</a></p>
		<?php else: ?>
			<form action="index.php" method="post">
			<fieldset>
				<p><strong>Vous allez mettre &agrave; jour votre ancienne version de PluXml <?php echo $plxUpdater->oldVersion ?></strong></p>
				<?php if(empty($plxUpdater->oldVersion)) : ?>
					<p>Veuillez s&eacute;lectionner dans la liste ci-dessous votre ancienne version de PluXml &agrave; mettre &agrave; jour.</p>
					<p><?php plxUtils::printSelect('version',$versions,''); ?></p>
					<p>
						Si votre ancienne version n'est pas list&eacute;e ici, c'est qu'il n'existe pas de proc&eacute;dure automatis&eacute;e de mise &agrave; jour car votre version est trop vieille.<br />
						Nous vous sugg&eacute;rons de t&eacute;l&eacute;charger la derni&egrave;re version de <a href="http://pluxml.org">PluXml</a> et de faire une nouvelle installation.
					</p>
				<?php endif; ?>
				<br />
				<p class="msg">Attention, avant de d&eacute;marrer la mise &agrave; jour, n'oubliez pas de faire une sauvegarde de vos donn&eacute;es en faisant une copie du dossier "data"</p>
				<p style="text-align:center"><input type="submit" name="submit" value="D&eacute;marrer la mise &agrave; jour" /></p>
			</fieldset>
			</form>
		<?php endif; ?>
	<?php else: ?>
	<?php
		$version = isset($_POST['version']) ? $_POST['version'] : '';
		$plxUpdater->start($version);
	?>
		<p><a href="<?php echo PLX_ROOT; ?>" title="Revenir au site">Retour</a></p>
	<?php endif; ?>
	</div>
</div>

</body>
</html>