<?php

/**
 * Edition du code source d'une page statique
 *
 * @package PLX
 * @author	Stephane F. et Florent MONTHEL
 **/
 
include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite la page statique
if(!empty($_POST) AND isset($plxAdmin->aStats[ $_POST['id'] ])) {
	$plxAdmin->editFileStatique($_POST);
	header('Location: statique.php?p='.$_POST['id']);
	exit;
} elseif(!empty($_GET['p'])) { # On affiche le contenu de la page
	$id = $_GET['p'];
	if(!isset($plxAdmin->aStats[ $id ])) {
		plxMsg::Error('Cette page statique n\'existe pas ou n\'existe plus !');
		header('Location: statiques.php');
		exit;
	}
	# On récupère le contenu
	$content = trim($plxAdmin->getFileStatique($id));
	$title = $plxAdmin->aStats[ $id ]['name'];
	$url = $plxAdmin->aStats[ $id ]['url'];
} else { # Sinon, on redirige
	header('Location: statiques.php');
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>
<p><a href="statiques.php">&laquo; Retour &agrave; la liste des pages statiques</a></p>

<h2>&Eacute;dition du code source de la page statique "<?php echo plxUtils::strCheck($title); ?>"</h2>

<p class="center"><a href="<?php echo PLX_ROOT; ?>?static<?php echo intval($id); ?>/<?php echo $url; ?>">Visualiser la page <?php echo plxUtils::strCheck($title); ?> sur le site</a></p>

<form action="statique.php" method="post" id="change-static-content">
	<fieldset>
		<?php plxUtils::printInput('id', $id, 'hidden');?>
		<p class="field"><label>Contenu&nbsp;:</label></p>
		<?php plxUtils::printArea('content', plxUtils::strCheck($content),140,30) ?>
    	<p class="center"><input type="submit" value="Enregistrer cette page statique"/></p>
	</fieldset>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>