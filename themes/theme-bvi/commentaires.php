<?php if(!defined('PLX_ROOT')) exit; ?>
<script type="text/javascript" src="<?php $plxShow->template(); ?>/js/comment.js"></script>
<?php # Si on a des commentaires ?>
<?php if($plxShow->plxMotor->plxGlob_coms->count): ?>
	<ol class="commentlist">
		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>	
			<li id="comment-<?php $plxShow->comId(); ?>" class="comment">
			<div class="header regularheader">
				<img alt='' src='' class='avatar avatar-24 photo' height='24' width='24' />	
				<div class="author with_avatar"><span id="commentauthor-<?php $plxShow->comId(); ?>"><?php $plxShow->comAuthor('link'); ?></span></div>
				<div class="items"></div>
				<div class="date">
				Le <?php $plxShow->comDate('#num_day #month #num_year(4)'); ?> | <a href="#comment-<?php $plxShow->comId(); ?>">#<?php $plxShow->comId(); ?></a>
				</div>
				<div class="fixed"></div>
			</div>
			<div class="body" id="commentbody-<?php $plxShow->comId(); ?>">
				<p><?php $plxShow->comContent() ?></p>
			</div>
			<div class="fixed"></div>			
			</li>
		<?php endwhile; # Fin de la boucle sur les commentaires ?>			
	</ol>


<?php endif; # Fin du if sur la prescence des commentaires ?>
<?php # Si on autorise les commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
	<div id="respond">
	<form id="commentform" action="<?php $plxShow->artUrl(); ?>#commentform" method="post">
		<div class="body">
			<div class="header">
				<h3 class="title">Ecrire un commentaire</h3>
				<div class="fixed"></div>
			</div>
			<div class="notice">
				<?php $plxShow->comMessage(); ?>
			</div>

			<div class="text"><textarea name="content" id="comment" class="textarea" cols="64" rows="8" tabindex="5"><?php $plxShow->comGet('content',''); ?></textarea></div>
			<div class="info">

				<div class="part">

					<div id="author_info">
						<div><label for="author" class="small">Nom&nbsp;</label></div>
						<div><input type="text" class="textfield" name="name" id="author" value="<?php $plxShow->comGet('name',''); ?>" tabindex="1" /></div>
						<div><label for="email" class="small">E-mail (facultatif)&nbsp;</label></div>
						<div><input type="text" class="textfield" name="mail" id="email" value="<?php $plxShow->comGet('mail',''); ?>" tabindex="2" /></div>
						<div><label for="url" class="small">Site (facultatif)&nbsp;</label></div>
						<div><input type="text" class="textfield" name="site" id="url" value="<?php $plxShow->comGet('site','http://'); ?>" tabindex="3" /></div>
					<?php # Affichage du capcha anti-spam
					if($plxShow->plxMotor->aConf['capcha']): ?>						
						<div><label for="rep" class="small"><?php $plxShow->capchaQ(); ?></label></div>
						<div><input type="text" class="textfield" name="rep" id="rep" size="10" tabindex="4" />
						     <input name="rep2" type="hidden" value="<?php $plxShow->capchaR(); ?>" /></div>
					<?php endif; # Fin du if sur le capcha anti-spam ?>						     
					</div>

				</div>


				<div class="part">
					<input name="submit" type="submit" id="submit" class="button" tabindex="6" value="Envoyer" />
				</div>

				<div class="feed">
		<?php # On affiche le fil Atom de cet article ?>
		<?php $plxShow->comFeed('atom',$plxShow->artId()); ?>
				</div>
			</div>

			<div class="fixed"></div>
		</div>
	</form>
	</div>
<?php endif; # Fin du if sur l'autorisation des commentaires ?>
