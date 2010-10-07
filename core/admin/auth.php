<?php

/**
 * Page d'authentification
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

# Variable pour retrouver la page d'authentification
define('PLX_AUTHPAGE', true);

include(dirname(__FILE__).'/prepend.php');

# Initialisation variable erreur
$error = '';

# Nettoyage et control de $_GET['p']
if(isset($_GET['p']) AND !empty($_GET['p'])) {
	$p = parse_url(urldecode($_GET['p']));
	$redirect = PLX_ROOT.'core/admin/'.basename($p['path']);
	if(!file_exists($redirect)) {
		$_GET['p']=$plxAdmin->aConf['racine'].'core/admin/';
	} 
}

# Déconnexion
if(!empty($_GET['d'])) {
	$_SESSION = array();
	session_destroy();
	$msg = 'Vous avez correctement &eacute;t&eacute; d&eacute;connect&eacute;';
}
# Authentification
if(!empty($_POST['login']) AND !empty($_POST['password'])) {
	$connected = false;
	foreach($plxAdmin->aUsers as $userid => $user) {
		if ($_POST['login']==$user['login'] AND md5($_POST['password'])==$user['password'] AND $user['active'] AND !$user['delete']) {
			$_SESSION['user'] = $userid;
			$_SESSION['profil'] = $user['profil'];
			$_SESSION['hash'] = plxUtils::charAleatoire(10);
			$connected = true;
		}
	}
	if($connected) {
		# On redirige
		if(!empty($_GET['p']) AND basename(urldecode($_GET['p'])) != '' AND basename(urldecode($_GET['p'])) != 'admin')
			header('Location: '.$plxAdmin->aConf['racine'].'core/admin/'.basename(urldecode($_GET['p'])));
		else
			header('Location: '.$plxAdmin->aConf['racine'].'core/admin/');
		exit;
	} else {
		$msg = 'Login et/ou mot de passe incorrect';
		$error = 'error';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>PluXml - Page d'authentification</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
<link rel="stylesheet" type="text/css" href="admin.css" media="screen" />
</head>

<body id="auth">
<form action="auth.php<?php echo !empty($_GET['p'])?'?p='.urldecode($_GET['p']):'' ?>" method="post">
	<fieldset>
		<legend>Connexion &agrave; la zone d'administration :</legend>
		<?php (!empty($msg))?plxUtils::showMsg($msg, $error):''; ?>
		<label>Login de connexion&nbsp;:</label>
		<?php plxUtils::printInput('login', (!empty($_POST['login']))?plxUtils::strCheck($_POST['login']):'', 'text', '18-255');?><br />
		<label>Mot de passe&nbsp;:</label>
		<?php plxUtils::printInput('password', '', 'password','18-255');?><br />
		<input type="submit" value="Valider" />
	</fieldset>
</form>

<p class="auth_return"><a href="<?php echo PLX_ROOT; ?>">Retour au site</a> | G&eacute;n&eacute;r&eacute; par <a href="http://pluxml.org">PluXml</a></p>

</body>
</html>