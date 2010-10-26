<?php include(dirname(__FILE__).'/header.php'); # On insere le header ?>
	<div class="post">
		<h3 class="title"><?php $plxShow->staticTitle(); ?></h3>

		<div class="content">
			<?php $plxShow->staticContent(); ?>
			<div class="fixed"></div>
		</div>

	</div>
<?php include(dirname(__FILE__).'/footer.php'); # On insere le footer ?>
