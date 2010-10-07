<?php if(!defined('PLX_ROOT')) exit; ?>
<?php
if(!empty($_GET['a'])) $artId = $_GET['a'];
else $artId = '[0-9]{4}';

$NbComsOffline = 0;
if ($aComFiles = $plxAdmin->plxGlob_coms->query('/^_'.$artId.'.(.*).xml$/'))
	$NbComsOffline = sizeof($aComFiles);

$NbComsOnline = 0;
if ($aComFiles = $plxAdmin->plxGlob_coms->query('/^'.$artId.'.(.*).xml$/'))
	$NbComsOnline = sizeof($aComFiles);
?>
<ul>
	<li><a href="commentaires_offline.php?page=1<?php echo (!empty($_GET['a'])?'&amp;a='.$_GET['a']:'') ?>" id="link_commentaires_offline" title="Liste des commentaires en attente de validation">Commentaires en attente de validation</a> (<?php echo $NbComsOffline ?>)</li>
	<li><a href="commentaires_online.php?page=1<?php echo (!empty($_GET['a'])?'&amp;a='.$_GET['a']:'') ?>" id="link_commentaires_online" title="Liste des commentaires publi&eacute;s">Commentaires publi&eacute;s</a> (<?php echo $NbComsOnline ?>)</li>
	<?php if(!empty($_GET['a'])) : ?>
		<li><a href="commentaire_new.php?a=<?php echo $_GET['a'] ?>" id="link_commentaire_new" title="R&eacute;diger un nouveau commentaire pour cet article">R&eacute;diger un nouveau commentaire</a></li>
	<?php endif; ?>
</ul>