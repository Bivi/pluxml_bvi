<?php include(dirname(__FILE__) . '/header.php'); # On insere le header  ?>
<?php while ($plxShow->plxMotor->plxRecord_arts->loop()): # On boucle sur les articles  ?>
  <div class="post">
    <h3 class="title"><?php $plxShow->artTitle('link'); ?> (<?php $plxShow->artDate('#num_day/#num_month/#num_year(4)'); ?>)</h3>

    <div class="content"><?php $plxShow->artChapo(); ?>
      <div class="fixed"></div>
    </div>

    <div class="meta">
      <div class="act">
      <?php $plxShow->artNbCom(); ?>
    </div>
    <div class="info">
      Class&eacute; dans : <?php $plxShow->artCat(); ?>
      <p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?></p>
    </div>
    <div class="fixed"></div>
  </div>
</div>
<?php endwhile; # Fin de la boucle sur les articles ?>
<?php # On affiche la pagination ?>
      <div id="pagenavi" class="block"><?php $plxShow->pagination(); ?></div>
<?php include(dirname(__FILE__) . '/footer.php'); # On insere le footer ?>