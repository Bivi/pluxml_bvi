<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>

<h1>Une erreur a &eacute;t&eacute; d&eacute;tect&eacute;e :</h1>

<div class="post clear">

<div class="entry-content">
<?php $plxShow->erreurMessage(); ?>
<p><a href="./" title="Accueil du site">Retour page d'accueil</a></p>
</div>

</div><!-- end .post -->
<div class="clear hr" style="width: 620px; height: 10px;"></div>

	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>