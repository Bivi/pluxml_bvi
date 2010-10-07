<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<?php while($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles ?>
			<h2 class="title"><?php $plxShow->artTitle('link'); ?></h2>
			<p class="info_top">R&eacute;dig&eacute; par <?php $plxShow->artAuthor() ?> | Class&eacute; dans : <?php $plxShow->artCat(); ?></p>
			<p class="date"><?php $plxShow->artDate('<span>#num_day</span><br />#num_month | #num_year(2)'); ?></p>
			<div class="post"><?php $plxShow->artChapo(); ?></div>
			<p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?> <span><?php $plxShow->artNbCom(); ?></span></p>
		<?php endwhile; # Fin de la boucle sur les articles ?>
		<?php # On affiche la pagination ?>
		<p id="pagination"><?php $plxShow->pagination(); ?></p>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
