<?php if(!defined('PLX_ROOT')) exit; ?>
	</div>
	<div id="footer">
		<p>G&eacute;n&eacute;r&eacute; par <a href="http://pluxml.org">PluXml</a> version <?php echo $plxAdmin->version; ?></p>
	</div>	
</div>
<?php if(method_exists($plxAdmin->editor, 'addFooter')) $plxAdmin->editor->addFooter(); ?>
</body>
</html>
