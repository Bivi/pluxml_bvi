<?php if(!defined('PLX_ROOT')) exit; ?>
<ul>
	<li><a href="parametres_base.php" id="link_config1" title="Modifier la configuration de base de votre PluXml">Configuration de base</a></li>
	<li><a href="parametres_affichage.php" id="link_view" title="Modifier les options d'affichage de votre PluXml">Options d'affichages</a></li>
	<li><a href="parametres_users.php" id="link_users" title="G&eacute;rer les comptes utilisateurs de votre PluXml">Comptes utilisateurs</a></li>
	<li><a href="parametres_avances.php" id="link_config2" title="Modifier la configuration avanc&eacute;e de votre PluXml">Configuration avanc&eacute;e</a></li>
	<li><a href="parametres_infos.php" id="link_info" title="Avoir des informations sur votre PluXml">Informations</a></li>
	<li><a href="parametres_version.php?hash=<?php echo $_SESSION['hash']; ?>" id="link_check" title="V&eacute;rifier la version officielle sur PluXml.org">V&eacute;rifier la version officielle</a></li>
</ul>