<?php

/**
 * Edition des catégories
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN, PROFIL_MODERATOR);

# On édite les catégories
if(!empty($_POST)) {
	$plxAdmin->editCategories($_POST);
	header('Location: categories.php');
	exit;
}

# On récupère les templates des categories
$files = plxGlob::getInstance(PLX_ROOT.'themes/'.$plxAdmin->aConf['style']);
if ($array = $files->query('/categorie(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

# Tableau du tri
$aTri = array('desc'=>'d&eacute;croissant', 'asc'=>'croissant');

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2>Cr&eacute;ation et &eacute;dition de cat&eacute;gories</h2>

<form action="categories.php" method="post" id="change-cat-file">
	<table class="table">
	<thead>
		<tr>
			<th style="width:5px"><input type="checkbox" onclick="checkAll(this.form, 'idCategory[]')" /></th>	
			<th class="tc4">Identifiant</th>
			<th class="tc4">Nom de la cat&eacute;gorie</th>
			<th class="tc4">Url</th>
			<th class="tc4">Tri des articles</th>
			<th class="tc4">Nb art/page</th>
			<th class="tc4">Ordre</th>				
			<th class="tc4">Menu</th>
			<th class="tc5">&nbsp;</th>
		</tr>
	</thead>
	<tbody>	
	<?php
	# Initialisation de l'ordre
	$num = 0;
	# Si on a des catégories
	if($plxAdmin->aCats) {
		foreach($plxAdmin->aCats as $k=>$v) { # Pour chaque catégorie
			$ordre = ++$num;
			echo '<tr class="line-'.($num%2).'">';
			echo '<td class="tc7"><input type="checkbox" name="idCategory[]" value="'.$k.'" /><input type="hidden" name="catNum[]" value="'.$k.'" /></td>';
			echo '<td class="tc6">Cat&eacute;gorie '.$k.'</td><td>';	
			plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '15-50');
			echo '</td><td>';
			plxUtils::printInput($k.'_url', $v['url'], 'text', '15-50');
			echo '</td><td>';
			plxUtils::printSelect($k.'_tri', $aTri, $v['tri']);
			echo '</td><td>';
			plxUtils::printInput($k.'_bypage', $v['bypage'], 'text', '4-3');
			echo '</td><td>';
			plxUtils::printInput($k.'_ordre', $ordre, 'text', '3-3');
			echo '</td><td>';
			plxUtils::printSelect($k.'_menu', array('oui'=>'Afficher','non'=>'Masquer'), $v['menu']);
			echo '</td><td class="tc6">';
			echo '<a id="link_'.$k.'" href="#" onclick="toggleTR(\'link_'.$k.'\', \'tr_'.$k.'\')">Options</a>';
			echo '</td></tr>';
			echo '<tr class="options" id="tr_'.$k.'"><td colspan="3" class="options-head">Template&nbsp;:</td><td colspan="6">&nbsp;&nbsp;themes/'.$plxAdmin->aConf['style'].'/';
			plxUtils::printSelect($k.'_template', $aTemplates, $v['template']);
			echo '</td></tr>';
		}
		# On récupère le dernier identifiant
		$a = array_keys($plxAdmin->aCats);
		rsort($a);
	} else {
		$a['0'] = 0;
	}
	$new_catid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
	?>
		<tr style="background-color:#e0e0e0">
			<td>&nbsp;</td>
			<td class="tc6">Nouvelle cat&eacute;gorie</td>
			<td>
			<?php
				echo '<input type="hidden" name="catNum[]" value="'.$new_catid.'" />';
				plxUtils::printInput($new_catid.'_template', 'categorie.php', 'hidden');
				plxUtils::printInput($new_catid.'_name', '', 'text', '15-50');
				echo '</td><td>';
				plxUtils::printInput($new_catid.'_url', '', 'text', '15-50');
				echo '</td><td>';
				plxUtils::printSelect($new_catid.'_tri', $aTri, $plxAdmin->aConf['tri']);
				echo '</td><td>';
				plxUtils::printInput($new_catid.'_bypage', $plxAdmin->aConf['bypage'], 'text', '4-3');
				echo '</td><td>';
				plxUtils::printInput($new_catid.'_ordre', ++$num, 'text', '3-3');
				echo '</td><td>';
				plxUtils::printSelect($new_catid.'_menu', array('oui'=>'Afficher','non'=>'Masquer'), '1');
				echo '</td><td>&nbsp;';
			?>
			</td>
		</tr>
		<tr>
			<td colspan="9">
				<?php plxUtils::printSelect('selection', array( '' => 'Pour la s&eacute;lection...', 'delete' => 'Supprimer'), '') ?>
				<input class="button" type="submit" name="submit" value="Ok" />
			</td>
		</tr>
		<tr>
			<td colspan="9" style="text-align:center">
				<input class="button" type="submit" name="update" value="Modifier la liste des cat&eacute;gories" />
			</td>
		</tr>
	</tbody>
	</table>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>