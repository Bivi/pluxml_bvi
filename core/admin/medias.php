<?php

/**
 * Gestion des images et documents
 *
 * @package PLX
 * @author  Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

/**
 * Méthode qui affiche les dossiers du dossier en cours
 *
 * @return	stdout
 * @author	Stephane F.
 **/
function displayFolders($dir, $plxMedias, $view) {

	if(!empty($plxMedias->aContent['folders'])) {
		# Initialisation variable de boucles
		$i = 1;
		foreach ($plxMedias->aContent['folders'] as $k => $v) {
			$i++;
			echo '<div class="line-'.($i%2).'" style="padding:5px;">';
				if($v != '../') {
					echo '<span style="float:right;">';
					echo '<a href="medias.php?hash='.$_SESSION['hash'].'&amp;deldir='.urlencode($dir.$v).'&amp;dir='.urlencode($dir).($view!=''?'&amp;v='.$view:'').'" title="Supprimer le dossier" onclick="Check=confirm(\'Supprimer ce dossier et son contenu ?\');if(Check==false) return false;">';
					echo '<img src="img/delete.gif" alt="Supprimer" /></a>';
					echo '</span>';
				}
				echo '<img src="img/categorie.png" width="12" height="12" alt="" /> &nbsp;';
				echo '<a href="medias.php?dir='.urlencode($k).($view!=''?'&amp;v='.$view:'').'" title="Visualiser le contenu du dossier">'.$v.'</a><br />';
			echo '</div>';
		}
		
	} else {
		echo '<p style="padding:10px;">Aucun dossier</p>';
	}
}

/**
 * Méthode qui affiche les images du dossier en cours
 *
 * @return	stdout
 * @author	Stephane F.
 **/
function displayImages($dir, $plxMedias, $view) {

	if(!empty($plxMedias->aContent['files'])) {
		foreach ($plxMedias->aContent['files'] as $k => $v) { 			
			echo '<div class="bloc_gal">';			
			# Affichage miniature
			echo '<p class="thumb">';
			if(file_exists($plxMedias->path.$dir.$v['name'].'.tb')) {
				echo '<a href="medias.php?file='.urlencode($v['name']).($view!=''?'&amp;v='.$view:'').'&amp;dir='.urlencode($dir).'">';
				echo '<img src="'.$plxMedias->path.$dir.$v['name'].'.tb'.'" alt="" title="'.$v['name'].'"/><br />';
				echo '</a>';
			}
			$filename = plxUtils::strCut($v['name'], 35,'','[...]');
			if(substr($filename, -5)=='[...]') $filename.=strrchr($v['name'],'.');
			echo '<span>'.$filename.'</span><br />';
			echo '</p>';		
			# Affichage des différents liens
			echo '<p class="thumb_link">';
			# Lien pour recréer la miniature
			echo '<a href="medias.php?mini='.urlencode($v['name']).($view!=''?'&amp;v='.$view:'').'&amp;dir='.urlencode($dir).'&amp;hash='.$_SESSION['hash'].'">';
			echo '<img src="img/photos.png" alt="Recr&eacute;er miniature" title="Recr&eacute;er la miniature" /></a> &nbsp; ';
			# Lien pour voir l'image
			echo '<a href="medias.php?file='.urlencode($v['name']).($view!=''?'&amp;v='.$view:'').'&amp;dir='.urlencode($dir).'">';
			echo '<img src="img/eye.png" alt="Voir" title="Voir l\'image" /></a> &nbsp; ';
			# Liens pour insérer dans l'article l'image ou la miniature
			if($view != '') {
				echo '<a href="javascript:void(0)" title="Ajouter la miniature " onclick="opener.insImg(\''.$view.'\', \''.str_replace('../', '', $plxMedias->path.$dir.$v['name'].'.tb').'\')">';
				echo '<img src="img/square_mini.png" alt="Insérer miniature" /></a>&nbsp;';
				echo '<a href="javascript:void(0)" title="Ajouter l\'image" onclick="opener.insImg(\''.$view.'\', \''.str_replace('../', '', $plxMedias->path.$dir.$v['name']).'\')">';
				echo '<img src="img/square.png" alt="Insérer image" /></a>&nbsp;';
			}
			# Déroulant pour déplacer l'image
			echo "\n".'<select name="files['.$dir.$v['name'].']">'.$plxMedias->getDirs($dir)."\n</select>\n&nbsp;";
			# Lien pour supprimer l'image
			echo '<a href="medias.php?delfile='.$dir.$v['name'].'&amp;dir='.urlencode($dir).'&amp;hash='.$_SESSION['hash'].($view!=''?'&amp;v='.$view:'').'" title="Supprimer l\'image" onclick="Check=confirm(\'Supprimer cette image ?\');if(Check==false) return false;"><img src="img/delete.gif" alt="Supprimer" /></a>';
			echo '</p>';					
			echo '</div>'."\n";
		}		
	} else {
		echo '<p style="padding:10px;">Aucun fichier</p>';		
	}
}

/**
 * Méthode qui affiche les documents du dossier en cours
 *
 * @return	stdout
 * @author	Stephane F.
 **/
function displayDocuments($dir, $plxMedias, $view) {

	if(!empty($plxMedias->aContent['files'])) {
		echo '<table class="table">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="width:75%">Nom du fichier</th>';
		echo '<th style="width:25%">Action</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		# Initialisation variable de boucles
		$i = 1;
		foreach ($plxMedias->aContent['files'] as $k => $v) { 
			$i++;
			echo '<tr class="line-'.($i%2).'">';
			echo '<td class="tc1">&nbsp;'.plxUtils::strCut($v['name'],100).'</td>';
			echo '<td class="tc1">&nbsp;';
			if ($view!='') {	
				# Icone pour lien crypté
				echo '<a href="javascript:void(0)" title="Ajouter le lien pour t&eacute;l&eacute;charger le fichier" onclick="opener.insDoc(\''.$view.'\', \''.str_replace('../', '', plxEncrypt::encryptId($plxMedias->path.$dir.$v['name'])).'\', \''.$v['name'].'\', \'1\')">';
				echo '<img src="img/lock_go.png" alt="Ajouter fichier" /></a>&nbsp;';
				# Icone pour lien en clair
				echo '<a href="javascript:void(0)" title="Ajouter le lien du fichier" onclick="opener.insDoc(\''.$view.'\', \''.str_replace('../', '', $plxMedias->path.$dir.$v['name']).'\', \''.$v['name'].'\', \'0\')">';
				echo '<img src="img/square.png" alt="Ajouter fichier" /></a>&nbsp;';
			}
			echo "\n".'<select name="files['.$dir.$v['name'].']">'.$plxMedias->getDirs($dir).'</select>&nbsp;';
			echo '<a href="medias.php?delfile='.$dir.$v['name'].'&amp;dir='.urlencode($dir).'&amp;hash='.$_SESSION['hash'].($view!=''?'&amp;v='.$view:'').'" title="Supprimer le fichier" onclick="Check=confirm(\'Supprimer ce fichier ?\');if(Check==false) return false;"><img src="img/delete.gif" alt="Supprimer" /></a>';
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	} else {
		echo '<p style="padding:10px;">Aucun fichier</p>';		
	}
}
# type de tri d'affichage
$_SESSION['images_order'] = !empty($_POST['tri'])?$_POST['tri']:(!empty($_SESSION['images_order'])?$_SESSION['images_order']:'alpha');

# Recherche du type de medias à afficher via la session
$medias = (!empty($_SESSION['medias'])?$_SESSION['medias']:'images');
if(!empty($_POST['medias'])) {
	$medias = $_POST['sel_medias'];
	$_SESSION['medias'] = $medias;
	$_GET['dir'] = '';
}

# Recherche du repertoire à afficher
$dir = '';
if(!empty($_GET['dir']) AND !strstr($_GET['dir'], '../')) {
	$dir = urldecode($_GET['dir']);
	$dir = (substr($dir, -1)!='/' AND $dir!='')?$dir.'/':$dir;
}
# Initialisation de la vue (chapo ou content)
$view = !empty($_GET['v'])?$_GET['v']:'';

# Nouvel objet de type plxMedias
$plxMedias = new plxMedias(PLX_ROOT.$plxAdmin->aConf[$medias]);
# On définit la taille des miniatures;
$plxMedias->miniWidth = $plxAdmin->aConf['miniatures_l'];
$plxMedias->miniHeight = $plxAdmin->aConf['miniatures_h']; 
	
# Création d'un dossier
if(!empty($_POST['newdir']) AND !strstr($_GET['dir'], '../') AND trim($_POST['newdir']) != '') {
	$plxMedias->newDir($dir, $_POST['newdir']);
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;
} 
# Recréation de la miniature
elseif(!empty($_GET['mini']) AND !empty($_GET['hash']) AND $_GET['hash'] == $_SESSION['hash']) {
	$plxMedias->makeThumb($_GET['mini']);
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;	
}
# Suppression d'un dossier et de tout son contenu
elseif(!empty($_GET['deldir']) AND !strstr($_GET['dir'], '../') AND !empty($_GET['hash']) AND $_GET['hash'] == $_SESSION['hash']) {
	$plxMedias->delDir(urldecode($_GET['deldir']));
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;
}
# Suppression d'un fichier
elseif(!empty($_GET['delfile']) AND !strstr($_GET['dir'], '../') AND !empty($_GET['hash']) AND $_GET['hash'] == $_SESSION['hash']) {
	$plxMedias->delFile(urldecode($_GET['delfile']));
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;
}
# Envoi d'un fichier
elseif(!empty($_POST['send']) AND !empty($_FILES)) {
	$plxMedias->upload($dir, $_FILES['file'], $medias);
    header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
    exit;
}
# Déplacement d'un ou plusieurs fichiers
elseif(!empty($_POST['change'])) {
	$plxMedias->moveFile($dir, $_POST["files"]);
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;	
}
# Recréer les vignettes
elseif(!empty($_POST['thumbs'])) {
	$plxMedias->makeThumbs($dir);
	header('Location: medias.php?dir='.urlencode($dir).($view!=''?'&v='.$view:''));
	exit;	
}

# Recuperation des fichiers du dossier en cours
$plxMedias->getDirContent($dir);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title>Librairie - <?php echo $plxAdmin->aConf['title']; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo strtolower(PLX_CHARSET); ?>" />
<link rel="stylesheet" type="text/css" href="admin.css" media="screen" />
</head>

<body class="popup">
	
	<?php if(!empty($_GET['file']) AND file_exists($plxMedias->path.$dir.urldecode($_GET['file']))) : # Affichage de l'image demandée ?>
	
		<p><a href="medias.php?dir=<?php echo $dir; ?><?php echo $view!=''?'&amp;v='.$view:'' ?>" class="backupload">retour</a><br /><br /></p>
		<div style="text-align:center;">
			<img src="<?php echo $plxMedias->path.$dir.urldecode($_GET['file']) ?>" alt="" />
		</div>
	
	<?php else : # Affichage de la galerie ?>
    
		<h2>Gestion des m&eacute;dias</h2>	
		
		<?php plxMsg::Display() ?>
		
		<form action="medias.php?dir=<?php echo urlencode($dir); ?><?php echo $view!=''?'&amp;v='.$view:'' ?>" method="post">
			<div style="padding: 3px 0 0 10px">
				Librairie : <?php plxUtils::printSelect('sel_medias', array('images' => 'Images', 'documents' => 'Documents'), $medias); ?>
				<input class="button" type="submit" name="medias" value="Ok" />
			</div>
		</form>	
		
		<form class="cssform" enctype="multipart/form-data" action="medias.php?dir=<?php echo urlencode($dir); ?><?php echo $view!=''?'&amp;v='.$view:'' ?>" method="post">
			<fieldset style="float:left">
				<div style="border:1px solid #333333;">
					<div style="height:170px;overflow:auto;">
						<?php displayFolders($dir, $plxMedias, $view); # Affichage de la liste des dossiers ?>
					</div>
					<?php # Affichage creation nouveau dossier ?>        
					<div style="background-color:#EDE9E3;padding:2px;border-top:1px solid #333333;">
						<input type="text" name="newdir" size="15" /><input type="submit" name="create" value="Cr&eacute;er un dossier" />
					</div>
				</div>
			</fieldset>
			<?php # Affichage de la zone d'upload de fichier ?>
        	<fieldset>
				<?php
					if($medias=='images') echo '<legend>Envoyer une image (gif, jpg, png)</legend>';
					else echo '<legend>Envoyer un fichier</legend>';
				?>
				<p>
					<input name="file[0]" size="17" type="file" />&nbsp;Fichier 1<br />
					<input name="file[1]" size="17" type="file" />&nbsp;Fichier 2<br />
					<input name="file[2]" size="17" type="file" />&nbsp;Fichier 3<br />
					<input name="file[3]" size="17" type="file" />&nbsp;Fichier 4<br />
					<input name="file[4]" size="17" type="file" />&nbsp;Fichier 5<br /><br />					
					<input type="submit" name="send" value="Envoyer" />&nbsp;Taille maxi des fichiers : <?php echo $plxMedias->maxUpload['display'] ?>
				</p>
        	</fieldset>
		</form>

		<?php # Affichage des fichiers ?>
		<?php $nbfiles = isset($plxMedias->aContent['files'])?sizeof($plxMedias->aContent['files']):0; ?>
		<form action="medias.php?dir=<?php echo urlencode($dir); ?><?php echo $view!=''?'&amp;v='.$view:'' ?>" method="post">
			<div class="h2">
				<div style="float:left; padding-top:5px"><?php echo $nbfiles ?> fichier<?php echo ($nbfiles>1?'s':'') ?> dans <?php echo './'.$dir; ?></div>
				<div style="float:right">
					Tri : <?php echo plxUtils::printSelect('tri', array('alpha' => 'alphab&eacute;tique', 'date' => 'date d&eacute;croissante'), $_SESSION['images_order']) ?>
					<input type="submit" name="order" value="Ok" />
				</div>
			</div>
		</form>
				
		<div style="clear:both"></div>
		<form action="medias.php?dir=<?php echo urlencode($dir); ?><?php echo $view!=''?'&amp;v='.$view:'' ?>" method="post">
		<?php # Affichage des fichiers ?>
		<?php $nbfiles = isset($plxMedias->aContent['files']) ? sizeof($plxMedias->aContent['files']) : 0; ?>
        <div style="overflow:auto;">
			<?php
				if(!empty($plxMedias->aContent['files'])) {
					# Tri par date décroissante
					if($_SESSION['images_order'] == 'date') usort($plxMedias->aContent['files'], create_function('$a, $b', 'return strcmp($b["date"], $a["date"]);'));
					# Tri par ordre alphabétique croissant
					else usort($plxMedias->aContent['files'], create_function('$a, $b', 'return strcmp($a["name"], $b["name"]);'));
				}
				switch($medias) {
					case 'images':
						displayImages($dir, $plxMedias, $view); # Affichage de la liste des images
						break;
					case 'documents':
						displayDocuments($dir, $plxMedias, $view); # Affichage de la liste des documents
						break;
					default:
						break;
				}
			?>
		</div>
		<p style="clear:both;text-align:center">
			<input type="submit" name="change" value="Appliquer les changements" />
			<?php if($medias == 'images') : ?>
				<input type="submit" name="thumbs" value="Recr&eacute;er les miniatures du dossier en cours" />
			<?php endif; ?>
		</p>
		</form>
	<?php endif; ?>
</body>
</html>