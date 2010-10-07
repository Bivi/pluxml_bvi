<!--
function dateNow() {
	var now = new Date();
	var y = now.getFullYear();
	var m = now.getMonth();
	var d = now.getDate();
	var h = now.getHours();
	var i = now.getMinutes();	
	if(i <= 9){i = '0'+i;}
	if(h <= 9){h = '0'+h;}
	if(d <= 9){d = '0'+d;}
	m = m+1;
	if(m <= 9){m = '0'+m;}
	document.getElementsByName('day')['0'].value = d;
	document.getElementsByName('time')['0'].value = h+":"+i;
	document.getElementsByName('month')['0'].value = m;
	document.getElementsByName('year')['0'].value = y;
}
function openPopup(fichier,nom,width,height) {
	var popup = window.open(unescape(fichier) , nom, "directories=no, toolbar=no, menubar=no, location=no, resizable=yes, scrollbars=yes, width="+width+" , height="+height);
	if(popup) {
		popup.focus();
	} else {
		alert('Ouverture de la fenêtre bloquée par un anti-popup!');
	}
	return;
}
function answerCom(where,id,author) {
	addText(where, '<a href="#c'+id+'">@'+author+'</a> :\n');
	scrollTo(0,0);
}
function checkAll(inputs, field) {
	for(var i = 0; i < inputs.elements.length; i++) {
		if(inputs[i].type == "checkbox" && inputs[i].name==field) {
			inputs[i].checked = !inputs[i].checked ;
		}
	}
}
function toggleTR(link, id) {
	var text = document.getElementById(link);
	var tr = document.getElementById(id).style.display;
	if (tr == 'table-row') {
		document.getElementById(id).style.display = 'none';
		text.innerHTML = 'Options';
	} else {
		document.getElementById(id).style.display = 'table-row';
		text.innerHTML = 'Masquer';
	}
}
function addText(where, open, close) {
	close = close==undefined ? '' : close;
	var formfield = document.getElementsByName(where)['0'];
	// IE support
	if (document.selection && document.selection.createRange) {
		formfield.focus();
		sel = document.selection.createRange();
		sel.text = open + sel.text + close;
		formfield.focus();
	}
	// Moz support
	else if (formfield.selectionStart || formfield.selectionStart == '0') {
		var startPos = formfield.selectionStart;
		var endPos = formfield.selectionEnd;
		var restoreTop = formfield.scrollTop;
		formfield.value = formfield.value.substring(0, startPos) + open + formfield.value.substring(startPos, endPos) + close + formfield.value.substring(endPos, formfield.value.length);
		formfield.selectionStart = formfield.selectionEnd = endPos + open.length + close.length;
		if (restoreTop > 0) formfield.scrollTop = restoreTop;
		formfield.focus();
	}
	// Fallback support for other browsers
	else {
		formfield.value += open + close;
		formfield.focus();
	}
	return;
}
function insImg(where, src) {
	if(src.substr(-3)=='.tb')
		addText(where, '<a href="'+src.substr(0,src.length-3)+'"><img src="'+src+'" alt="" /></a>');
	else
		addText(where, '<img src="'+src+'" alt="" />');
}
function insDoc(where, src, title, download) {
	if(download=='1')
		addText(where, '<a href="./?telechargement/'+src+'">'+title+'</a>');
	else
		addText(where, src);	
}
-->