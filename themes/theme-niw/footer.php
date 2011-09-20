<?php if(!defined('PLX_ROOT')) exit; ?>
<div id="footer">
	<p>&copy; <?php $plxShow->mainTitle('link'); ?> &bull; Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a></p>
	<p>G&eacute;n&eacute;r&eacute; par <a href="http://pluxml.org" title="Blog ou Cms sans base de donn&eacute;es">PluXml</a> 
	en <?php $plxShow->chrono(); ?> 
	<?php $plxShow->httpEncoding() ?> &bull; 
	<a href="<?php $plxShow->urlRewrite('core/admin/') ?>" title="Administration">Administration</a> &bull; 
	<a href="<?php echo $plxShow->urlRewrite('#top') ?>" title="Remonter en haut de page">Haut de page</a>
	</p>
</div>
</body>
</html>
