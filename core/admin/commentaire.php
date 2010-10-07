<?php

/**
 * Edition d'un commentaire
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MODERATOR);

# On édite, supprime ou valide notre commentaire
if(!empty($_POST) AND !empty($_POST['comId'])) {
	# On définit vers quel page il faut se rediriger
	$redirect = preg_match('/^_/',$_POST['comId'])?'commentaires_offline.php':'commentaires_online.php';
	# Suppression, on redirige
	if(isset($_POST['delete'])) {
		$plxAdmin->delCommentaire($_POST['comId']);
		header('Location: '.$redirect.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
		exit;
	}
	# Modération ou validation
	if(isset($_POST['mod'])) {
		$plxAdmin->editCommentaire($_POST,$_POST['comId']);
		$plxAdmin->modCommentaire($_POST['comId']);
		header('Location: ./commentaire.php?c='.$_POST['comId'].(!empty($_GET['a'])?'&a='.$_GET['a']:''));
		exit;
	}
	# Répondre au commentaire
	if(isset($_POST['answer'])) {
		header('Location: ./commentaire_new.php?c='.$_POST['comId']).(!empty($_GET['a'])?'&a='.$_GET['a']:'');
		exit;
	}
	# Edition
	$plxAdmin->editCommentaire($_POST,$_POST['comId']);
	header('Location: ./commentaire.php?c='.$_POST['comId'].(!empty($_GET['a'])?'&a='.$_GET['a']:''));
	exit;
}

# Variable de redirection
$redirect = preg_match('/^_/',$_GET['c'])?'commentaires_offline.php':'commentaires_online.php';

# On va récupérer les infos sur le commentaire
$plxAdmin->getCommentaires('/^'.$_GET['c'].'.xml$/',0,1);
if(!$plxAdmin->plxGlob_coms->count OR !$plxAdmin->plxRecord_coms->size) { # Commentaire inexistant
	# On redirige
	plxMsg::Error('Le commentaire demand&eacute; n\'existe pas ou n\'existe plus !');
	header('Location: '.$redirect.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}

# On va récupérer les infos sur l'article
$artId = $plxAdmin->plxRecord_coms->f('article');
# On va rechercher notre article
if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$artId.'.(.+).xml$/','','sort',0,1)) == false) {
	# On indique que le commentaire est attaché à aucun article
	$article = '<strong>aucun article</strong>';
	# Statut du commentaire
	$statut = '<strong>non visible (nous vous conseillons de supprimer ce commentaire)</strong>';
} else {
	$result = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# On génère notre lien
	$article = '<a href="article.php?a='.$result['numero'].'" title="Article attach&eacute; &agrave; ce commentaire">';
	$article .= plxUtils::strCheck($result['title']);
	$article .= '</a>';
	# Statut du commentaire
	if(preg_match('/^_/',$_GET['c']))
		$statut = '<strong>hors ligne</strong>';
	else
		$statut = '<a href="'.PLX_ROOT.'?article'.intval($plxAdmin->plxRecord_coms->f('article')).'/#c'.$plxAdmin->plxRecord_coms->f('numero').'" title="Visualiser ce commentaire en ligne">en ligne</a>';
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<?php if(!empty($_GET['a'])) : ?>
	<p><a href="./<?php echo $redirect.'?a='.$_GET['a']; ?>">&laquo; Retour &agrave; la liste des commentaires de cet article</a></p>
<?php else : ?>
	<p><a href="./<?php echo $redirect; ?>">&laquo; Retour &agrave; la liste des commentaires</a></p>
<?php endif; ?>

<h2>Edition d'un commentaire</h2>

<ul>
	<li>Auteur : <strong><?php echo $plxAdmin->plxRecord_coms->f('author'); ?></strong></li>
	<li>Type de commentaire : <strong><?php echo $plxAdmin->plxRecord_coms->f('type'); ?></strong></li>
	<li>Date : <?php echo plxDate::dateIsoToHum($plxAdmin->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?></li>
	<li>Ip : <?php echo $plxAdmin->plxRecord_coms->f('ip'); ?></li>
	<?php if($plxAdmin->plxRecord_coms->f('site') != '') : ?>
		<li>Site : <?php echo '<a href="'.$plxAdmin->plxRecord_coms->f('site').'">'.$plxAdmin->plxRecord_coms->f('site').'</a>'; ?></li>
	<?php endif; ?>
	<?php if($plxAdmin->plxRecord_coms->f('mail') != '') : ?>
		<li>E-mail : <?php echo '<a href="mailto:'.$plxAdmin->plxRecord_coms->f('mail').'">'.$plxAdmin->plxRecord_coms->f('mail').'</a>'; ?></li>
	<?php endif; ?>
	<li>Statut : <?php echo $statut; ?></li>
	<li>Article attach&eacute; : <?php echo $article; ?></li>
</ul>
<form action="commentaire.php<?php echo (!empty($_GET['a'])?'?a='.$_GET['a']:'') ?>" method="post" id="change-com-content">
	<fieldset>
		<?php plxUtils::printInput('comId',$_GET['c'],'hidden'); ?>
		<p class="field"><label>Commentaire&nbsp;:</label></p>
		<?php if($plxAdmin->plxRecord_coms->f('type') == 'admin') : ?>
			<?php plxUtils::printArea('content',plxUtils::strCheck($plxAdmin->plxRecord_coms->f('content')), 60, 7); ?>
		<?php else : ?>
			<?php plxUtils::printArea('content',$plxAdmin->plxRecord_coms->f('content'), 60, 7); ?>
		<?php endif; ?>
		<p class="center">
			<input class="bgred" type="submit" name="delete" value="Supprimer" onclick="Check=confirm('Supprimer ce commentaire ?');if(Check==false) return false;"/>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<?php if(preg_match('/^_/',$_GET['c'])) : ?>
				<input type="submit" name="mod" value="Valider la publication" /> 
			<?php else : ?>
				<input type="submit" name="mod" value="Mettre hors ligne" />
				<input type="submit" name="answer" value="R&eacute;pondre &agrave; ce commentaire" />
			<?php endif; ?>
			<input type="submit" name="update" value="Mettre &agrave; jour" /> 
		</p>
	</fieldset>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>