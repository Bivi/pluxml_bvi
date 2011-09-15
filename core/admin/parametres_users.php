<?php
/**
 * Edition des utilisateurs
 *
 * @package PLX
 * @author	Stephane F.
 **/

include(dirname(__FILE__).'/prepend.php');

# Control du token du formulaire
plxToken::validateFormToken($_POST);

# Control de l'accès à la page en fonction du profil de l'utilisateur connecté
$plxAdmin->checkProfil(PROFIL_ADMIN);

# Edition des utilisateurs
if (!empty($_POST)) {
	$plxAdmin->editUsers($_POST);
	header('Location: parametres_users.php');
	exit;
}

# Tableau des profils
$aProfils = array(
	PROFIL_ADMIN => L_PROFIL_ADMIN,
	PROFIL_MANAGER => L_PROFIL_MANAGER,
	PROFIL_MODERATOR => L_PROFIL_MODERATOR,
	PROFIL_EDITOR => L_PROFIL_EDITOR,
	PROFIL_WRITER => L_PROFIL_WRITER
);

# On inclut le header
include(dirname(__FILE__).'/top.php');
?>

<h2><?php echo L_CONFIG_USERS_TITLE; ?></h2>

<?php eval($plxAdmin->plxPlugins->callHook('AdminUsersTop')) # Hook Plugins ?>

<form action="parametres_users.php" method="post" id="form_users">
	<table class="table">
	<thead>
		<tr>
			<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idUser[]')" /></th>
			<th class="title"><?php echo L_CONFIG_USERS_ID ?></th>
			<th><?php echo L_PROFIL_USER ?></th>
			<th><?php echo L_PROFIL_LOGIN ?></th>
			<th><?php echo L_PROFIL_PASSWORD ?></th>
			<th><?php echo L_PROFIL ?></th>
			<th><?php echo L_CONFIG_USERS_ACTIVE ?></th>
			<th><?php echo L_CONFIG_USERS_ACTION ?></th>
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
				echo '<td><input type="checkbox" name="idUser[]" value="'.$userid.'" /><input type="hidden" name="userNum[]" value="'.$userid.'" /></td>';
				echo '<td>Utilisateur '.$userid.'</td><td>';
				plxUtils::printInput($userid.'_name', plxUtils::strCheck($user['name']), 'text', '20-255');
				echo '</td><td>';
				plxUtils::printInput($userid.'_login', plxUtils::strCheck($user['login']), 'text', '11-255');
				echo '</td><td>';
				plxUtils::printInput($userid.'_password', '', 'password', '11-255');
				echo '</td><td>';
				if($userid=='001') {
					plxUtils::printSelect($userid.'_profil', $aProfils, $user['profil'], true, 'readonly');
					echo '</td><td>';
					plxUtils::printSelect($userid.'_active', array('1'=>L_YES,'0'=>L_NO), $user['active'], true, 'readonly');
				} else {
					plxUtils::printSelect($userid.'_profil', $aProfils, $user['profil']);
					echo '</td><td>';
					plxUtils::printSelect($userid.'_active', array('1'=>L_YES,'0'=>L_NO), $user['active']);
				}
				echo '</td>';
				echo '<td><a href="user.php?p='.$userid.'">'.L_OPTIONS.'</a></td>';
				echo '</tr>';
			}
		}
		# On récupère le dernier identifiant
		$a = array_keys($plxAdmin->aUsers);
		rsort($a);
	} else {
		$a['0'] = 0;
	}
	$new_userid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
	?>
		<tr class="new">
		<td>&nbsp;</td>
			<td><?php echo L_CONFIG_USERS_NEW; ?></td>
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
				plxUtils::printSelect($new_userid.'_profil', $aProfils, PROFIL_WRITER);
				echo '</td><td>';
				plxUtils::printSelect($new_userid.'_active', array('1'=>L_YES,'0'=>L_NO), '1');
				echo '</td>';
			?>
			<td>&nbsp;</td>
		</tr>
	</tbody>
	</table>
	<p class="center">
		<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update" type="submit" name="update" value="<?php echo L_CONFIG_USERS_UPDATE ?>" />
	</p>
	<p>
		<?php plxUtils::printSelect('selection', array( '' => L_FOR_SELECTION, 'delete' => L_DELETE), '') ?>
		<input class="button submit" type="submit" name="submit" value="<?php echo L_OK ?>" />
	</p>
</form>

<?php
# Hook Plugins
eval($plxAdmin->plxPlugins->callHook('AdminUsersFoot'));
# On inclut le footer
include(dirname(__FILE__).'/foot.php');
?>