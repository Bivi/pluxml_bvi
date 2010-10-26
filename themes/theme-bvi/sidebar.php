<?php if(!defined('PLX_ROOT')) exit; ?>
<!-- sidebar START -->
<div id="sidebar">

	<!-- sidebar right -->
	<div id="sidebar_right">

        <!-- search box -->
        <div class="widget s">
          <form method="post" id="searchform" action="<?php $plxShow->urlRewrite('?static1/rechercher') ?>">
            <div id="searchbox">
              <input class="textfield" type="text" name="searchfield" value="" />
              <input class="button" type="submit"  value="Go"  />
              <div class="tip">Rechercher sur mon Blog</div>
              <div class="fixed"></div>
            </div>
          </form>
        </div>

		<!-- recent posts -->
		<div class="widget widget_pages">
			<h3>Derniers articles</h3>
			<ul>
			    <?php $plxShow->lastArtList('<li><a href="#art_url" class="#art_status" title="#art_title">#art_title</a></li>'); ?>
			</ul>
		</div>

		<!-- recent comments -->
			<div class="widget">
				<h3>Derniers commentaires</h3>
				<ul>
					<?php $plxShow->lastComList('<li><a href="#com_url">#com_author a dit :</a> #com_content(34)</li>'); ?>
				</ul>
			</div>

		<!-- categories -->
		<div class="widget widget_categories">
			<h3>Cat&eacute;gories</h3>
			<ul>
				<?php $plxShow->catList('','<li id="#cat_id"><a href="#cat_url" class="#cat_status" title="#cat_name">#cat_name</a></li>'); ?>
			</ul>
		</div>

	<div class="widget widget_tags">
		<h3>Mots cl&eacute;s</h3>
		<ul>
			<?php $plxShow->tagList('<li class="#tag_status"><a href="#tag_url" title="#tag_name">#tag_name</a></li>', 20); ?>
			<li class="last_li">&nbsp;</li>
		</ul>
	</div>
    <div class="widget widget_archives">
        <h3>Archives</h3>
        <ul>
            <?php $plxShow->archList('<li id="#archives_id" class="#archives_status"><a href="#archives_url" title="#archives_name">#archives_name (#archives_nbart)</a></li>'); ?>
        </ul>
    </div>	

	</div>
</div>
<!-- sidebar END -->