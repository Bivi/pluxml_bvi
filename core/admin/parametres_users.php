<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author	Stephane F. 
 **/

include(dirname(__FILE__).'/prepend.php');

# Control de l'acc�s � la page en fonction du profil de l'utilisateur connect�
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Edition des utilisateurs
if (!empty($_POST)) {
	$plxAdmin->editUsers($_POST);
	header('Location: parametres_users.php');
	exit;
}

# Tableau des profils
$aProfils = array(
	PROFIL_ADMIN => 'Administrateur',
	PROFIL_MODERATOR => 'R&eacute;dacteur avanc&eacute;',
	PROFIL_WRITER => 'R&eacute;dacteur',
        PROFIL_READER => 'Lecteur'
);

# On inclut le header	
include(dirname(__FILE__).'/top.php');
?>

<h2>Gestion des utilisateurs</h2>

<form action="parametres_users.php" method="post" id="change_users_file">
	<table class="table">
	<thead>
		<tr>
			<th style="width:5px"><input type="checkbox" onclick="checkAll(this.form, 'idUser[]')" /></th>	
			<th class="tc4">Num&eacute;ro d'utilisateur</th>
			<th class="tc1">Nom d'utilisateur</th>			
			<th class="tc4">Login connexion</th>				
			<th class="tc4">Mot de passe</th>
			<th class="tc4">Profil</th>
			<th class="tc4">Actif</th>
			<th class="tc5">Action</th>			
		</tr>
	</thead>
	<tbody>			
	<?php
	# Initialisation de l'ordre
	$num = 0;	
	if($plxAdmin->aUsers) {
		foreach($plxAdmin->aUsers as $userid => $user)	{
			if (!$user['delete']) {
				echo '<tr class="line-'.($num%2).'">';
				echo '<td class="tc7"><input type="checkbox" name="idUser[]" value="'.$userid.'" /><input type="hidden" name="userNum[]" value="'.$userid.'" /></td>';
				echo '<td class="tc6">Utilisateur '.$userid.'</td><td>';
				plxUtils::printInput($userid.'_name', plxUtils::strCheck($user['name']), 'text', '20-255');
				echo '</td><td>';
				plxUtils::printInput($userid.'_login', plxUtils::strCheck($user['login']), 'text', '11-255');
				echo '</td><td>';				
				plxUtils::printInput($userid.'_password', '', 'password', '11-255');
				echo '</td><td>';
				if($userid=='001') {
					plxUtils::printSelect($userid.'_profil', $aProfils, $user['profil'], true, 'readonly');
					echo '</td><td>';
					plxUtils::printSelect($userid.'_active', array('0'=>'Non','1'=>'Oui'), $user['active'], true, 'readonly');
				} else {
					plxUtils::printSelect($userid.'_profil', $aProfils, $user['profil']);
					echo '</td><td>';
					plxUtils::printSelect($userid.'_active', array('0'=>'Non','1'=>'Oui'), $user['active']);
				}
				echo '</td><td class="tc7"><a id="link_'.$userid.'" href="#" onclick="toggleTR(\'link_'.$userid.'\', \'tr_'.$userid.'\')">Options</a></td></tr>';
				echo '<tr style="display:none" id="tr_'.$userid.'"><td style="width:100%" colspan="8">';
				echo '<p>Informations&nbsp;:</p>';
				plxUtils::printArea($userid.'_infos',plxUtils::strCheck($user['infos']),60,5);
				echo '</td></tr>';
			}
		}
		# On r�cup�re le dernier identifiant
		$a = array_keys($plxAdmin->aUsers);
		rsort($a);
	} else {
		$a['0'] = 0;
	}
	$new_userid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
	?>
		<tr style="background-color:#e0e0e0">
		<td>&nbsp;</td>
			<td class="tc6">Nouvel utilisateur</td>
			<td>
			<?php
				echo '<input type="hidden" name="userNum[]" value="'.$new_userid.'" />';
				plxUtils::printInput($new_userid.'_newuser', 'true', 'hidden');
				plxUtils::printInput($new_userid.'_name', '', 'text', '20-255');
				plxUtils::printInput($new_userid.'_infos', '', 'hidden');
				echo '</td><td>';				
				plxUtils::printInput($new_userid.'_login', '', 'text', '11-255');
				echo '</td><td>';
				plxUtils::printInput($new_userid.'_password', '', 'password', '11-255');
				echo '</td><td>';
				plxUtils::printSelect($new_userid.'_profil', $aProfils, PROFIL_READER);
				echo '</td><td>';
				plxUtils::printSelect($new_userid.'_active', array( '0' => 'Non', '1' => 'Oui'), '1');
				echo '</td>';
			?>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="8">
				<?php plxUtils::printSelect('selection', array( '' => 'Pour la s&eacute;lection...', 'delete' => 'Supprimer'), '') ?>
				<input class="button" type="submit" name="submit" value="Ok" />
			</td>
		</tr>
		<tr>
			<td colspan="8" style="text-align:center">
				<input class="button" type="submit" name="update" value="Modifier la liste des utilisateurs" />
			</td>
		</tr>
	</tbody>
	</table>
</form>

<?php
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>