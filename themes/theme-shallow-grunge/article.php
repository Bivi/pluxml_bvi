<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
<div id="page">
	<div id="content">
		<div class="title">
			<h1><?php $plxShow->artTitle(''); ?></h1>
			<p class="meta">
				<span class="author">Par <?php $plxShow->artAuthor() ?></span>, 
				<span class="date">le&nbsp;<?php $plxShow->artDate('#num_day #month #num_year(4)');?></span>
				&bull;
				<span class="comments"><?php $plxShow->artNbCom(); ?></span>
			</p>
		</div>
		<div class="post"><?php $plxShow->artContent(); ?></div>
		<?php $plxShow->artAuthorInfos('<div class="infos">#art_authorinfos</div>'); ?>
		<div class="meta bottom">
			<div class="tags">Mots cl&eacute;s&nbsp;: <?php $plxShow->artTags('<a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>'); ?></div>
			<div class="categories">Class&eacute; dans&nbsp;: <?php $plxShow->artCat(); ?></div>
		</div>
		<?php include(dirname(__FILE__).'/commentaires.php'); # On insere les commentaires ?>
	</div>
	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>