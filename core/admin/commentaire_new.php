<?php

/**
 * Création d'un commentaire
 *
 * @package PLX
 * @author	Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MODERATOR);

# On va checker le mode (répondre ou écrire)
if(!empty($_GET['c'])) { # Mode "answer"
	# On check que le commentaire existe et est "online"
	$plxAdmin->getCommentaires('/^'.$_GET['c'].'.xml$/',0,1);
	# Commentaire inexistant
	if(!$plxAdmin->plxGlob_coms->count OR !$plxAdmin->plxRecord_coms->size) {
		# On redirige
		plxMsg::Error('Le commentaire auquel vous tentez de r&eacute;pondre n\'existe pas ou n\'existe plus !');
		header('Location: commentaires_online.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
		exit;
	}
	# Commentaire offline
	if(preg_match('/^_/',$_GET['c'])) {
		# On redirige
		plxMsg::Error('Le commentaire est hors ligne, impossible d\'y r&eacute;pondre !');
		header('Location: commentaires_online.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
		exit;
	}
	# On va rechercher notre article
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$plxAdmin->plxRecord_coms->f('article').'.(.+).xml$/','','sort',0,1)) == false) { # Article inexistant
		plxMsg::Error('L\'article demand&eacute; n\'existe pas ou n\'existe plus, impossible d\'&eacute;crire un commentaire !');
		header('Location: index.php');
		exit;
	}
	# Variables de traitement
	$artId = $plxAdmin->plxRecord_coms->f('article');
	if(!empty($_GET['a'])) $get = 'c='.$_GET['c'].'&amp;a='.$_GET['a'];
	else $get = 'c='.$_GET['c'];
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# Variable du formulaire
	$content = '<a href="#c'.$plxAdmin->plxRecord_coms->f('numero').'">@'.$plxAdmin->plxRecord_coms->f('author')."</a> :\n";
	$article = '<a href="article.php?a='.$aArt['numero'].'" title="Article attach&eacute; &agrave; ce commentaire">';
	$article .= plxUtils::strCheck($aArt['title']);
	$article .= '</a>';
	# Ok, on récupère les commentaires de l'article
	$plxAdmin->getCommentaires('/^'.$artId.'.(.*).xml$/','rsort');
} elseif(!empty($_GET['a'])) { # Mode "new"
	# On check l'article si il existe bien
	if(($aFile = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.+).xml$/','','sort',0,1)) == false) {
		plxMsg::Error('L\'article demand&eacute; n\'existe pas ou n\'existe plus, impossible d\'&eacute;crire un commentaire !');
		header('Location: index.php');
		exit;
	}
	# Variables de traitement
	$artId = $_GET['a'];
	$get = 'a='.$_GET['a'];
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$aFile['0']);
	# Variable du formulaire
	$content = '';
	$article = '<a href="article.php?a='.$aArt['numero'].'" title="Article attach&eacute; &agrave; ce commentaire">';
	$article .= plxUtils::strCheck($aArt['title']);
	$article .= '</a>';
	# Ok, on récupère les commentaires de l'article
	$plxAdmin->getCommentaires('/^'.$artId.'.(.*).xml$/','rsort');
} else { # Mode inconnu
	header('Location: .index.php');
	exit;	
}

# On a validé le formulaire
if(!empty($_POST) AND !empty($_POST['content'])) {
	# Création du commentaire
	if(!$plxAdmin->newCommentaire($artId,$_POST['content'])) { # Erreur
		plxMsg::Error('Une erreur est survenue au cours de la cr&eacute;tion du commentaire');
	} else { # Ok
		plxMsg::Info('Le commentaire a &eacute;t&eacute; cr&eacute;e avec succ&egrave;s');
	}
	header('Location: commentaire_new.php?a='.$artId);
	exit;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<?php if(!empty($_GET['a'])) : ?>
	<p><a href="commentaires_online.php?a=<?php echo $_GET['a']; ?>">&laquo; Retour &agrave; la liste des commentaires de cet article</a></p>
<?php else : ?>
	<p><a href="commentaires_online.php">&laquo; Retour &agrave; la liste des commentaires</a></p>
<?php endif; ?>

<h2>R&eacute;diger un commentaire (article &laquo;<?php echo plxUtils::strCheck(plxUtils::strCut($aArt['title'],80)); ?>&raquo;)</h2>

<ul>
	<li>Auteur : <strong><?php echo plxUtils::strCheck($plxAdmin->aUsers[$_SESSION['user']]['name']); ?></strong></li>
	<li>Type de commentaire : <strong>admin</strong></li>
	<li>Site : <?php echo '<a href="'.$plxAdmin->racine.'">'.$plxAdmin->racine.'</a>'; ?></li>
	<li>Article attach&eacute; : <?php echo $article; ?></li>
</ul>

<form action="commentaire_new.php?<?php echo $get ?>" method="post" id="change-com-content">
	<fieldset>
		<?php plxUtils::printArea('content',plxUtils::strCheck($content), 60, 7); ?>
		<p class="center">
			<input type="submit" name="create" value="Enregistrer"/>
		</p>
	</fieldset>
</form>

<?php if($plxAdmin->plxGlob_coms->count AND !empty($plxAdmin->plxRecord_coms->size)) : # On a des commentaires ?>
	<h2>Commentaires de cet article (du plus r&eacute;cent au plus ancien) :</h2>
	<div id="comments">
	<?php while($plxAdmin->plxRecord_coms->loop()) : # On boucle ?>
		<?php $comId = $plxAdmin->plxRecord_coms->f('article').'.'.$plxAdmin->plxRecord_coms->f('numero'); ?>
		<div class="comment type-<?php echo $plxAdmin->plxRecord_coms->f('type') ?><?php echo ($_GET['c']==$comId?' current':'') ?>" id="c<?php echo $plxAdmin->plxRecord_coms->f('numero'); ?>">
			<div class="info_comment">
				<p>Par <strong><?php echo $plxAdmin->plxRecord_coms->f('author'); ?></strong> 
				le <?php echo plxDate::dateIsoToHum($plxAdmin->plxRecord_coms->f('date'), '#day #num_day #month #num_year(4) &agrave; #hour:#minute'); ?>
				 - <a href="commentaire.php<?php echo (!empty($_GET['a']))?'?c='.$comId.'&amp;a='.$_GET['a']:'?c='.$comId; ?>" title="&Eacute;diter ce commentaire">&eacute;diter</a>
				 - <a href="javascript:answerCom('content','<?php echo $plxAdmin->plxRecord_coms->f('numero'); ?>','<?php echo plxUtils::strCheck($plxAdmin->plxRecord_coms->f('author')) ?>');" title="R&Eacute;pondre &agrave; ce commentaire">r&eacute;pondre</a>
				</p>
			</div>
			<blockquote><p><?php echo nl2br($plxAdmin->plxRecord_coms->f('content')); ?></p></blockquote>
		</div>
	<?php endwhile; ?>
	</div>
<?php endif; ?>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>