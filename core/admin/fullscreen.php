<?php

/**
 * Editeur plein écran (plxToolbar)
 *
 * @package PLX
 * @author  Stephane F
 **/
 
include_once dirname(__FILE__)."/../vendor/markdown.php";

include(dirname(__FILE__).'/prepend.php');

# Initialisation de la vue (chapo ou content)
$view = !empty($_GET['v'])?$_GET['v']:'';
$origine = !empty($_GET['o'])?$_GET['o']:'';

if(!empty($_POST)) # Prévisualisation
	$content =  trim($_POST[$view]);
else
	$content = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
	<link rel="stylesheet" type="text/css" href="admin.css" media="screen" />
	<script type="text/javascript" src="../lib/functions.js"></script>
	<?php if(method_exists($plxAdmin->editor, 'addHeader')) $plxAdmin->editor->addHeader(); ?>
	<script type="text/javascript">
	function updater() {
		window.opener.document.getElementsByName('<?php echo $view ?>')['0'].value = document.getElementsByName('<?php echo $view ?>')['0'].value;
		window.close();
		return false;
	}
	function load() {
		document.getElementsByName('<?php echo $view ?>')['0'].value = window.opener.document.getElementsByName('<?php echo $view ?>')['0'].value;
	}
	</script>
</head>

<body class="popup" <?php if(empty($_POST['preview'])) echo 'onload="load()"' ?>>

<?php # On a un aperçu
if(isset($_POST['preview']) AND $origine == 'article') {
	# On remplace les chemins des images et documents (pas au même niveau)
  $_content = Markdown($content);
	$_content = str_replace('src="'.$plxAdmin->aConf['images'],'src="'.PLX_ROOT.$plxAdmin->aConf['images'],$_content);
	$_content = str_replace('href="'.$plxAdmin->aConf['documents'],'href="'.PLX_ROOT.$plxAdmin->aConf['documents'],$_content);
	$_content = str_replace('href="./?telechargement/','href="'.PLX_ROOT.'?telechargement/',$_content);
	echo '<blockquote id="preview">';
	echo "<h3>Pr&eacute;visualisation</h3>\n";
	echo '<div class="preview">'.$_content.'</div>';
	echo "</blockquote>\n";
}
?>
<p style="clear:both;"></p>

<form action="fullscreen.php?v=<?php echo $view ?>&amp;o=<?php echo $origine ?>" method="post" id="change-art-content">
	<fieldset>
		<?php $label = $view=='chapo' ? 'Chap&ocirc; (facultatif)' : 'Contenu'; ?>
		<p class="field"><label><?php echo $label ?>&nbsp;:</label></p>
		<?php plxUtils::printArea($view,plxUtils::strCheck($content),60,30); ?>
		<p style="clear:both;text-align:center">
			<?php if($origine == 'article') : ?>
				<input type="submit" name="preview" value="Aper&ccedil;u" />
			<?php endif; ?>
			<input type="submit" name="update" value="Mettre &agrave; jour" onclick="updater()" />
			<input type="submit" name="close" value="Fermer" onclick="window.close()" />
		</p>
	</fieldset>
</form>
<?php if(method_exists($plxAdmin->editor, 'addFooter')) $plxAdmin->editor->addFooter(); ?>
</body>
</html>