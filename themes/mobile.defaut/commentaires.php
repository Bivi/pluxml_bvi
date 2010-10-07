<?php if(!defined('PLX_ROOT')) exit; ?>
<?php # Si on a des commentaires ?>
<?php if($plxShow->plxMotor->plxGlob_coms->count): ?>
	<div id="comments">
		<h2>Commentaires</h2>
		<?php while($plxShow->plxMotor->plxRecord_coms->loop()): # On boucle sur les commentaires ?>
			<div id="<?php $plxShow->comId(); ?>" class="comment type-<?php $plxShow->comType(); ?>">
				<div class="info_comment">
					<p>Par <?php $plxShow->comAuthor('link'); ?> 
					le <?php $plxShow->comDate('#num_day/#num_month/#num_year(2) &agrave; #hour:#minute'); ?></p>
				</div>
				<blockquote><p><?php $plxShow->comContent() ?></p></blockquote>
			</div>
		<?php endwhile; # Fin de la boucle sur les commentaires ?>
		<?php # On affiche le fil Atom de cet article ?>
		<div class="feeds"><?php $plxShow->comFeed('atom',$plxShow->artId()); ?></div>
	</div>
<?php endif; # Fin du if sur la prescence des commentaires ?>	
<?php # Si on autorise les commentaires ?>
<?php if($plxShow->plxMotor->plxRecord_arts->f('allow_com') AND $plxShow->plxMotor->aConf['allow_com']): ?>
	<div id="form">
		<h2>Ecrire un commentaire</h2>
		<p class="message_com"><?php $plxShow->comMessage(); ?></p>
		<form action="<?php $plxShow->artUrl(); ?>#form" method="post">
			<fieldset>
				<label>Nom&nbsp;:</label>
				<input name="name" type="text" size="30" value="<?php $plxShow->comGet('name',''); ?>" maxlength="30" /><br />
				<label>Site (facultatif)&nbsp;:</label>
				<input name="site" type="text" size="30" value="<?php $plxShow->comGet('site','http://'); ?>" /><br />
				<label>E-mail (facultatif)&nbsp;:</label>
				<input name="mail" type="text" size="30" value="<?php $plxShow->comGet('mail',''); ?>" /><br />
				<label>Commentaire&nbsp;:</label>
				<textarea name="content" cols="35" rows="8"><?php $plxShow->comGet('content',''); ?></textarea>
				<?php # Affichage du capcha anti-spam
				if($plxShow->plxMotor->aConf['capcha']): ?>
					<label><strong>V&eacute;rification anti-spam</strong>&nbsp;:</label>
					<p><?php $plxShow->capchaQ(); ?>&nbsp;:&nbsp;<input name="rep" type="text" size="10" /></p>
					<input name="rep2" type="hidden" value="<?php $plxShow->capchaR(); ?>" />
				<?php endif; # Fin du if sur le capcha anti-spam ?>
				<p><input type="submit" value="Envoyer" />&nbsp;<input type="reset" value="Effacer" /></p>
			</fieldset>
		</form>
	</div>
<?php endif; # Fin du if sur l'autorisation des commentaires ?>