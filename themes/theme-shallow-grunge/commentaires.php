<?php if(!defined('PLX_ROOT')) exit; ?>
<?php # Si on a des commentaires ?>
<?php if($plxShow->plxMotor->plxGlob_coms->count): ?>
	<div id="comments">
		<h2>Commentaires</h2>
		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>
			<div id="<?php $plxShow->comId(); ?>" class="comment type-<?php $plxShow->comType(); ?>">
				<div class="meta">
					<p><a href="<?php $plxShow->ComUrl() ?>" title="#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?>">#<?php echo $plxShow->plxMotor->plxRecord_coms->i+1 ?></a>
					&bull;
					Le <?php $plxShow->comDate('#num_day #month #num_year(4) &agrave; #hour:#minute'); ?>, <?php $plxShow->comAuthor('link'); ?> a dit :</p>
				</div>
				<blockquote>
					<p><?php $plxShow->comContent() ?></p>
				</blockquote>
			</div>
		<?php endwhile; # Fin de la boucle sur les commentaires ?>
		<div class="feed"><?php $plxShow->comFeed('rss',$plxShow->artId()); ?></div>
	</div>
<?php endif; # Fin du if sur la prescence des commentaires ?>
<?php # Si on autorise les commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
	<div id="comments-form">
		<h2>Ecrire un commentaire</h2>
		<p class="message"><?php $plxShow->comMessage(); ?></p>
		<form action="<?php $plxShow->artUrl(); ?>#comments-form" method="post">
			<fieldset>
				<p>
					<label>Nom</label>
					<input name="name" type="text" size="20" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" /><br />
				</p>
				<p>
					<label>Site (facultatif)</label>
					<input name="site" type="text" size="20" value="<?php $plxShow->comGet('site',''); ?>" /><br />
				</p>
				<p>
					<label>E-mail (facultatif)</label>
					<input name="mail" type="text" size="20" value="<?php $plxShow->comGet('mail',''); ?>" /><br />
				</p>
				<p>
					<textarea name="content" cols="35" rows="6"><?php $plxShow->comGet('content',''); ?></textarea>
				</p>
				<p class="button">
					<?php # Affichage du capcha anti-spam
					if($plxShow->plxMotor->aConf['capcha']): ?>
						<?php $plxShow->capchaQ(); ?>&nbsp;<input name="rep" type="text" size="10" />
						<input name="rep2" type="hidden" value="<?php $plxShow->capchaR(); ?>" />
					<?php endif; # Fin du if sur le capcha anti-spam ?>
					<span class="buttons"><input type="reset" value="Effacer" />&nbsp;&nbsp;&nbsp;<input type="submit" value="Envoyer" /></span>
				</p>
			</fieldset>
		</form>
	</div>
<?php endif; # Fin du if sur l'autorisation des commentaires ?>