<?php if(!defined('PLX_ROOT')) exit; ?>
<?php # Si on a des commentaires ?>
<?php if($plxShow->plxMotor->plxGlob_coms->count): ?>
<div class="commentlist-top"></div>
<ul class="commentlist">

<li style="background: none; border: none; padding: 0; margin: 0;"><h3 id="comments">Commentaires</h3></li>
		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>
<li class="comment even thread-even depth-<?php $plxShow->comType(); ?>" id="comment-<?php $plxShow->comId(); ?>">

    <div class="gravatar-wrap">
     <img src="http://www.gravatar.com/avatar.php?gravatar_id=<?php echo md5( strtolower($plxShow->plxMotor->plxRecord_coms->f('mail')) ) ?>&amp;default=http://www.gravatar.com/avatar/3b3be63a4c2a439b013787725dfce802.jpg&amp;size=44" alt="Avatar Gravatar" class="avatar avatar-44 photo" height="44" width="44" />
    </div>

<span class="left-meta left">
  <span class="comment-author"><span>&nbsp;<?php $plxShow->comAuthor('link'); ?></span></span><br />
  <span class="comment-meta">&nbsp;<a href="<?php $plxShow->ComUrl() ?>" title="#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?>">#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?></a>&nbsp;Le <?php $plxShow->comDate('#num_day #month #num_year(4)'); ?> </span>
</span>
<span class="reply right"></span>

<div style="background:#EEEEEE;margin:0 0 5px;padding:10px 10px 5px;">
<p><?php $plxShow->comContent() ?></p>
</div>


</li>
		<?php endwhile; # Fin de la boucle sur les commentaires ?>
</ul>

<div class="navigation comment-nav">
		<?php # On affiche le fil Atom de cet article ?>
		<div class="feed_article"><?php $plxShow->comFeed('atom',$plxShow->artId()); ?></div>
</div>

<?php endif; # Fin du if sur la prescence des commentaires ?>
<?php # Si on autorise les commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
<ul class="respond-ul">
<li id="respond">

<h2 id="leaveareply">Ecrire un commentaire</h2>

		<form action="<?php $plxShow->artUrl(); ?>#form" method="post" id="commentform">
		        <div class="divform">
				<div><input name="name" type="text" size="20" value="<?php $plxShow->comGet('name','Nom'); ?>" maxlength="30" tabindex="1" /></div><br /><br /><br />
			        <div><input name="site" type="text" size="20" value="<?php $plxShow->comGet('site','Site (facultatif)'); ?>" tabindex="2" /></div><br /><br /><br />
				<div><input name="mail" type="text" size="20" value="<?php $plxShow->comGet('mail','E-mail (facultatif)'); ?>" tabindex="3" /><div>
			</div>

                <div class="textarea-top"></div>
                  <div><textarea name="content" id="comment" cols="50" rows="10" tabindex="4"><?php $plxShow->comGet('content',''); ?></textarea></div>
                <div class="textarea-bottom"></div>	

					<?php # Affichage du capcha anti-spam
					if($plxShow->plxMotor->aConf['capcha']): ?>
						<br /><?php $plxShow->capchaQ(); ?><br /><input name="rep" type="text" size="10" tabindex="5" />
						<input name="rep2" type="hidden" value="<?php $plxShow->capchaR(); ?>" />
					<?php endif; # Fin du if sur le capcha anti-spam ?>
					
				<div class="submit-button">
					<input style="border:none;" type="submit" id="submit" tabindex="6" value="" />
				</div>	
		</form>

</li><!-- end #respond -->

</ul>
<div class="commentlist-bottom"></div>
<?php endif; # Fin du if sur l'autorisation des commentaires ?>