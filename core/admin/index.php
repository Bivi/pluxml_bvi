<?php

/**
 * Listing des articles
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Suppression des articles selectionnes
if(isset($_POST['selection']) AND $_POST['selection'] == 'delete' AND isset($_POST['idArt'])) {
	foreach ($_POST['idArt'] as $k => $v) $plxAdmin->delArticle($v);
	header('Location: index.php');
	exit;
}

# Check des variables GET pour la recherche
$_GET['catId'] = (!empty($_GET['catId']))?plxUtils::unSlash(trim($_GET['catId'])):'';
$_GET['artTitle'] = (!empty($_GET['artTitle']))?plxUtils::unSlash(trim($_GET['artTitle'])):'';
# On génère notre motif de recherche
$userId = ($_SESSION['profil'] < PROFIL_WRITER ? '[0-9]{3}' : $_SESSION['user']);
if($_GET['catId'] != '')
	$motif = '/^[0-9]{4}.(.*)'.$_GET['catId'].'(.*).'.$userId.'.[0-9]{12}.(.*)'.plxUtils::title2filename($_GET['artTitle']).'(.*).xml$/';
else
	$motif = '/^[0-9]{4}.([0-9,|home|draft]*).'.$userId.'.[0-9]{12}.(.*)'.plxUtils::title2filename($_GET['artTitle']).'(.*).xml$/';

# Traitement
$plxAdmin->prechauffage('admin', $motif, $plxAdmin->aConf['bypage_admin']);
$plxAdmin->getPage(); # Recuperation de la page
$plxAdmin->getFiles('all'); # Recuperation des fichiers
$plxAdmin->getArticles(); # Recuperation des articles

# Génération de notre tableau des catégories
if($plxAdmin->aCats) {
	foreach($plxAdmin->aCats as $k=>$v) $aCat[$k] = plxUtils::strCheck($v['name']);
	$aAllCat['Cat&eacute;gories'] = $aCat;
}
$aAllCat['Emplacements sp&eacute;cifiques']['home'] = 'Page d\'accueil';
$aAllCat['Emplacements sp&eacute;cifiques']['draft'] = 'Brouillons';
$aAllCat['Emplacements sp&eacute;cifiques'][''] = 'Tous les articles';	

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Liste des articles</h2>

<form action="index.php?page=1" method="get" id="frm_sel">
	<fieldset class="withlabel">
		<?php plxUtils::printInput('page',1,'hidden'); ?> 
		<legend>Filtre de recherche :</legend>
		<p class="center">Titre de l'article&nbsp;: <?php plxUtils::printInput('artTitle',plxUtils::strCheck($_GET['artTitle']),'text','30-50'); ?> 
		Emplacement&nbsp;: <?php plxUtils::printSelect('catId', $aAllCat, $_GET['catId']); ?>
		<input class="button" type="submit" value="Filtrer" /></p>
	</fieldset>
</form>

<form action="index.php" method="post" id="frm_arts">
<table class="table">
<thead>
	<tr>
		<th style="width:5px"><input type="checkbox" onclick="checkAll(this.form, 'idArt[]')" /></th>	
		<th class="tc1">Date</th>
		<th class="tc2">Titre</th>
		<th class="tc4">Cat&eacute;gorie</th>
		<th class="tc1">Nb coms</th>
		<th class="tc4">Auteur</th>			
		<th class="tc4">Action</th>
	</tr>
</thead>
<tbody>

<?php
# On va lister les articles
if($plxAdmin->plxGlob_arts->count AND $plxAdmin->plxRecord_arts->size) { # On a des articles
	while($plxAdmin->plxRecord_arts->loop()) { # Pour chaque article
		$author = $plxAdmin->aUsers[$plxAdmin->plxRecord_arts->f('author')]['name'];
		# Date
		$year = substr($plxAdmin->plxRecord_arts->f('date'), 0, 4);
		$month = substr($plxAdmin->plxRecord_arts->f('date'), 5, 2);
		$day = substr($plxAdmin->plxRecord_arts->f('date'), 8, 2);
		$publi = ($plxAdmin->plxRecord_arts->f('date') > plxDate::timestampToIso(time(),$plxAdmin->aConf['delta']))?false:true;
		# Catégories : liste des libellelés toutes les categories
		$draft='';
		$libCats='';
		$catIds = explode(',', $plxAdmin->plxRecord_arts->f('categorie'));
		if(sizeof($catIds)>0) {
			$catsName = array();
			foreach($catIds as $catId) {
				if($catId=='home') $catsName[] = 'Accueil';
				elseif($catId=='draft') $draft= ' - <strong>Brouillon</strong>';
				elseif(!isset($plxAdmin->aCats[$catId])) $catsName[] = 'Non class&eacute;';
				else $catsName[] = plxUtils::strCheck($plxAdmin->aCats[$catId]['name']);
			}
			if(sizeof($catsName)>0) {
				$libCats = $catsName[0];
				unset($catsName[0]);
				if(sizeof($catsName)>0) $libCats .= ', ... <a class="help" title="'.implode(',', $catsName).'">&nbsp;</a>';
			}
			else $libCats = 'Non class&eacute;';
		}
		# Commentaires
		$nbComsToValidate = $plxAdmin->getNbCommentaires('/^_'.$plxAdmin->plxRecord_arts->f('numero').'.(.*).xml$/');
		$nbComsValidated = $plxAdmin->getNbCommentaires('/^'.$plxAdmin->plxRecord_arts->f('numero').'.(.*).xml$/');
		# On affiche la ligne
		echo '<tr class="line-'.($plxAdmin->plxRecord_arts->i%2).'">';
		echo '<td><input type="checkbox" name="idArt[]" value="'.$plxAdmin->plxRecord_arts->f('numero').'" /></td>';
		echo '<td class="tc1">&nbsp;'.$day.'/'.$month.'/'.$year.'</td>';	
		echo '<td class="tc4">&nbsp;<a href="article.php?a='.$plxAdmin->plxRecord_arts->f('numero').'" title="&Eacute;diter cet article">'.plxUtils::strCheck(plxUtils::strCut($plxAdmin->plxRecord_arts->f('title'),60)).'</a>'.$draft.'</td>';
		echo '<td class="tc1">&nbsp;'.$libCats.'</td>';
		echo '<td class="tc1" style="text-align:center">&nbsp;<a title="Commentaires en attente de validation" href="commentaires_offline.php?a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsToValidate.'</a> / <a title="Commentaires publi&eacute;s" href="commentaires_online.php?a='.$plxAdmin->plxRecord_arts->f('numero').'&amp;page=1">'.$nbComsValidated.'</a></td>';
		echo '<td class="tc4">&nbsp;'.plxUtils::strCheck($author).'</td>';
		echo '<td class="tc4" style="text-align:center">&nbsp;';
		if($publi AND $draft=='') # Si l'article est publié
			echo '<a href="'.PLX_ROOT.'?article'.intval($plxAdmin->plxRecord_arts->f('numero')).'/'.$plxAdmin->plxRecord_arts->f('url').'" title="Visualiser cet article sur le site">Visualiser</a> - ';
		echo '<a href="article.php?a='.$plxAdmin->plxRecord_arts->f('numero').'" title="Editer cet article">&Eacute;diter</a>';
		echo "</td>";
		echo "</tr>";
	}
	?>
	<tr>
		<td colspan="7">
			<?php plxUtils::printSelect('selection', array( '' => 'Pour la s&eacute;lection...', 'delete' => 'Supprimer'), '') ?>
			<input class="button" type="submit" name="submit" value="Ok" />
		</td>
	</tr>
	<?php
} else { # Pas d'article
	echo '<tr><td colspan="7" class="center">Aucun article ne correspond &agrave; votre recherche</td></tr>';
}
?>

</tbody>
</table>
</form>

<div id="pagination">
<?php # Affichage de la pagination
if($plxAdmin->plxGlob_arts->count) { # Si on a des articles (hors page)
	# Calcul des pages
	$last_page = ceil($plxAdmin->plxGlob_arts->count/$plxAdmin->bypage);	
	if($plxAdmin->page > $last_page) $plxAdmin->page = $last_page;
	$prev_page = $plxAdmin->page - 1;
	$next_page = $plxAdmin->page + 1;
	# Generation des URLs
	$p_url = './?page='.$prev_page.'&amp;catId='.urlencode($_GET['catId']).'&amp;artTitle='.urlencode($_GET['artTitle']); # Page precedente
	$n_url = './?page='.$next_page.'&amp;catId='.urlencode($_GET['catId']).'&amp;artTitle='.urlencode($_GET['artTitle']); # Page suivante
	$l_url = './?page='.$last_page.'&amp;catId='.urlencode($_GET['catId']).'&amp;artTitle='.urlencode($_GET['artTitle']); # Derniere page
	$f_url = './?page=1&amp;catId='.urlencode($_GET['catId']).'&amp;artTitle='.urlencode($_GET['artTitle']); # Premiere page
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

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>