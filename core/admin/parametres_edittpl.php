<?php
/**
 * Edition des fichiers templates du thème en vigueur
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Initialisation
$tpl = !empty($_GET['tpl'])?$_GET['tpl']:'home.php';
if(isset($_POST['load'])) $tpl = $_POST['template'];
$style = !isset($_GET['mobile'])?$plxAdmin->aConf['style']:$plxAdmin->aConf['style_mobile'];
$filename = realpath(PLX_ROOT.'themes/'.$style.'/'.$tpl);
if(!preg_match('#^'.str_replace('\\', '/', realpath(PLX_ROOT.'themes/'.$style.'/').'#'), str_replace('\\', '/', $filename)))
	$filename = PLX_ROOT.'themes/'.$style.'/home.php';

# On teste l'existence du thème
if(empty($style) OR !is_dir(PLX_ROOT.'themes/'.$style)) {
	plxMsg::Error('Ce th&egrave;me n\'existe pas !');
	header('Location: parametres_affichage.php');
	exit;
}

# Traitement du formulaire: sauvegarde du template
if(isset($_POST['submit']) AND !empty($tpl) AND trim($_POST['content']) != '') {
	if(plxUtils::write($_POST['content'], $filename))
		plxMsg::Info("Fichier enregistr&eacute; avec succ&egrave;s");
	else
		plxMsg::Error("Erreur pendant l'enregistrement du fichier");
	header("Location: parametres_edittpl.php?tpl=".$tpl.(isset($_GET['mobile'])?'&mobile':''));
	exit;
} 

# On récupère les fichiers templates du thèmes
$files = plxGlob::getInstance(PLX_ROOT.'themes/'.$style);
if ($aTemplates = $files->query('/[a-z0-9-_]+.(php|css)$/')) {
	foreach($aTemplates as $k=>$v)
		$aTemplate[ $v ] = $v;
}

# On récupère le contenu du fichier template
$content = '';
if(file_exists($filename) AND filesize($filename) > 0) {
	if($f = fopen($filename, 'r')) {
		$content = fread($f, filesize($filename));
		fclose($f);
	}
}	

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Edition du th&egrave;me &laquo;<?php echo $style ?>&raquo;</h2>

<form action="parametres_edittpl.php<?php echo isset($_GET['mobile'])?'?mobile':'' ?>" method="post">
	<p class="field">
		<label>Choix du fichier &agrave; &eacute;diter :</label>
		<?php plxUtils::printSelect('template', $aTemplate, $tpl); ?> <input class="button" name="load" type="submit" value="Charger" />
	</p>
</form>

<form action="parametres_edittpl.php?tpl=<?php echo $tpl ?><?php echo isset($_GET['mobile']) ? '&amp;mobile' : '' ?>" method="post">
	<fieldset>
		<p class="field"><label>Contenu&nbsp;:</label></p>
		<?php plxUtils::printArea('content',plxUtils::strCheck($content),60,20); ?>
		<p class="center"><input name="submit" type="submit" value="Sauvegarder le fichier" /></p>
	</fieldset>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>