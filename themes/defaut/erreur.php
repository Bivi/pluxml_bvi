<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<h2 class="title">Une erreur a &eacute;t&eacute; d&eacute;tect&eacute;e :</h2>
		<div class="post">
			<p class="center"><?php $plxShow->erreurMessage(); ?></p>
			<p class="center"><a href="./" title="Accueil du site">Retour page d'accueil</a></p>
		</div>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
