<?php if(!defined('PLX_ROOT')) exit; ?>
		</div>
		<!-- main END -->

	<?php include(dirname(__FILE__).'/sidebar.php'); # On insere la sidebar ?>
		<div class="fixed"></div>
	</div>
	<!-- content END -->

	<!-- footer START -->

	<div id="footer">
		<div class="content">
			<span id="about">
				&copy; <?php $plxShow->mainTitle('link'); ?> - G&eacute;n&eacute;r&eacute; par <a href="http://pluxml.org" title="Blog ou Cms sans base de donn&eacute;es">Pluxml</a> <?php $plxShow->version(); ?> en <?php $plxShow->chrono(); ?> | Theme par <a href="http://www.neoease.com/">NeoEase</a> | Valide <a href="http://validator.w3.org/check?uri=referer">XHTML 1.1</a> et <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">CSS 3</a></span>

			<a id="gotop" href="#" onclick="MGJS.goTop();return false;">Haut de page</a>
			<div class="fixed"></div>
		</div>
	</div>
	<!-- footer END -->

</div>
<!-- container END -->

</body>
</html>

