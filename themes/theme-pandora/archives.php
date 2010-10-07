<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
		<?php while($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles ?>

<h2 class="entry-title"><?php $plxShow->artTitle('link'); ?></h2><br />
<div class="entry-title-meta-left"></div>
<div class="entry-title-meta"><span class="date">Le <?php $plxShow->artDate('#day #num_day #month #num_year(4)'); ?>  par <?php $plxShow->artAuthor() ?></span> <span class="category"><?php $plxShow->artCat(); ?></span> <span class="entry-header-comments"><?php $plxShow->artNbCom(); ?></span></div>
<div class="entry-title-meta-right"></div>

<div class="post clear" id="post-<?php echo $plxShow->artId() ?>">

<div class="entry-content">
<?php $plxShow->artChapo(); ?>
<p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?></p>
</div>

</div><!-- end .post -->
<div class="clear hr" style="width: 620px; height: 10px;"></div>

		<?php endwhile; # Fin de la boucle sur les articles ?>
		<?php # On affiche le fil Atom des articles de cette categorie ?>
		<div class="feed_categorie"><?php $plxShow->artFeed('atom',$plxShow->catId()); ?></div>		
		<?php # On affiche la pagination ?>

<div class="paged-navigation">
<?php $plxShow->pagination(); ?>
</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>