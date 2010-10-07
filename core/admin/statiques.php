<?php

/**
 * Edition des pages statiques
 *
 * @package PLX
 * @author	Stephane F et Florent MONTHEL
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# On édite les pages statiques
if(!empty($_POST)) {
	$plxAdmin->editStatiques($_POST);
	header('Location: statiques.php');
	exit;
}

# On récupère les templates des pages statiques
$files = plxGlob::getInstance(PLX_ROOT.'themes/'.$plxAdmin->aConf['style']);
if ($array = $files->query('/static(-[a-z0-9-_]+)?.php$/')) {
	foreach($array as $k=>$v)
		$aTemplates[$v] = $v;
}

# On inclut le header	
include(dirname(__FILE__).'/top.php');
?>

<h2>Cr&eacute;ation et &eacute;dition des pages statiques</h2>
<form action="statiques.php" method="post" id="change-static-file">
	<table class="table">
	<thead>
		<tr>
			<th style="width:5px"><input type="checkbox" onclick="checkAll(this.form, 'idStatic[]')" /></th>	
			<th class="tc4">Identifiant</th>
			<th class="tc4">Groupe</th>
			<th class="tc4">Titre</th>
			<th class="tc4">Url</th>
			<th class="tc4">Active</th>
			<th class="tc4">Ordre</th>
			<th class="tc4">Menu</th>
			<th class="tc5">Action</th>
		</tr>
	</thead>
	<tbody>
	<?php
	# Initialisation de l'ordre
	$num = 0;
	# Si on a des pages statiques
	if($plxAdmin->aStats) {
		foreach($plxAdmin->aStats as $k=>$v) { # Pour chaque page statique
			$ordre = ++$num;
			echo '<tr class="line-'.($num%2).'">';
			echo '<td class="tc7"><input type="checkbox" name="idStatic[]" value="'.$k.'" /><input type="hidden" name="staticNum[]" value="'.$k.'" /></td>';
			echo '<td class="tc6">Page '.$k.($k==$plxAdmin->aConf['homestatic']?' <img src="img/home.png" alt="" title="D&eacute;finie en tant que page d\'accueil" />':'').'</td><td>';
			plxUtils::printInput($k.'_group', plxUtils::strCheck($v['group']), 'text', '13-50');
			echo '</td><td>';			
			plxUtils::printInput($k.'_name', plxUtils::strCheck($v['name']), 'text', '13-50');
			echo '</td><td>';
			plxUtils::printInput($k.'_url', $v['url'], 'text', '12-50');
			echo '</td><td>';
			plxUtils::printSelect($k.'_active', array('1'=>'Oui','0'=>'Non'), $v['active']);
			echo '</td><td>';	
			plxUtils::printInput($k.'_ordre', $ordre, 'text', '2-3');
			echo '</td><td>';	
			plxUtils::printSelect($k.'_menu', array('oui'=>'Afficher','non'=>'Masquer'), $v['menu']);			
			echo '</td><td class="tc6">';
			echo '<a href="statique.php?p='.$k.'" title="Editer le code source de cette page">&Eacute;diter</a>&nbsp;-&nbsp;';
			echo '<a href="'.PLX_ROOT.'?static'.intval($k).'/'.$v['url'].'" title="Visualiser la page '.plxUtils::strCheck($v['name']).' sur le site">Voir</a>&nbsp;-&nbsp;';
			echo '<a id="link_'.$k.'" href="#" onclick="toggleTR(\'link_'.$k.'\', \'tr_'.$k.'\')">Options</a>';
			echo '</td></tr>';
			echo '<tr class="options" id="tr_'.$k.'"><td colspan="3" class="options-head">Template&nbsp;:</td><td colspan="6">&nbsp;&nbsp;themes/'.$plxAdmin->aConf['style'].'/';
			plxUtils::printSelect($k.'_template', $aTemplates, $v['template']);
			echo '</td></tr>';
			
		}
		# On récupère le dernier identifiant
		$a = array_keys($plxAdmin->aStats);
		rsort($a);	
	} else {
		$a['0'] = 0;
	}
	$new_staticid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
	?>
		<tr style="background-color:#e0e0e0">
			<td>&nbsp;</td>
			<td class="tc6">Nouvelle page</td>
			<td>
			<?php
				echo '<input type="hidden" name="staticNum[]" value="'.$new_staticid.'" />';
				plxUtils::printInput($new_staticid.'_group', '', 'hidden', '13-50');
				echo '</td><td>';				
				plxUtils::printInput($new_staticid.'_name', '', 'text', '13-50');
				plxUtils::printInput($new_staticid.'_template', 'static.php', 'hidden');
				echo '</td><td>';
				plxUtils::printInput($new_staticid.'_url', '', 'text', '12-50');
				echo '</td><td>';
				plxUtils::printSelect($new_staticid.'_active', array('1'=>'Oui','0'=>'Non'), '0');
				echo '</td><td>';
				plxUtils::printInput($new_staticid.'_ordre', ++$num, 'text', '2-3');
				echo '</td><td>';
				plxUtils::printSelect($new_staticid.'_menu', array('oui'=>'Afficher','non'=>'Masquer'), '1');
			?>
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="9">
				<?php plxUtils::printSelect('selection', array( '' => 'Pour la s&eacute;lection...', 'delete' => 'Supprimer'), '') ?>
				<input class="button" type="submit" name="submit" value="Ok" />
			</td>
		</tr>
		<tr>
			<td colspan="8" style="text-align:center">
				<input class="button" type="submit" name="update" value="Modifier la liste des pages statiques" />
			</td>
		</tr>
	</tbody>
	</table>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>