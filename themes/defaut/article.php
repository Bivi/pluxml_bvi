<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<h2 class="title"><?php $plxShow->artTitle(''); ?></h2>
		<p class="info_top">R&eacute;dig&eacute; par <?php $plxShow->artAuthor() ?> | Class&eacute; dans : <?php $plxShow->artCat(); ?></p>
		<p class="date"><?php $plxShow->artDate('<span>#num_day</span><br />#num_month | #num_year(2)'); ?></p>		
		<div class="post"><?php $plxShow->artContent(); ?></div>
		<p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?></p>
		<?php $plxShow->artAuthorInfos('<div class="infos">#art_authorinfos</div>'); ?>
		<?php include(dirname(__FILE__).'/commentaires.php'); # On insere les commentaires ?>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
