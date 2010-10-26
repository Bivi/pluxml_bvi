<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
		<?php while($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles ?>
	<div class="post">
		<h3 class="title"><?php $plxShow->artTitle('link'); ?></h3>

		<div class="content"><?php $plxShow->artChapo(); ?>
			<div class="fixed"></div>
		</div>

		<div class="meta">
			<div class="act">
				<?php $plxShow->artNbCom(); ?>
			</div>
			<div class="info">
				Le <?php $plxShow->artDate('<span>#num_day</span> #num_month #num_year(4)'); ?> | Class&eacute; dans : <?php $plxShow->artCat(); ?> 
			</div>
			<div class="fixed"></div>
		</div>
	</div>
		<?php endwhile; # Fin de la boucle sur les articles ?>
		<?php # On affiche le fil Atom des articles de cette categorie ?>
		<div class="feed_categorie"><?php $plxShow->artFeed('atom',$plxShow->catId()); ?></div>
		<?php # On affiche la pagination ?>
			<div id="pagenavi" class="block"><?php $plxShow->pagination(); ?></div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
