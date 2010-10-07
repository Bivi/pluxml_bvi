<!--
function _plxToolbar() {

	this.customButtons = new Array();
	
	this.addButton = function(customButton) {
		this.customButtons.push(customButton);
	}
	this.insert = function(textarea, tag_open, tag_close, qst, msg) {
		if((answer = (qst ? prompt(qst, msg) : '')) == null) return;		
		switch (tag_open) {
			case "<a>":
				tag_open = '<a href="'+answer+'">';
				break;
			case "<left>":
				tag_open = '\n<p style="text-align:left">';
				tag_close = '</p>';
				break;
			case "<center>":
				tag_open = '\n<p style="text-align:center">';
				tag_close = '</p>';
				break;
			case "<right>":
				tag_open = '\n<p style="text-align:right">';
				tag_close = '</p>';
				break;
		}
		addText(textarea, tag_open, tag_close);
	}	
	this.doToolbar = function(textarea, origine, mini) {
		var url = window.location.pathname;
		var toolbar = '';
		if(mini=='mini') {
			toolbar += '<input class="bold" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<strong>\',\'<\/strong>\')" title="Texte en gras" \/>';		
			toolbar += '<input class="link" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<a>\',\'<\/a>\',\'Veuillez entrer une adresse\', \'http://www.\')" title="Lien" \/>';
		} else {
			toolbar += '<input class="p" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'\\n<p>\',\'<\/p>\')" title="Paragraphe" \/>';
			toolbar += '<input class="h2" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<h2>\',\'<\/h2>\')" title="Titre H2" \/>';
			toolbar += '<input class="h3" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<h3>\',\'<\/h3>\')" title="Titre H3" \/>';
			toolbar += '<input class="h4" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<h4>\',\'<\/h4>\')" title="Titre H4" \/>';
			toolbar += '<input class="h5" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<h5>\',\'<\/h5>\')" title="Titre H5" \/>';
			toolbar += '<input class="bold" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<strong>\',\'<\/strong>\')" title="Texte en gras" \/>';
			toolbar += '<input class="italic" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<em>\',\'<\/em>\')" title="Texte en italic" \/>';
			toolbar += '<input class="underline" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<ins>\',\'<\/ins>\')" title="Texte soulign&eacute;" \/>';
			toolbar += '<input class="strike" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<del>\',\'\<\/del>\')" title="Texte barr&eacute;" \/>';
			toolbar += '<input class="link" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<a>\',\'<\/a>\',\'Veuillez entrer une adresse\', \'http://www.\')" title="Lien" \/>';
			toolbar += '<input class="br" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<br />\\n\',\'\')" title="Retour &agrave; la ligne" \/>';
			toolbar += '<input class="hr" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<hr />\\n\',\'\')" title="Ligne horizontale" \/>';
			toolbar += '<input class="ul" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'\\n<ul>\\n<li>\',\'<\/li>\\n<\/ul>\')" title="Liste &agrave; puce" \/>';
			toolbar += '<input class="ol" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'\\n<ol>\\n<li>\',\'<\/li>\\n<\/ol>\')" title="Liste num&eacute;rot&eacute;e" \/>';
			toolbar += '<input class="blockquote" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'\\n<blockquote>\',\'<\/blockquote>\')" title="Retrait" \/>';
			toolbar += '<input class="p_left" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<left>\',\'\')" title="Texte &agrave; gauche" \/>';
			toolbar += '<input class="p_center" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<center>\',\'\')" title="Texte centr&eacute;" \/>';
			toolbar += '<input class="p_right" type="button" onclick="plxToolbar.insert(\''+textarea+'\',\'<right>\',\'\')" title="Texte &agrave; droite" \/>';
			toolbar += '<input class="media" type="button" onclick="openPopup(\'medias.php?v='+textarea+'\',\'M&eacute;dias\',\'750\',\'520\');return false;" title="Gestionnaire de m&eacute;dias" \/>';
			if(!url.match(new RegExp("fullscreen.php","gi"))) {
				toolbar += '<input class="fullscreen" type="button" onclick="openPopup(\'fullscreen.php?v='+textarea+'&amp;o='+origine+'\',\'Fullscreen\', screen.width, screen.height);return false;" title="Plein &eacute;cran" \/>';
			}			
			for(i=0;i<this.customButtons.length;i++){
				toolbar += '<input style="background:url('+this.customButtons[i].icon+') no-repeat;" class="button" type="button" onclick="plxToolbar.insert(\''+textarea+'\', plxToolbar.customButtons['+i+'].onclick(\''+textarea+'\'),\'\')" title="'+this.customButtons[i].title+'" \/>';
			}
		}
		return toolbar;
	}
	this.addToolbar = function(textarea, origine, mini) {
		var obj = document.getElementById('id_'+textarea);
		var p = document.createElement('p');
		p.setAttribute("class","plxtoolbar");
		p.setAttribute("className","plxtoolbar"); /* Hack IE */
		p.innerHTML = this.doToolbar(textarea, origine, mini);
		var html = obj.parentNode;
		html.insertBefore(p,obj);
	}
	this.init = function() {
		var url = window.location.pathname;
		var mini = '';
		if(url.match(new RegExp("commentaire.php","gi"))) 
			mini='mini';
		var textareas = document.getElementsByTagName("textarea");
		for(var i=0;i<textareas.length;i++){ 
			this.addToolbar(textareas[i].name,'article',mini);
		}
	}
}
var plxToolbar = new _plxToolbar();
-->