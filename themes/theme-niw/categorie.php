<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<?php while($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles ?>
			<div class="title">
				<h2><?php $plxShow->artTitle('link'); ?></h2>
				<p class="meta">
					<span class="author">Par <?php $plxShow->artAuthor() ?></span>, 
					<span class="date">le&nbsp;<?php $plxShow->artDate('#num_day #month #num_year(4)');?></span>
					&bull;
					<span class="comments"><?php $plxShow->artNbCom(); ?></span>
					&bull;
					<span><a href="<?php $plxShow->artUrl(); ?>">Lire la suite</a></span>
				</p>
			</div>
			<div class="post"><?php $plxShow->artChapo("Lire la suite"); ?></div>
		<?php endwhile; # Fin de la boucle sur les articles ?>
		<?php # On affiche le fil Atom de cette categorie ?>
		<div class="feed"><?php $plxShow->artFeed('rss',$plxShow->catId()); ?></div>
		<?php # On affiche la pagination ?>
		<p id="pagination"><?php $plxShow->pagination(); ?></p>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>