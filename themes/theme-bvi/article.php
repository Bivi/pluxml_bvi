<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
	<div class="post">
		<h3 class="title"><?php $plxShow->artTitle('link'); ?></h3>

		<div class="content">
			<?php $plxShow->artContent(); ?>
			<div class="fixed"></div>
		</div>

		<div class="meta">
				<!-- <div class="author"></div> //Option pour gravatar -->

			<div class="act">
				<!-- <a href="#respond">Laisser un Commentaire</a> -->
			</div>
			<div class="info">
				Le <?php $plxShow->artDate('<span>#num_day</span> #num_month #num_year(4)'); ?> | Class&eacute; dans : <?php $plxShow->artCat(); ?>
			</div>
			<div class="fixed"></div>
		</div>
	</div>
		<?php include(dirname(__FILE__).'/commentaires.php'); # On insere les commentaires ?>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>