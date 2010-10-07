<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F 
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MODERATOR);

# Suppression des commentaires selectionnes
if(isset($_POST['selection']) AND $_POST['selection'] == 'delete' AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->delCommentaire($v);
	header('Location: commentaires_online.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}
# Mise hors-ligne des commentaires selectionnes
elseif (isset($_POST['selection']) AND ($_POST['selection'] == 'offline') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v);
	header('Location: commentaires_online.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}

# Commentaires d'un article, on check
if(!empty($_GET['a'])) {
	# Infos sur notre article
	$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.*).xml$/','','sort',0,1);
	if(!$plxAdmin->plxGlob_arts->count) { # Article inexistant 
		plxMsg::Error('L\'article demand&eacute n\'existe pas ou n\'existe plus');
		header('Location: index.php');
		exit;
	}
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
	$portee = 'article &laquo;'.plxUtils::strCheck(plxUtils::strCut($aArt['title'],80)).'&raquo;';
	$artRegex = $_GET['a'];
} else { # Commentaires globaux
	$portee = 'site entier';
	$artRegex = '[0-9]{4}';
}

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Liste des commentaires publi&eacute;s (<?php echo $portee; ?>)</h2>

<form action="commentaires_online.php<?php echo !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post">
<table class="table">
<thead>
	<tr>
		<th style="width:5px"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
		<th style="width:110px">Date</th>
		<th style="width:100px">Auteur</th>
		<th>Message</th>
		<th style="width:200px">Action</th>
	</tr>
</thead>
<tbody>
	
<?php
# On va récupérer les commentaires publiés pour cette page
$plxAdmin->getPage();
$start = $plxAdmin->aConf['bypage_admin_coms']*($plxAdmin->page-1);
$plxAdmin->getCommentaires('/^'.$artRegex.'.(.*).xml$/','rsort',$start,$plxAdmin->aConf['bypage_admin_coms'],'all');
if($plxAdmin->plxGlob_coms->count AND !empty($plxAdmin->plxRecord_coms->size)) { # On a des commentaires
	while($plxAdmin->plxRecord_coms->loop()) { # On boucle
		$year = substr($plxAdmin->plxRecord_coms->f('date'),0,4);
		$month = substr($plxAdmin->plxRecord_coms->f('date'),5,2);
		$day = substr($plxAdmin->plxRecord_coms->f('date'),8,2);
		$time = substr($plxAdmin->plxRecord_coms->f('date'),11,8);
		$artId = $plxAdmin->plxRecord_coms->f('article');
		$id = $artId.'.'.$plxAdmin->plxRecord_coms->f('numero');
		# On coupe le commentaire mais attention aux entités HTML
		if($plxAdmin->plxRecord_coms->f('type') == 'admin')
			$content = plxUtils::strCut(strip_tags($plxAdmin->plxRecord_coms->f('content')),70);
		else
			$content = plxUtils::strCheck(plxUtils::strCut(plxUtils::strRevCheck($plxAdmin->plxRecord_coms->f('content')),70));
		# On génère notre ligne
		echo '<tr class="line-'.($plxAdmin->plxRecord_coms->i%2).'">';
		echo '<td><input type="checkbox" name="idCom[]" value="'.$id.'" /></td>';
		echo '<td>&nbsp;'.$day.'/'.$month.'/'.$year.' '.$time.'</td>';
		echo '<td>&nbsp;'.plxUtils::strCut($plxAdmin->plxRecord_coms->f('author'),15).'</td>';
		echo '<td>&nbsp;<a href="'.PLX_ROOT.'?article'.intval($artId).'/#c'.$plxAdmin->plxRecord_coms->f('numero').'" title="Visualiser le commentaire sur le site">'.$content.'</a></td>';
		echo '<td style="text-align:center"> ';
		echo '<a href="commentaire.php?c='.$id.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'').'" title="&Eacute;diter ce commentaire">&Eacute;diter</a> - ';
		echo '<a href="commentaire_new.php?c='.$id.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'').'" title="R&eacute;pondre &agrave; ce commentaire">R&eacute;pondre</a> - ';
		echo '<a href="article.php?a='.$artId.'" title="Article attach&eacute; &agrave; ce commentaire">Article</a>';
		echo '</td></tr>';
	}
	?>
	<tr>
		<td colspan="5">
			<?php plxUtils::printSelect('selection', array(''=> 'Pour la s&eacute;lection...', 'delete' => 'Supprimer', 'offline' => 'Mettre hors ligne'), ''); ?>
			<input class="button" type="submit" name="submit" value="Ok" />
		</td>
	</tr>
	<?php		
} else { # Pas de commentaires
	echo '<tr><td colspan="5" class="center">Aucun commentaire</td></tr>';
}
?>
</tbody>
</table>
</form>

<div id="pagination">
<?php # Affichage de la pagination
if($plxAdmin->plxGlob_coms->count) { # Si on a des commentaires (hors page)
	# Calcul des pages
	$last_page = ceil($plxAdmin->plxGlob_coms->count/$plxAdmin->aConf['bypage_admin_coms']);
	if($plxAdmin->page > $last_page) $plxAdmin->page = $last_page;
	$prev_page = $plxAdmin->page - 1;
	$next_page = $plxAdmin->page + 1;
	# Generation des URLs
	$p_url = 'commentaires_online.php?page='.$prev_page.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Page precedente
	$n_url = 'commentaires_online.php?page='.$next_page.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Page suivante
	$l_url = 'commentaires_online.php?page='.$last_page.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Derniere page
	$f_url = 'commentaires_online.php?page=1'.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Premiere page
	# On effectue l'affichage
	if($plxAdmin->page > 2) # Si la page active > 2 on affiche un lien 1ere page
		echo '<span><a href="'.$f_url.'" title="Aller à la premi&egrave;re page">&laquo;</a></span>';
	if($plxAdmin->page > 1) # Si la page active > 1 on affiche un lien page precedente
		echo '<span><a href="'.$p_url.'" title="Page pr&eacute;c&eacute;dente">pr&eacute;c&eacute;dent</a></span>';
	# Affichage de la page courante
	echo '<span>Page '.$plxAdmin->page.' sur '.$last_page.'</span>';
	if($plxAdmin->page < $last_page) # Si la page active < derniere page on affiche un lien page suivante
		echo '<span><a href="'.$n_url.'" title="Page suivante">suivant</a></span>';
	if(($plxAdmin->page + 1) < $last_page) # Si la page active++ < derniere page on affiche un lien derniere page
		echo '<span><a href="'.$l_url.'" title="Aller &agrave; la derni&egrave;re page">&raquo;</a></span>';
} ?>
</div>

<?php if(!empty($plxAdmin->aConf['clef'])) : ?>
<fieldset class="withlabel">
<legend>Flux de syndication priv&eacute;s :</legend>
	<ul class="feed">
		<?php $urlp_hl = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/hors-ligne'; ?>
		<li>Commentaires hors-ligne : <a href="<?php echo $urlp_hl ?>" title="Flux atom des commentaires hors-ligne"><?php echo $urlp_hl ?></a></li>
		<?php $urlp_el = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/en-ligne'; ?>
		<li>Commentaires en-ligne : <a href="<?php echo $urlp_el ?>" title="Flux atom des commentaires en-ligne"><?php echo $urlp_el ?></a></li>
	</ul>
</fieldset>
<?php endif; ?>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>