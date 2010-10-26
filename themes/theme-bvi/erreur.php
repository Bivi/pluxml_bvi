<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
	<div class="post">
		<h3 class="title">Une erreur a &eacute;t&eacute; d&eacute;tect&eacute;e :</h3>

		<div class="content">
			<p style="text-align:center"><?php $plxShow->erreurMessage(); ?></p>
			<p style="text-align:center"><a href="./" title="Accueil du site">Retour page d'accueil</a></p>
				<div class="fixed"></div>
		</div>

	</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
