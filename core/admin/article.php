<?php

/**
 * Edition d'un article
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include_once dirname(__FILE__)."/../vendor/markdown.php";

include(dirname(__FILE__).'/prepend.php');

# Formulaire validé
if(!empty($_POST)) { # Création, mise à jour, suppression ou aperçu
	if(trim($_POST['title']) == '') $_POST['title'] = 'Nouvel article'; # Si titre vide ;)
	# Suppression d'un article
	if(isset($_POST['delete'])) {
		$plxAdmin->delArticle($_POST['artId']);	
		header('Location: index.php');
		exit;
	}
	# Mode création ou maj
	if(isset($_POST['update']) OR isset($_POST['draft'])) {
		
		if(isset($_POST['draft'])) $_POST['catId'][] = 'draft';
	
		if(plxDate::checkDate($_POST['day'],$_POST['month'],$_POST['year'],$_POST['time'])) { # Vérifie la date
			$plxAdmin->editArticle($_POST,$_POST['artId']);
			header('Location: article.php?a='.$_POST['artId']);
			exit;
		}
		# Vérification invalide
		$_GET['msg'] = 'Date de publication invalide.';
	}
	# Ajout d'une catégorie
	if(isset($_POST['new_category'])) {
		# Ajout de la nouvelle catégorie
		$plxAdmin->editCategories($_POST);
		# On recharge la nouvelle liste
		$plxAdmin->getCategories(PLX_ROOT.$plxAdmin->aConf['categories']);
	}
	# Alimentation des variables
	$artId = $_POST['artId'];
	$title = trim($_POST['title']);
	$author = $_POST['author'];
	$catId = isset($_POST['catId'])?$_POST['catId']:'';
	$date['day'] = $_POST['day'];
	$date['month'] = $_POST['month'];
	$date['year'] = $_POST['year'];
	$date['time'] = $_POST['time'];
	$chapo = trim($_POST['chapo']);
	$content =  trim($_POST['content']);
	$tags = trim($_POST['tags']);
	$url = $_POST['url'];
	$allow_com = $_POST['allow_com'];
	$template = $_POST['template'];
} elseif(!empty($_GET['a'])) { # On n'a rien validé, c'est pour l'édition d'un article
	# On va rechercher notre article
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.+).xml$/','','sort',0,1)) == false) { # Article inexistant
		plxMsg::Error('L\'article demand&eacute; n\'existe pas ou n\'existe plus !');
		header('Location: index.php');
		exit;
	}
	# On parse et alimente nos variables
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	$title = trim($result['title']);
	$chapo = trim($result['chapo']);
	$content =  trim($result['content']);
	$tags =  trim($result['tags']);	
	$author = $result['author'];
	$url = $result['url'];	
	$date = plxDate::dateIso2Admin($result['date']);
	$catId = explode(',', $result['categorie']);
	$artId = $result['numero'];
	$allow_com = $result['allow_com'];
	$template = $result['template'];
	
	if($author!=$_SESSION['user'] AND $_SESSION['profil']==PROFIL_WRITER) {
		plxMsg::Error('Vous n\'avez pas les droits pour acc&eacute;der &agrave cet article !');
		header('Location: index.php');
		exit;
	}
	
} else { # On a rien validé, c'est pour la création d'un article
	$title = 'Nouvel article';
	$chapo = $url = '';
	$content = '';
	$tags = '';
	$author = $_SESSION['user'];
	$date = array ('year' => @date('Y'),'month' => @date('m'),'day' => @date('d'),'time' => @date('H:i'));
	$catId = array();
	$artId = '0000';
	$allow_com = $plxAdmin->aConf['allow_com'];
	$template = 'article.php';
}

# On inclut le header
include(dirname(__FILE__).'/top.php');

# On construit la liste des utilisateurs
foreach($plxAdmin->aUsers as $userid => $user) {
	if($user['active'] AND !$user['delete'] ) {
		if($user['profil']==PROFIL_ADMIN)
			$users['Administrateurs'][$userid] = plxUtils::strCheck($user['name']);
		elseif($user['profil']==PROFIL_MODERATOR)
			$users['R&eacute;dacteurs avanc&eacute;s'][$userid] = plxUtils::strCheck($user['name']);
		else
			$users['R&eacute;dacteurs'][$userid] = plxUtils::strCheck($user['name']);
	}
}

# On récupère les templates des articles
$files = plxGlob::getInstance(PLX_ROOT.'themes/'.$plxAdmin->aConf['style']);
if ($array = $files->query('/article(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

$cat_id='000';
?>

<p><a href="./">&laquo; Retour &agrave; la liste des articles</a></p>

<h2><?php echo (empty($_GET['a']))?'Nouvel article':'Modification d\'un article'; ?></h2>

<?php # On a un aperçu
if(isset($_POST['preview'])) {

	# On remplace les chemins relatifs en chemin absolus
	$_chapo = plxUtils::rel2abs($plxAdmin->aConf['racine'], $chapo);
	$_content = plxUtils::rel2abs($plxAdmin->aConf['racine'], $content);
	echo '<blockquote id="preview">';
	echo "<h3>Pr&eacute;visualisation : ".plxUtils::strCheck($title)."</h3>\n";
	echo '<div class="preview">'.Markdown($_chapo).'</div><div class="preview">'.Markdown($_content).'</div>';
	echo "</blockquote>\n";
}
?>
<p style="clear:both;"></p>

<form action="article.php" method="post" id="change-art-content">
	<div style="float:left;width:590px">
		<fieldset>
			<?php plxUtils::printInput('artId',$artId,'hidden'); ?>
			<p class="field"><label>Titre&nbsp;:</label>
			<?php plxUtils::printInput('title',plxUtils::strCheck($title),'text','50-255'); ?>
			</p>
			<p class="field"><label>Auteur&nbsp;:&nbsp;</label>
			<?php 
				if($_SESSION['profil'] < PROFIL_WRITER)
					plxUtils::printSelect('author', $users, $author);
				else {
					echo '<input type="hidden" name="author" value="'.$author.'" />';
					echo '<strong>'.plxUtils::strCheck($plxAdmin->aUsers[$author]['name']).'</strong>';
				}
			?>
			</p>
			<p class="field"><label>Chap&ocirc; (facultatif)&nbsp;:</label></p>
			<?php plxUtils::printArea('chapo',plxUtils::strCheck($chapo),95,8); ?>
			<p class="field"><label>Contenu&nbsp;:</label></p>
			<?php plxUtils::printArea('content',plxUtils::strCheck($content),95,20); ?>
		</fieldset>
	</div>
	<div style="float:right;width:270px">
		<p class="head">&Eacute;tat&nbsp;:&nbsp;<strong> 
		<?php 
			if(is_array($catId) AND sizeof($catId)>0) echo (in_array('draft', $catId)) ? 'Brouillon' : 'Publi&eacute;';
			else echo "Brouillon";
		?></strong>
		</p>	
		<fieldset>
			<p class="field"><label>Date de publication&nbsp;:</label></p> 
			<p>
				<?php plxUtils::printInput('day',$date['day'],'text','2-2',false,'fld1'); ?>
				<?php plxUtils::printInput('month',$date['month'],'text','2-2',false,'fld1'); ?>
				<?php plxUtils::printInput('year',$date['year'],'text','2-4',false,'fld2'); ?>
				<?php plxUtils::printInput('time',$date['time'],'text','2-5',false,'fld2'); ?>
				<a href="javascript:void(0)" onclick="dateNow(); return false;" title="maintenant"><img src="img/date.png" alt="" /></a>
			</p>
			<p class="field"><label>Emplacements&nbsp;:</label></p>
			<p>
				<?php
					$selected = (is_array($catId) AND in_array('home', $catId)) ? ' checked="checked"' : '';
					echo '<input type="checkbox" id="cat_home" name="catId[]"'.$selected.' value="home" /><label for="cat_home">&nbsp;Page d\'accueil</label><br />';
					foreach($plxAdmin->aCats as $cat_id => $cat_name) {
						$selected = (is_array($catId) AND in_array($cat_id, $catId)) ? ' checked="checked"' : '';
						echo '<input type="checkbox" id="cat_'.$cat_id.'" name="catId[]"'.$selected.' value="'.$cat_id.'" /><label for="cat_'.$cat_id.'">&nbsp;'.$cat_name['name'].'</label><br />';
					}
				?>
			</p>
			<?php if($_SESSION['profil'] < PROFIL_WRITER) : ?>
			<p class="field"><label>Nouvelle cat&eacute;gorie&nbsp;:</label></p>
			<p>
				<?php 
				plxUtils::printInput('new_catid',str_pad($cat_id+1, 3, "0", STR_PAD_LEFT),'hidden'); 
				plxUtils::printInput('new_catname','','text','17-50'); 
				?>
				<input type="submit" name="new_category" value="Ajouter" />
			</p>
			<?php endif; ?>
			<p class="field"><label>Mots cl&eacute;s <a class="help" title="S&eacute;parer les mots cl&eacute;s par une virgule">&nbsp;</a>&nbsp;:</label></p>
			<p>
				<?php plxUtils::printInput('tags',$tags,'text','25-255'); ?>
			</p>			
			<p class="field"><label>Autoriser les commentaires&nbsp;:</label></p>
			<p>
				<?php plxUtils::printSelect('allow_com',array('1'=>'Oui','0'=>'Non'),$allow_com); ?>
			</p>			
			<p class="field"><label>Url <a class="help" title="l'URL se remplit automatiquement &agrave; la cr&eacute;ation">&nbsp;</a>&nbsp;:</label></p>
			<p><?php plxUtils::printInput('url',$url,'text','27-255'); ?></p>
			<p class="field"><label>Template&nbsp;:</label></p>
			<?php plxUtils::printSelect('template', $aTemplates, $template); ?>
			<?php if($artId != '0000') : ?>
			<p>&nbsp;</p>
			<ul class="opts">
				<li>&nbsp;<a href="commentaires_online.php?a=<?php echo $artId ?>&amp;page=1" title="G&eacute;rer les commentaires de cet article">G&eacute;rer les commentaires</a></li>
				<li>&nbsp;<a href="commentaire_new.php?a=<?php echo $artId ?>" title="R&eacute;diger un commentaire sur cet article">R&eacute;diger un commentaire</a></li>
			</ul>
			<?php endif; ?>
		</fieldset>
	</div>
	<div style="clear:both">
		<p class="center">
			<?php if($artId != '0000') : ?>
				<input class="bgred" type="submit" name="delete" value="Supprimer" onclick="Check=confirm('Supprimer cet article ?');if(Check==false) return false;"/>
				&nbsp;&nbsp;&nbsp;&nbsp;
			<?php endif; ?>			
			<input type="submit" name="preview" value="Aper&ccedil;u"/>
			<?php
				if(is_array($catId) AND sizeof($catId)>0 AND !in_array('draft', $catId)) {
					echo '<input type="submit" name="draft" value="Mettre hors ligne"/>';
					echo '<input type="submit" name="update" value="Enregistrer"/>';
				} else {
					echo '<input type="submit" name="draft" value="Enregistrer brouillon"/>';
					echo '<input type="submit" name="update" value="Publier"/>';
				}
			?>
		</p>
	</div>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>