<?php include(dirname(__FILE__) . '/header.php'); # On insere le header  ?>
<div class="post">
  <h3 class="title"><?php $plxShow->artTitle('link'); ?> (<?php $plxShow->artDate('#num_day/#num_month/#num_year(4)'); ?>)</h3>

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
      Class&eacute; dans : <?php $plxShow->artCat(); ?>
      <?php echo '&nbsp;    [<a href="core/admin/article.php?a='.$plxShow->plxMotor->plxRecord_arts->f('numero').'" title="&Eacute;diter cet article" target="_blank">Editer</a>]' ?>
      <p class="info_bottom">Mots cl&eacute;s : <?php $plxShow->artTags(); ?></p>
    </div>
    <div class="fixed"></div>
  </div>
</div>
<?php include(dirname(__FILE__) . '/commentaires.php'); # On insere les commentaires ?>
<?php include(dirname(__FILE__) . '/footer.php'); # On insere le footer ?>