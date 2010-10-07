<?php if(!defined('PLX_ROOT')) exit; ?>
</div><!-- end.posts-wrap -->
<div class="sidebar-wrap">

		<div class="widget">
                       <h3 class="widgettitle">Cat&eacute;gories</h3>
                       <div class="widget-bg">
		         <ul>
			<?php $plxShow->catList('','<li id="#cat_id" class="#cat_status"><a href="#cat_url" title="#cat_name">#cat_name (#art_nb)</a></li>'); ?>
                         </ul>		
                       </div>
                </div><div class="widget-footer"></div>


		<div class="widget">
                       <h3 class="widgettitle">Derniers articles</h3>
                       <div class="widget-bg">
		         <ul>
			<?php $plxShow->lastArtList('<li class="#art_status"><a href="#art_url" title="#art_title">#art_title</a></li>'); ?>
                         </ul>		
                       </div>
                </div><div class="widget-footer"></div>


		<div class="widget">
                       <h3 class="widgettitle">Derniers commentaires</h3>
                       <div class="widget-bg">
		         <ul>
			<?php $plxShow->lastComList('<li><a href="#com_url"><strong>#com_author a dit : </strong> #com_content(20)</a></li>'); ?>
                         </ul>		
                       </div>
                </div><div class="widget-footer"></div>



		<div class="widget">
                       <h3 class="widgettitle">Archives</h3>
                       <div class="widget-bg">
		         <ul>
			<?php $plxShow->archList('<li id="#archives_id" class="#archives_status"><a href="#archives_url" title="#archives_name">#archives_name (#archives_nbart)</a></li>'); ?>
                         </ul>		
                       </div>
                </div><div class="widget-footer"></div>

		

		<div class="widget">
                       <h3 class="widgettitle">Mots cl&eacute;s</h3>
                       <div class="widget-bg">
		         <ul>
			<?php $plxShow->tagList('<li class="#tag_status"><a href="#tag_url" title="#tag_name">#tag_name</a></li>', 20); ?>
			<li class="last_li">&nbsp;</li>
                         </ul>		
                       </div>
                </div><div class="widget-footer"></div>
			

</div><!-- sidebar wrap end -->