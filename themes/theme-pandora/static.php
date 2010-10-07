<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>

<h2 class="entry-title"><?php $plxShow->staticTitle(); ?></h2><br />

<div class="post clear">

<div class="entry-content">
<?php $plxShow->staticContent(); ?>
</div>

</div><!-- end .post -->
<div class="clear hr" style="width: 620px; height: 10px;"></div>

	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>