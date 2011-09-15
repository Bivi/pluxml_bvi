<?php

/**
 * Listing des commentaires en attente de validation
 *
 * @package PLX
 * @author	Stephane F
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPrepend'));

# Control de l'acc�s � la page en fonction du profil de l'utilisateur connect�
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR);

# validation de l'id de l'article si pass� en parametre
if(isset($_GET['a']) AND !preg_match('/^[0-9]{4}$/',$_GET['a'])) {
	plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
	header('Location: index.php');
	exit;
}

# Suppression des commentaires selectionnes
if(isset($_POST['selection']) AND ($_POST['selection'][0] == 'delete' OR $_POST['selection'][1] == 'delete') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->delCommentaire($v);
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}
# Validation des commentaires selectionnes
elseif(isset($_POST['selection']) AND ($_POST['selection'][0] == 'online' OR $_POST['selection'][1] == 'online') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v);
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}
# Mise hors-ligne des commentaires selectionnes
elseif (isset($_POST['selection']) AND ($_POST['selection'][0] == 'offline' OR $_POST['selection'][1] == 'offline') AND isset($_POST['idCom'])) {
	foreach ($_POST['idCom'] as $k => $v) $plxAdmin->modCommentaire($v);
	header('Location: comments.php'.(!empty($_GET['a'])?'?a='.$_GET['a']:''));
	exit;
}

# R�cuperation des infos sur l'article attach� au commentaire si pass� en param�tre
if(!empty($_GET['a'])) {
	# Infos sur notre article
	if(!$globArt = $plxAdmin->plxGlob_arts->query('/^'.$_GET['a'].'.(.*).xml$/','','sort',0,1)) {
		plxMsg::Error(L_ERR_UNKNOWN_ARTICLE); # Article inexistant
		header('Location: index.php');
		exit;
	}
	# Infos sur l'article
	$aArt = $plxAdmin->parseArticle(PLX_ROOT.$plxAdmin->aConf['racine_articles'].$globArt['0']);
	$portee = L_COMMENTS_ARTICLE_SCOPE.' &laquo;'.$aArt['title'].'&raquo;';
} else { # Commentaires globaux
	$portee = L_COMMENTS_GLOBAL_SCOPE;
}

# On inclut le header
include(dirname(__FILE__).'/top.php');

# R�cuperation du type de commentaire � afficher
if(!empty($_GET['sel']) AND in_array($_GET['sel'], array('online', 'offline', 'all')))
	$comSel = plxUtils::nullbyteRemove($_GET['sel']);
else
	$comSel = ((isset($_SESSION['selCom']) AND !empty($_SESSION['selCom'])) ? $_SESSION['selCom'] : 'all');

if(!empty($_GET['a'])) {
	$comSelMotif = '/^_?'.$_GET['a'].'.(.*).xml$/';
	$_SESSION['selCom'] = 'all';
	$nbComPagination=$plxAdmin->nbComments($_GET['a']);
	echo '<h2>'.L_COMMENTS_ALL_LIST.' ('.$nbComPagination.')</h2>';
}
elseif($comSel=='online') {
	$comSelMotif = '/^[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'online';
	$nbComPagination=$plxAdmin->nbComments('online');
	echo '<h2>'.L_COMMENTS_ONLINE_LIST.'</h2>';
}
elseif($comSel=='offline') {
	$comSelMotif = '/^_[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'offline';
	$nbComPagination=$plxAdmin->nbComments('offline');
	echo '<h2>'.L_COMMENTS_OFFLINE_LIST.'</h2>';
}
else { // all
	$comSelMotif = '/^_?[0-9]{4}.(.*).xml$/';
	$_SESSION['selCom'] = 'all';
	$nbComPagination=$plxAdmin->nbComments('all');
	echo '<h2>'.L_COMMENTS_ALL_LIST.'</h2>';
}
?>

<h3><?php echo $portee ?></h3>

<?php eval($plxAdmin->plxPlugins->callHook('AdminCommentsTop')) # Hook Plugins ?>

<form action="comments.php<?php echo !empty($_GET['a'])?'?a='.$_GET['a']:'' ?>" method="post" id="form_comments">

<p class="breadcrumbs">
	<a <?php echo ($_SESSION['selCom']=='all')?'class="selected" ':'' ?>href="comments.php?sel=all&amp;page=1"><?php echo L_ALL ?></a>&nbsp;(<?php echo $plxAdmin->nbComments('all') ?>)&nbsp;|&nbsp;
	<a <?php echo ($_SESSION['selCom']=='online')?'class="selected" ':'' ?>href="comments.php?sel=online&amp;page=1"><?php echo L_COMMENT_ONLINE ?></a>&nbsp;(<?php echo $plxAdmin->nbComments('online') ?>)&nbsp;|&nbsp;
	<a <?php echo ($_SESSION['selCom']=='offline')?'class="selected" ':'' ?>href="comments.php?sel=offline&amp;page=1"><?php echo L_COMMENT_OFFLINE ?></a>&nbsp;(<?php echo $plxAdmin->nbComments('offline') ?>)
	<?php
	if(!empty($_GET['a'])) {
		echo ' | <a href="comment_new.php?a='.$_GET['a'].'" title="'.L_COMMENT_NEW_COMMENT_TITLE.'">'.L_COMMENT_NEW_COMMENT.'</a>';
	}
?>
</p>
<p>
	<?php
	if($comSel=='online')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'offline' => L_COMMENT_SET_OFFLINE), '', false,'',false);
	elseif($comSel=='offline')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'online' => L_COMMENT_SET_ONLINE), '', false,'',false);
	elseif($comSel=='all')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'offline' => L_COMMENT_SET_OFFLINE, 'online' => L_COMMENT_SET_ONLINE), '', false,'',false);

	?>
	<input class="button submit" type="submit" name="submit" value="Ok" />
</p>
<table class="table">
<thead>
	<tr>
		<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idCom[]')" /></th>
		<th class="datetime"><?php echo L_COMMENTS_LIST_DATE ?></th>
		<th class="message"><?php echo L_COMMENTS_LIST_MESSAGE ?></th>
		<th class="author"><?php echo L_COMMENTS_LIST_AUTHOR ?></th>
		<th class="action"><?php echo L_COMMENTS_LIST_ACTION ?></th>
	</tr>
</thead>
<tbody>

<?php
# On va r�cup�rer les commentaires
$plxAdmin->getPage();
$start = $plxAdmin->aConf['bypage_admin_coms']*($plxAdmin->page-1);
$coms = $plxAdmin->getCommentaires($comSelMotif,'rsort',$start,$plxAdmin->aConf['bypage_admin_coms'],'all');
if($coms) {
	$num=0;
	while($plxAdmin->plxRecord_coms->loop()) { # On boucle
		$year = substr($plxAdmin->plxRecord_coms->f('date'),0,4);
		$month = substr($plxAdmin->plxRecord_coms->f('date'),5,2);
		$day = substr($plxAdmin->plxRecord_coms->f('date'),8,2);
		$time = substr($plxAdmin->plxRecord_coms->f('date'),11,8);
		$artId = $plxAdmin->plxRecord_coms->f('article');
		$status = $plxAdmin->plxRecord_coms->f('status');
		$id = $status.$artId.'.'.$plxAdmin->plxRecord_coms->f('numero');
		$content = nl2br($plxAdmin->plxRecord_coms->f('content'));
		if($_SESSION['selCom']=='all') {
			$content = $content.' - <strong>'.($status==''?L_COMMENT_ONLINE:L_COMMENT_OFFLINE).'</strong><br />';
		}
		# On g�n�re notre ligne
		echo '<tr class="line-'.(++$num%2).' top type-'.$plxAdmin->plxRecord_coms->f('type').'">';
		echo '<td><input type="checkbox" name="idCom[]" value="'.$id.'" /></td>';
		echo '<td>'.$day.'/'.$month.'/'.$year.' '.$time.'&nbsp;</td>';
		echo '<td>'.$content.'&nbsp;</td>';
		echo '<td>'.plxUtils::strCut($plxAdmin->plxRecord_coms->f('author'),30).'&nbsp;</td>';
		echo '<td> ';
		echo '<a href="comment.php?c='.$id.(!empty($_GET['a'])?'&amp;a='.$_GET['a']:'').'" title="'.L_COMMENT_EDIT_TITLE.'">'.L_COMMENT_EDIT.'</a> | ';
		echo '<a href="article.php?a='.$artId.'" title="'.L_COMMENT_ARTICLE_LINKED_TITLE.'">'.L_COMMENT_ARTICLE_LINKED.'</a>';
		echo '</td></tr>';
	}
} else { # Pas de commentaires
	echo '<tr><td colspan="5" class="center">'.L_NO_COMMENT.'</td></tr>';
}
?>
</tbody>
</table>
<p>
	<?php echo plxToken::getTokenPostMethod() ?>
	<?php
	if($comSel=='online')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'offline' => L_COMMENT_SET_OFFLINE), '', false,'',false);
	elseif($comSel=='offline')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'online' => L_COMMENT_SET_ONLINE), '', false,'',false);
	elseif($comSel=='all')
		plxUtils::printSelect('selection[]', array(''=> L_FOR_SELECTION, 'delete' => L_COMMENT_DELETE, 'offline' => L_COMMENT_SET_OFFLINE, 'online' => L_COMMENT_SET_ONLINE), '', false,'',false);

	?>
	<input class="button submit" type="submit" name="submit" value="Ok" />
</p>
</form>

<div id="pagination">
<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsPagination'));
# Affichage de la pagination
if($coms) { # Si on a des commentaires (hors page)
	# Calcul des pages
	$last_page = ceil($nbComPagination/$plxAdmin->aConf['bypage_admin_coms']);
	if($plxAdmin->page > $last_page) $plxAdmin->page = $last_page;
	$prev_page = $plxAdmin->page - 1;
	$next_page = $plxAdmin->page + 1;
	# Generation des URLs
	$p_url = 'comments.php?page='.$prev_page.'&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Page precedente
	$n_url = 'comments.php?page='.$next_page.'&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Page suivante
	$l_url = 'comments.php?page='.$last_page.'&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Derniere page
	$f_url = 'comments.php?page=1'.'&amp;sel='.$_SESSION['selCom'].(!empty($_GET['a'])?'&amp;a='.$_GET['a']:''); # Premiere page
	# On effectue l'affichage
	if($plxAdmin->page > 2) # Si la page active > 2 on affiche un lien 1ere page
		echo '<span class="p_first"><a href="'.$f_url.'" title="'.L_PAGINATION_FIRST_TITLE.'">'.L_PAGINATION_FIRST.'</a></span>';
	if($plxAdmin->page > 1) # Si la page active > 1 on affiche un lien page precedente
		echo '<span class="p_prev"><a href="'.$p_url.'" title="'.L_PAGINATION_PREVIOUS_TITLE.'">'.L_PAGINATION_PREVIOUS.'</a></span>';
	# Affichage de la page courante
	printf('<span class="p_page">'.L_PAGINATION.'</span>',$plxAdmin->page,$last_page);
	if($plxAdmin->page < $last_page) # Si la page active < derniere page on affiche un lien page suivante
		echo '<span class="p_next"><a href="'.$n_url.'" title="'.L_PAGINATION_NEXT_TITLE.'">'.L_PAGINATION_NEXT.'</a></span>';
	if(($plxAdmin->page + 1) < $last_page) # Si la page active++ < derniere page on affiche un lien derniere page
		echo '<span class="p_last"><a href="'.$l_url.'" title="'.L_PAGINATION_LAST_TITLE.'">'.L_PAGINATION_LAST.'</a></span>';
}
?>
</div>

<?php if(!empty($plxAdmin->aConf['clef'])) : ?>
<div class="feeds">
	<p><?php echo L_COMMENTS_PRIVATE_FEEDS ?> :</p>
	<ul class="feed">
		<?php $urlp_hl = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/hors-ligne'; ?>
		<li><a href="<?php echo $urlp_hl ?>" title="<?php echo L_COMMENT_OFFLINE_FEEDS_TITLE ?>"><?php echo L_COMMENT_OFFLINE_FEEDS ?></a></li>
		<?php $urlp_el = $plxAdmin->racine.'feed.php?admin'.$plxAdmin->aConf['clef'].'/commentaires/en-ligne'; ?>
		<li><a href="<?php echo $urlp_el ?>" title="<?php echo L_COMMENT_ONLINE_FEEDS_TITLE ?>"><?php echo L_COMMENT_ONLINE_FEEDS ?></a></li>
	</ul>
</div>
<?php endif; ?>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminCommentsFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>
