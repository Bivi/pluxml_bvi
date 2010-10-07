<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>

<h2 class="entry-title"><?php $plxShow->artTitle(''); ?></h2><br />
<div class="entry-title-meta-left"></div>
<div class="entry-title-meta"><span class="date">Le <?php $plxShow->artDate('#day #num_day #month #num_year(4)'); ?>  par <?php $plxShow->artAuthor() ?></span> <span class="category"><?php $plxShow->artCat(); ?></span> <span class="entry-header-comments"><?php $plxShow->artNbCom(); ?></span></div>
<div class="entry-title-meta-right"></div>

<div class="post clear" id="post-<?php echo $plxShow->artId() ?>">

<div class="entry-content">
<?php $plxShow->artContent(); ?>
		<p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?></p>
		<?php $plxShow->artAuthorInfos('<div class="infos">#art_authorinfos</div>'); ?>
</div>

</div><!-- end .post -->
<div class="clear hr" style="width: 620px; height: 10px;"></div>

		<?php # On affiche la pagination ?>

<div class="navigation" id="nav-single">
	<?php $plxShow->pagination(); ?>
</div>
		<?php include(dirname(__FILE__).'/commentaires.php'); # On insere les commentaires ?>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>