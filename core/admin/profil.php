<?php

/**
 * Edition du profil utilisateur
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# On édite la configuration
if(!empty($_POST)) {
	$plxAdmin->editProfil($_POST);
	header('Location: profil.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');

$profil = $plxAdmin->aUsers[$_SESSION['user']];
?>

<h2>Edition de votre profil</h2>

<form action="profil.php" method="post" id="change-profil-file">
	<fieldset class="withlabel">
		<legend>Profil :</legend>
		<p class="field"><label>Login de connexion&nbsp;:</label>&nbsp;<strong><?php echo plxUtils::strCheck($profil['login']) ?></strong></p>
		<p class="field"><label>Nom d'utilisateur&nbsp;:</label></p>
		<?php plxUtils::printInput('name', plxUtils::strCheck($profil['name']), 'text', '20-255') ?>
		<p class="field"><label>Informations&nbsp;:</label></p>
		<?php plxUtils::printArea('infos',plxUtils::strCheck($profil['infos']),140,5); ?>
	</fieldset>
	<p class="center"><input type="submit" name="profil" value="Modifier votre profil" /></p>	
	<fieldset class="withlabel">
		<legend>Changement du mot de passe :</legend>
		<p class="field"><label>Mot de passe&nbsp;:</label></p>
		<?php plxUtils::printInput('password1', '', 'password', '20-255') ?>
		<p class="field"><label>Confirmation du mot de passe&nbsp;:</label></p>
		<?php plxUtils::printInput('password2', '', 'password', '20-255') ?>		
	</fieldset>
	<p class="center"><input type="submit" name="password" value="Changer votre mot de passe" /></p>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>