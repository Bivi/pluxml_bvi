<?php

/**
 * Classe plxUtils rassemblant les fonctions utiles à PluXml
 *
 * @package PLX
 * @author	Florent MONTHEL et Stephane F
 **/
class plxUtils {

	/**
	 * Méthode qui retourne un tableau contenu les paramètres passés dans l'url de la page courante
	 *
	 * @return	array	tableau avec les paramètres passés dans l'url de la page courante
	 **/
	public static function getGets() {

		if(!empty($_GET)) {
			$a = array_keys($_GET);
			return strip_tags($a[0]);
		}
		return false;
	}

	/**
	 * Méthode qui supprime les antislashs
	 *
	 * @param	content				variable ou tableau 
	 * @return	array ou string		tableau ou variable avec les antislashs supprimés
	 **/
	public static function unSlash($content) {

		if(get_magic_quotes_gpc() == 1) {
			if(is_array($content)) { # On traite un tableau
				foreach($content as $k=>$v) { # On parcourt le tableau
					if(is_array($v)) {
						foreach($v as $key=>$val)
							$new_content[$k][$key] = stripslashes($val);
					} else {
						$new_content[ $k ] = stripslashes($v);
					}
				}
			} else { # On traite une chaine
				$new_content = stripslashes($content);
			}
			# On retourne le tableau modifie
			return $new_content;
		} else {
			return $content;
		}
	}

	/**
	 * Méthode qui vérifie le bon formatage d'une adresse email
	 *
	 * @param	mail		adresse email à vérifier
	 * @return	boolean		vrai si adresse email bien formatée
	 **/
	public static function checkMail($mail) {

		# On verifie le mail via une expression reguliere
		if(preg_match('/^[-+.\w]{1,64}@[-.\w]{1,64}\.[-.\w]{2,6}$/i',$mail))
			return true;
		else
			return false;
	}

	/**
	 * Méthode qui vérifie si l'url passée en paramètre correspond à un format valide
	 *
	 * @param	site		url d'un site
	 * @return	boolean		vrai si l'url est bien formatée
	 **/
	public static function checkSite($site) {

		# On verifie le site via une expression reguliere
		if(preg_match('/^http(s)?:\/\/[-.\w]{1,64}\.[-.\w]{2,6}/i',$site))
			return true;
		else
			return false;
	}

	/**
	 * Méthode qui retourne l'adresse ip d'un visiteur
	 *
	 * @return	string		adresse ip d'un visiteur
	 **/
	public static function getIp() {

		return  $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Méthode qui affiche une liste de sélection
	 *
	 * @param	name		nom de la liste
	 * @param	array		valeurs de la liste sous forme de tableau (nom, valeur)
	 * @param	selected	valeur par défaut
	 * @param	readonly	vrai si la liste est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @return	stdout
	 **/
	public static function printSelect($name, $array, $selected='', $readonly=false, $class='') {

		if($readonly)
			echo '<select id="id_'.$name.'" name="'.$name.'" disabled="disabled" class="readonly">'."\n";
		else
			echo '<select id="id_'.$name.'" name="'.$name.'"'.($class!=''?' class="'.$class.'"':'').'>'."\n";			
		foreach($array as $a => $b) {
			if(is_array($b)) {
				echo '<optgroup label="'.$a.'">'."\n";
				foreach($b as $c=>$d) {
					if($c == $selected)
						echo "\t".'<option value="'.$c.'" selected="selected">'.$d.'</option>'."\n";
					else
						echo "\t".'<option value="'.$c.'">'.$d.'</option>'."\n";
				}
				echo '</optgroup>'."\n";
			} else {
				if($a == $selected)
					echo "\t".'<option value="'.$a.'" selected="selected">'.$b.'</option>'."\n";
				else
					echo "\t".'<option value="'.$a.'">'.$b.'</option>'."\n";
			}
		}
		echo '</select>'."\n";
	}

	/**
	 * Méthode qui affiche un zone de saisie
	 *
	 * @param	name		nom de la zone de saisie
	 * @param	value		valeur contenue dans la zone de saisie
	 * @param	type		type du champ (text, password)
	 * @param	size		longueur du champ - nombre maximal de caractères pouvant être saisis (par défaut 50-255)
	 * @param	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @return	stdout
	 **/
	public static function printInput($name, $value='', $type='text', $size='50-255', $readonly=false, $class='') {

		$size = explode('-',$size);
		if($readonly)
			echo '<input id="id_'.$name.'" name="'.$name.'" type="'.$type.'" class="readonly" value="'.$value.'" size="'.$size[0].'" maxlength="'.$size[1].'" readonly="readonly" />'."\n";
		else
			echo '<input id="id_'.$name.'" name="'.$name.'" type="'.$type.'"'.($class!=''?' class="'.$class.'"':'').' value="'.$value.'" size="'.$size[0].'" maxlength="'.$size[1].'" />'."\n";	
	}

	/**
	 * Méthode qui affiche une zone de texte
	 *
	 * @param	name		nom de la zone de texte
	 * @param	value		valeur contenue dans la zone de texte
	 * @param	cols		nombre de caractères affichés par colonne
	 * @params	rows		nombre de caractères affichés par ligne
	 * @param	readonly	vrai si le champ est en lecture seule (par défaut à faux)
	 * @param	class		class css à utiliser pour formater l'affichage
	 * @return	stdout
	 **/
	public static function printArea($name, $value='', $cols='', $rows='', $readonly=false, $class='') {

		if($readonly)
			echo '<textarea id="id_'.$name.'" name="'.$name.'" class="readonly" cols="'.$cols.'" rows="'.$rows.'" readonly="readonly">'.$value.'</textarea>'."\n";
		else
			echo '<textarea id="id_'.$name.'" name="'.$name.'"'.($class!=''?' class="'.$class.'"':'').' cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea>'."\n";
	}

	/**
	 * Méthode qui teste si un fichier est accessible en écriture
	 *
	 * @param	file		emplacement et nom du fichier à tester
	 * @return	stdout		affiche un message si le fichier est accessible ou non en écriture
	 **/	
	public static function testWrite($file) {

		if(is_writable($file))
			echo $file.' est accessible en &eacute;criture';
		else
			echo '<span class="alert">'.$file.' n\'est pas accessible en &eacute;criture</span>';
	}

	/**
	 * Méthode qui formate une chaine de caractères en supprimant des caractères non valides
	 *
	 * @param	str			chaine de caracères à formater
	 * @param	charset		charset à utiliser dans le formatage de la chaine (par défaut utf-8)
	 * @return	string		chaine formatée
	 **/
	public static function removeAccents($str,$charset='utf-8') {

	    $str = htmlentities($str, ENT_NOQUOTES, $charset);
	    $str = preg_replace('#\&([A-za-z])(?:acute|cedil|circ|grave|ring|tilde|uml|uro)\;#', '\1', $str);
	    $str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); # pour les ligatures e.g. '&oelig;'
	    $str = preg_replace('#\&[^;]+\;#', '', $str); # supprime les autres caractères    
	    return $str;
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour une url
	 *
	 * @param	str			chaine de caractères à formater
	 * @return	string		nom d'url valide
	 **/
	public static function title2url($str) {

		$str = strtolower(plxUtils::removeAccents($str,PLX_CHARSET));
		$str = preg_replace('/[^[:alnum:]]+/',' ',$str);
		return strtr(trim($str), ' ', '-');
	}

	/**
	 * Méthode qui convertit une chaine de caractères au format valide pour un nom de fichier
	 *
	 * @param	str			chaine de caractères à formater
	 * @return	string		nom de fichier valide
	 **/
	public static function title2filename($str) {

		$str = strtolower(plxUtils::removeAccents($str,PLX_CHARSET));
		$str = preg_replace('/[^[:alnum:]|.|_]+/',' ',$str);
		return strtr(ltrim(trim($str),'.'), ' ', '-');
	}

	/**
	 * Méthode qui convertit un chiffre en chaine de caractères sur une longueur de n caractères, completée par des 0 à gauche
	 *
	 * @param	num					chiffre à convertire
	 * @param	length				longeur de la chaine à retourner
	 * @return	string				chaine formatée
	 **/
	public static function formatRelatif($num, $lenght) {

		$fnum = str_pad(abs($num), $lenght, '0', STR_PAD_LEFT);
		if($num > -1)
			return '+'.$fnum;
		else
			return '-'.$fnum;
	}

	/**
	 * Méthode qui écrit dans un fichier
	 * Mode écriture seule; place le pointeur de fichier au début du fichier et réduit la taille du fichier à 0. Si le fichier n'existe pas, on tente de le créer. 
	 *
	 * @param	xml					contenu du fichier 
	 * @param	filename			emplacement et nom du fichier
	 * @return	boolean				retourne vrai si l'écriture s'est bien déroulée
	 **/
	public static function write($xml, $filename) {

		if(file_exists($filename)) {
			$f = fopen($filename.'.tmp', 'w'); # On ouvre le fichier temporaire
			fwrite($f, trim($xml)); # On écrit
			fclose($f); # On ferme
			unlink($filename);
			rename($filename.'.tmp', $filename); # On renomme le fichier temporaire avec le nom de l'ancien
		} else {
			$f = fopen($filename, 'w'); # On ouvre le fichier
			fwrite($f, trim($xml)); # On écrit
			fclose($f); # On ferme
		}
		# On place les bons droits
		@chmod($filename,0644);
		# On vérifie le résultat
		if(file_exists($filename) AND !file_exists($filename.'.tmp'))
			return true;
		else
			return false;
	}

	/**
	 * Méthode qui crée la miniature d'une image
	 *
	 * @param	filename			emplacement et nom du fichier source
	 * @param	filename_out		emplacement et nom de la miniature créée
	 * @param	width				largeur de la miniature
	 * @param	height				hauteur de la miniature
	 * @param	quality				qualité de l'image
	 * @return	null
	 **/
	public static function makeThumb($filename, $filename_out, $width, $height, $quality) {
	
		# Informations sur l'image
		list($width_orig,$height_orig,$type) = getimagesize($filename);

		# Calcul du ratio
		$ratio_w = $width / $width_orig;
		$ratio_h = $height / $height_orig;
		if($ratio_w < $ratio_h AND $ratio_w < 1) {
			$width = $ratio_w * $width_orig;
			$height = $ratio_w * $height_orig;
		} elseif($ratio_h < 1) {
			$width = $ratio_h * $width_orig;
			$height = $ratio_h * $height_orig;
		} else {
			$width = $width_orig;
			$height = $height_orig;
		}
		
		# Création de l'image
		$image_p = imagecreatetruecolor($width,$height);
	
		if($type == 1) {
			$image = imagecreatefromgif($filename);
			$color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
			imagefill($image_p, 0, 0, $color);
			imagesavealpha($image_p, true);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagegif($image_p, $filename_out);
		}
		elseif($type == 2) {
			$image = imagecreatefromjpeg($filename);
			imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagejpeg($image_p, $filename_out, $quality);
		}
		elseif($type == 3) {
			$image = imagecreatefrompng($filename);
			$color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
			imagefill($image_p, 0, 0, $color);
			imagesavealpha($image_p, true);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagepng($image_p, $filename_out);
		}
		
		return is_readable($filename_out);
	}

	/**
	 * Méthode qui affiche un message
	 *
	 * @param	msg			message à afficher
	 * @param	class		class css à utiliser pour formater l'affichage du message
	 * @return	stdout
	 **/
	public static function showMsg($msg, $class='') {

		if($class=='') echo '<p class="msg"><strong>'.$msg.'</strong></p>';
		else echo '<p class="'.$class.'"><strong>'.$msg.'</strong></p>';
	}

	/**
	 * Méthode qui retourne l'url de base du site
	 *
	 * @return	string		url de base du site
	 **/
	public static function getRacine() {

		$doc = str_replace('install.php', '', $_SERVER['SCRIPT_NAME']);
		if(!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on')
			return trim('https://'.$_SERVER['HTTP_HOST'].$doc);
		else
			return trim('http://'.$_SERVER['HTTP_HOST'].$doc);
	}

	/**
	 * Méthode qui retourne une chaine de caractères au hasard
	 *
	 * @param	taille		nombre de caractère de la chaine à retourner (par défaut sur 10 caractères)
	 * @return	string		chaine de caractères au hasard
	 **/
	public static function charAleatoire($taille='10') {

		$string = '';	 
		$chaine = 'abcdefghijklmnpqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';	 
		mt_srand((float)microtime()*1000000);	 
		for($i=0; $i<$taille; $i++)
			$string .= $chaine[ mt_rand()%strlen($chaine) ];	 
		return $string;	 
	}

	/**
	 * Méthode qui coupe une chaine de caractères sur n caractères ou sur n mots
	 *
	 * @param	str			chaine de caractères à couper
	 * @param	length		nombre de caractères ou nombre de mots à garder (par défaut 25)
	 * @param   type		à renseigner avec la valeur 'word' pour couper en nombre de mots. Par défaut la césure se fait en nombre de caractères
	 * @param	add_text	texte à ajouter après la chaine coupée (par défaut '...' est ajouté)
	 * @return	string		chaine de caractères coupée
	 **/
	public static function strCut($str='', $length=25, $type='', $add_text='...') {
		if($type == 'word') { # On coupe la chaine en comptant le nombre de mots
			$content = explode(' ',$str);
			$length = sizeof($content) < $length ? sizeof($content) : $length;
			return implode(' ',array_slice($content,0,$length)).$add_text;
		} else { # On coupe la chaine en comptant le nombre de caractères
			return strlen($str) > $length ? substr($str, 0, $length-strlen($add_text)).$add_text : $str;
		}
	}
	
	/**
	 * Méthode qui retourne une chaine de caractères formatée en fonction du charset
	 *
	 * @param	str			chaine de caractères
	 * @return	string		chaine de caractères tenant compte du charset
	 **/
	public static function strCheck($str) {

		return htmlspecialchars($str,ENT_QUOTES,PLX_CHARSET);
	}
	
	/**
	 * Méthode qui retourne une chaine de caractères HTML en fonction du charset
	 *
	 * @param	str			chaine de caractères
	 * @return	string		chaine de caractères tenant compte du charset
	 **/
	public static function strRevCheck($str) {

		return html_entity_decode($str,ENT_QUOTES,PLX_CHARSET);
	}

	/**
	 * Méthode qui recherche un fichier dans le dossier 'admin/sous_navigation' en fonction du nom de la page affichée
	 *
	 * @return	string		nom du fichier de sous navigation
	 **/
	public static function getSousNav() {

		$file = preg_split('/[\/]/',$_SERVER['SCRIPT_NAME']);
		$script = array_pop($file);
		$template = preg_split('/[_.]/',$script);
		if(file_exists('sous_navigation/'.$template[0].'.php'))
			return 'sous_navigation/'.$template[0].'.php';
		if(file_exists('sous_navigation/'.$template[0].'s.php'))
			return 'sous_navigation/'.$template[0].'s.php';
	}

	/**
	 * Méthode qui détermine si on navigue à partir d'un mobile
	 *
	 * @return	boolean		retourne vrai si on est sur un mobile
	 *
	 * This code is from http://detectmobilebrowsers.mobi/ - please do not republish it without due credit and hyperlink to http://detectmobilebrowsers.mobi/ really, i'd prefer it if it wasn't republished in full as that way it's main source is it's homepage and it's always kept up to date
	 * For help generating the function call visit http://detectmobilebrowsers.mobi/ and use the function generator. If you need serious help with this please drop me an email to andy@andymoore.info with the subject 'DETECTION CODE PAID SUPPORT REUQEST' with a detailed outline of what you need and how I can help and I will get back to you with a proposal for integration.
	 * Published by Andy Moore - .mobi certified mobile web developer - http://andymoore.info/
	 * This code is free to download and use on non-profit websites, if your website makes a profit or you require support using this code please upgrade.
	 * Please upgrade for use on commercial websites http://detectmobilebrowsers.mobi/?volume=49999
	 * To submit a support request please forward your PayPal receipt with your questions to the email address you sent the money to and I will endeavour to get back to you. It might take me a few days but I reply to all support issues with as much helpful info as I can provide. Though really everything is published on the site.
	 * The function has eight parameters that can be passed to it which define the way it handles different scenarios. These paramaters are:
		* iPhone - Set to true to treat iPhones as mobiles, false to treat them like full browsers or set a URL (including http://) to redirect iPhones and iPods to.
		* Android - Set to true to treat Android handsets as mobiles, false to treat them like full browsers or set a URL (including http://) to redirect Android and Google mobile users to.
		* Opera Mini - Set to true to treat Opera Mini like a mobile, false to treat it like full browser or set a URL (including http://) to redirect Opera Mini users to.
		* Blackberry - Set to true to treat Blackberry like a mobile, false to treat it like full browser or set a URL (including http://) to redirect Blackberry users to.
		* Palm - Set to true to treat Palm OS like a mobile, false to treat it like full browser or set a URL (including http://) to redirect Palm OS users to.
		* Windows - Set to true to treat Windows Mobiles like a mobile, false to treat it like full browser or set a URL (including http://) to redirect Windows Mobile users to.
		* Mobile Redirect URL - This should be full web address (including http://) of the site (or page) you want to send mobile visitors to. Leaving this blank will make the script return true when it detects a mobile.
		* Desktop Redirect URL - This should be full web address (including http://) of the site (or page) you want to send non-mobile visitors to. Leaving this blank will make the script return false when it fails to detect a mobile.
	 * Change Log:
		* 25.11.08 - Added Amazon's Kindle to the pipe seperated array
		* 27.11.08 - Added support for Blackberry options
		* 27.01.09 - Added usage samples & help with PHP in HTML - .zip
		* 09.03.09 - Added support for Windows Mobile options
		* 09.03.09 - Removed 'ppc;'=>'ppc;', from array to reduce false positives
		* 09.03.09 - Added support for Palm OS options
		* 09.03.09 - Added sample .htaccess html.html and help.html files to download
		* 16.03.09 - Edited sample .htaccess file - now works with GoDaddy
		* 14.08.09 - Reduced false positives
		* 14.08.09 - Added Palm Pre
		* 14.08.09 - Added answer about search engine spiders
		* 14.08.09 - Added status variable to report back it's findings for debugging
		* 14.08.09 - Added Torch Mobile Iris Browser to Windows Mobile section
		* 14.08.09 - Added HTC Touch 3G to Windows Mobile section
		* 14.08.09 - Added help links to PHP header and setup PHP in HTML
		* 14.08.09 - Added six usage examples
		* 15.08.09 - Checked against the list of agents in the WURFL - 99.27% detected!
			* 11,489 mobile user agent strings checked
			* 99.27% detection rate after a number of small changes
			* Those user agent strings listed that are not detected are either robots or too generic for user agent detection
			* Any mobiles not detected by their user agent would most likely return true as they'd be detected by the headers they add.
		* 20.11.09 - Removed PDA from the piped array to stop false positives
		* 22.12.09 - Moved the site to a server hosted at Rackspace
		* 23.12.09 - Added support for Mozilla Fennec
		* 23.04.10 - Added support for the Apple iPad
			* Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B367 Safari/531.21.10
			* Mozilla/5.0 (iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Mobile/7D11
			* Mozilla/5.0 (iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B317 Safari/531.21.10
		* 23.04.10 - Changed all eregi function calls to preg_match
		* 23.04.10 - Added two more examples
			* Added example-7.php which allows switching between desktop and mobile versions
			* Added example-8.php which shows why the script made the decision it did
		* No longer using include, using require_once instead
		bug fixes with many thanks and much credit to http://www.punchkickinteractive.com/ - thanks Ryan!
	 */
	public static function mobileDetect($iphone=true,$ipad=true,$android=true,$opera=true,$blackberry=true,$palm=true,$windows=true,$mobileredirect=false,$desktopredirect=false){
		$mobile_browser   = false; // set mobile browser as false till we can prove otherwise
		$user_agent       = $_SERVER['HTTP_USER_AGENT']; // get the user agent value - this should be cleaned to ensure no nefarious input gets executed
		$accept           = $_SERVER['HTTP_ACCEPT']; // get the content accept value - this should be cleaned to ensure no nefarious input gets executed

		switch(true){ // using a switch against the following statements which could return true is more efficient than the previous method of using if statements
			case (preg_match('/ipad/i',$user_agent)); // we find the word ipad in the user agent
				$mobile_browser = $ipad; // mobile browser is either true or false depending on the setting of ipad when calling the function
				$status = 'Apple iPad';
				if(substr($ipad,0,4)=='http'){ // does the value of ipad resemble a url
					$mobileredirect = $ipad; // set the mobile redirect url to the url value stored in the ipad value
				} // ends the if for ipad being a url
				break; // break out and skip the rest if we've had a match on the ipad // this goes before the iphone to catch it else it would return on the iphone instead
			case (preg_match('/ipod/i',$user_agent)||preg_match('/iphone/i',$user_agent)); // we find the words iphone or ipod in the user agent
				$mobile_browser = $iphone; // mobile browser is either true or false depending on the setting of iphone when calling the function
				$status = 'Apple';
				if(substr($iphone,0,4)=='http'){ // does the value of iphone resemble a url
					$mobileredirect = $iphone; // set the mobile redirect url to the url value stored in the iphone value
				} // ends the if for iphone being a url
				break; // break out and skip the rest if we've had a match on the iphone or ipod
			case (preg_match('/android/i',$user_agent));  // we find android in the user agent
				$mobile_browser = $android; // mobile browser is either true or false depending on the setting of android when calling the function
				$status = 'Android';
				if(substr($android,0,4)=='http'){ // does the value of android resemble a url
					$mobileredirect = $android; // set the mobile redirect url to the url value stored in the android value
				} // ends the if for android being a url
				break; // break out and skip the rest if we've had a match on android
			case (preg_match('/opera mini/i',$user_agent)); // we find opera mini in the user agent
				$mobile_browser = $opera; // mobile browser is either true or false depending on the setting of opera when calling the function
				$status = 'Opera';
				if(substr($opera,0,4)=='http'){ // does the value of opera resemble a rul
					$mobileredirect = $opera; // set the mobile redirect url to the url value stored in the opera value
				} // ends the if for opera being a url 
				break; // break out and skip the rest if we've had a match on opera
			case (preg_match('/blackberry/i',$user_agent)); // we find blackberry in the user agent
				$mobile_browser = $blackberry; // mobile browser is either true or false depending on the setting of blackberry when calling the function
				$status = 'Blackberry';
				if(substr($blackberry,0,4)=='http'){ // does the value of blackberry resemble a rul
					$mobileredirect = $blackberry; // set the mobile redirect url to the url value stored in the blackberry value
				} // ends the if for blackberry being a url 
				break; // break out and skip the rest if we've had a match on blackberry
			case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i',$user_agent)); // we find palm os in the user agent - the i at the end makes it case insensitive
				$mobile_browser = $palm; // mobile browser is either true or false depending on the setting of palm when calling the function
				$status = 'Palm';
				if(substr($palm,0,4)=='http'){ // does the value of palm resemble a rul
					$mobileredirect = $palm; // set the mobile redirect url to the url value stored in the palm value
				} // ends the if for palm being a url 
				break; // break out and skip the rest if we've had a match on palm os
			case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i',$user_agent)); // we find windows mobile in the user agent - the i at the end makes it case insensitive
				$mobile_browser = $windows; // mobile browser is either true or false depending on the setting of windows when calling the function
				$status = 'Windows Smartphone';
				if(substr($windows,0,4)=='http'){ // does the value of windows resemble a rul
					$mobileredirect = $windows; // set the mobile redirect url to the url value stored in the windows value
				} // ends the if for windows being a url 
				break; // break out and skip the rest if we've had a match on windows
			case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i',$user_agent)); // check if any of the values listed create a match on the user agent - these are some of the most common terms used in agents to identify them as being mobile devices - the i at the end makes it case insensitive
				$mobile_browser = true; // set mobile browser to true
				$status = 'Mobile matched on piped preg_match';
				break; // break out and skip the rest if we've preg_match on the user agent returned true 
			case ((strpos($accept,'text/vnd.wap.wml')>0)||(strpos($accept,'application/vnd.wap.xhtml+xml')>0)); // is the device showing signs of support for text/vnd.wap.wml or application/vnd.wap.xhtml+xml
				$mobile_browser = true; // set mobile browser to true
				$status = 'Mobile matched on content accept header';
				break; // break out and skip the rest if we've had a match on the content accept headers
			case (isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])); // is the device giving us a HTTP_X_WAP_PROFILE or HTTP_PROFILE header - only mobile devices would do this
				$mobile_browser = true; // set mobile browser to true
				$status = 'Mobile matched on profile headers being set';
				break; // break out and skip the final step if we've had a return true on the mobile specfic headers
			case (in_array(strtolower(substr($user_agent,0,4)),array('1207'=>'1207','3gso'=>'3gso','4thp'=>'4thp','501i'=>'501i','502i'=>'502i','503i'=>'503i','504i'=>'504i','505i'=>'505i','506i'=>'506i','6310'=>'6310','6590'=>'6590','770s'=>'770s','802s'=>'802s','a wa'=>'a wa','acer'=>'acer','acs-'=>'acs-','airn'=>'airn','alav'=>'alav','asus'=>'asus','attw'=>'attw','au-m'=>'au-m','aur '=>'aur ','aus '=>'aus ','abac'=>'abac','acoo'=>'acoo','aiko'=>'aiko','alco'=>'alco','alca'=>'alca','amoi'=>'amoi','anex'=>'anex','anny'=>'anny','anyw'=>'anyw','aptu'=>'aptu','arch'=>'arch','argo'=>'argo','bell'=>'bell','bird'=>'bird','bw-n'=>'bw-n','bw-u'=>'bw-u','beck'=>'beck','benq'=>'benq','bilb'=>'bilb','blac'=>'blac','c55/'=>'c55/','cdm-'=>'cdm-','chtm'=>'chtm','capi'=>'capi','cond'=>'cond','craw'=>'craw','dall'=>'dall','dbte'=>'dbte','dc-s'=>'dc-s','dica'=>'dica','ds-d'=>'ds-d','ds12'=>'ds12','dait'=>'dait','devi'=>'devi','dmob'=>'dmob','doco'=>'doco','dopo'=>'dopo','el49'=>'el49','erk0'=>'erk0','esl8'=>'esl8','ez40'=>'ez40','ez60'=>'ez60','ez70'=>'ez70','ezos'=>'ezos','ezze'=>'ezze','elai'=>'elai','emul'=>'emul','eric'=>'eric','ezwa'=>'ezwa','fake'=>'fake','fly-'=>'fly-','fly_'=>'fly_','g-mo'=>'g-mo','g1 u'=>'g1 u','g560'=>'g560','gf-5'=>'gf-5','grun'=>'grun','gene'=>'gene','go.w'=>'go.w','good'=>'good','grad'=>'grad','hcit'=>'hcit','hd-m'=>'hd-m','hd-p'=>'hd-p','hd-t'=>'hd-t','hei-'=>'hei-','hp i'=>'hp i','hpip'=>'hpip','hs-c'=>'hs-c','htc '=>'htc ','htc-'=>'htc-','htca'=>'htca','htcg'=>'htcg','htcp'=>'htcp','htcs'=>'htcs','htct'=>'htct','htc_'=>'htc_','haie'=>'haie','hita'=>'hita','huaw'=>'huaw','hutc'=>'hutc','i-20'=>'i-20','i-go'=>'i-go','i-ma'=>'i-ma','i230'=>'i230','iac'=>'iac','iac-'=>'iac-','iac/'=>'iac/','ig01'=>'ig01','im1k'=>'im1k','inno'=>'inno','iris'=>'iris','jata'=>'jata','java'=>'java','kddi'=>'kddi','kgt'=>'kgt','kgt/'=>'kgt/','kpt '=>'kpt ','kwc-'=>'kwc-','klon'=>'klon','lexi'=>'lexi','lg g'=>'lg g','lg-a'=>'lg-a','lg-b'=>'lg-b','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-f'=>'lg-f','lg-g'=>'lg-g','lg-k'=>'lg-k','lg-l'=>'lg-l','lg-m'=>'lg-m','lg-o'=>'lg-o','lg-p'=>'lg-p','lg-s'=>'lg-s','lg-t'=>'lg-t','lg-u'=>'lg-u','lg-w'=>'lg-w','lg/k'=>'lg/k','lg/l'=>'lg/l','lg/u'=>'lg/u','lg50'=>'lg50','lg54'=>'lg54','lge-'=>'lge-','lge/'=>'lge/','lynx'=>'lynx','leno'=>'leno','m1-w'=>'m1-w','m3ga'=>'m3ga','m50/'=>'m50/','maui'=>'maui','mc01'=>'mc01','mc21'=>'mc21','mcca'=>'mcca','medi'=>'medi','meri'=>'meri','mio8'=>'mio8','mioa'=>'mioa','mo01'=>'mo01','mo02'=>'mo02','mode'=>'mode','modo'=>'modo','mot '=>'mot ','mot-'=>'mot-','mt50'=>'mt50','mtp1'=>'mtp1','mtv '=>'mtv ','mate'=>'mate','maxo'=>'maxo','merc'=>'merc','mits'=>'mits','mobi'=>'mobi','motv'=>'motv','mozz'=>'mozz','n100'=>'n100','n101'=>'n101','n102'=>'n102','n202'=>'n202','n203'=>'n203','n300'=>'n300','n302'=>'n302','n500'=>'n500','n502'=>'n502','n505'=>'n505','n700'=>'n700','n701'=>'n701','n710'=>'n710','nec-'=>'nec-','nem-'=>'nem-','newg'=>'newg','neon'=>'neon','netf'=>'netf','noki'=>'noki','nzph'=>'nzph','o2 x'=>'o2 x','o2-x'=>'o2-x','opwv'=>'opwv','owg1'=>'owg1','opti'=>'opti','oran'=>'oran','p800'=>'p800','pand'=>'pand','pg-1'=>'pg-1','pg-2'=>'pg-2','pg-3'=>'pg-3','pg-6'=>'pg-6','pg-8'=>'pg-8','pg-c'=>'pg-c','pg13'=>'pg13','phil'=>'phil','pn-2'=>'pn-2','pt-g'=>'pt-g','palm'=>'palm','pana'=>'pana','pire'=>'pire','pock'=>'pock','pose'=>'pose','psio'=>'psio','qa-a'=>'qa-a','qc-2'=>'qc-2','qc-3'=>'qc-3','qc-5'=>'qc-5','qc-7'=>'qc-7','qc07'=>'qc07','qc12'=>'qc12','qc21'=>'qc21','qc32'=>'qc32','qc60'=>'qc60','qci-'=>'qci-','qwap'=>'qwap','qtek'=>'qtek','r380'=>'r380','r600'=>'r600','raks'=>'raks','rim9'=>'rim9','rove'=>'rove','s55/'=>'s55/','sage'=>'sage','sams'=>'sams','sc01'=>'sc01','sch-'=>'sch-','scp-'=>'scp-','sdk/'=>'sdk/','se47'=>'se47','sec-'=>'sec-','sec0'=>'sec0','sec1'=>'sec1','semc'=>'semc','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','sk-0'=>'sk-0','sl45'=>'sl45','slid'=>'slid','smb3'=>'smb3','smt5'=>'smt5','sp01'=>'sp01','sph-'=>'sph-','spv '=>'spv ','spv-'=>'spv-','sy01'=>'sy01','samm'=>'samm','sany'=>'sany','sava'=>'sava','scoo'=>'scoo','send'=>'send','siem'=>'siem','smar'=>'smar','smit'=>'smit','soft'=>'soft','sony'=>'sony','t-mo'=>'t-mo','t218'=>'t218','t250'=>'t250','t600'=>'t600','t610'=>'t610','t618'=>'t618','tcl-'=>'tcl-','tdg-'=>'tdg-','telm'=>'telm','tim-'=>'tim-','ts70'=>'ts70','tsm-'=>'tsm-','tsm3'=>'tsm3','tsm5'=>'tsm5','tx-9'=>'tx-9','tagt'=>'tagt','talk'=>'talk','teli'=>'teli','topl'=>'topl','hiba'=>'hiba','up.b'=>'up.b','upg1'=>'upg1','utst'=>'utst','v400'=>'v400','v750'=>'v750','veri'=>'veri','vk-v'=>'vk-v','vk40'=>'vk40','vk50'=>'vk50','vk52'=>'vk52','vk53'=>'vk53','vm40'=>'vm40','vx98'=>'vx98','virg'=>'virg','vite'=>'vite','voda'=>'voda','vulc'=>'vulc','w3c '=>'w3c ','w3c-'=>'w3c-','wapj'=>'wapj','wapp'=>'wapp','wapu'=>'wapu','wapm'=>'wapm','wig '=>'wig ','wapi'=>'wapi','wapr'=>'wapr','wapv'=>'wapv','wapy'=>'wapy','wapa'=>'wapa','waps'=>'waps','wapt'=>'wapt','winc'=>'winc','winw'=>'winw','wonu'=>'wonu','x700'=>'x700','xda2'=>'xda2','xdag'=>'xdag','yas-'=>'yas-','your'=>'your','zte-'=>'zte-','zeto'=>'zeto','acs-'=>'acs-','alav'=>'alav','alca'=>'alca','amoi'=>'amoi','aste'=>'aste','audi'=>'audi','avan'=>'avan','benq'=>'benq','bird'=>'bird','blac'=>'blac','blaz'=>'blaz','brew'=>'brew','brvw'=>'brvw','bumb'=>'bumb','ccwa'=>'ccwa','cell'=>'cell','cldc'=>'cldc','cmd-'=>'cmd-','dang'=>'dang','doco'=>'doco','eml2'=>'eml2','eric'=>'eric','fetc'=>'fetc','hipt'=>'hipt','http'=>'http','ibro'=>'ibro','idea'=>'idea','ikom'=>'ikom','inno'=>'inno','ipaq'=>'ipaq','jbro'=>'jbro','jemu'=>'jemu','java'=>'java','jigs'=>'jigs','kddi'=>'kddi','keji'=>'keji','kyoc'=>'kyoc','kyok'=>'kyok','leno'=>'leno','lg-c'=>'lg-c','lg-d'=>'lg-d','lg-g'=>'lg-g','lge-'=>'lge-','libw'=>'libw','m-cr'=>'m-cr','maui'=>'maui','maxo'=>'maxo','midp'=>'midp','mits'=>'mits','mmef'=>'mmef','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','mwbp'=>'mwbp','mywa'=>'mywa','nec-'=>'nec-','newt'=>'newt','nok6'=>'nok6','noki'=>'noki','o2im'=>'o2im','opwv'=>'opwv','palm'=>'palm','pana'=>'pana','pant'=>'pant','pdxg'=>'pdxg','phil'=>'phil','play'=>'play','pluc'=>'pluc','port'=>'port','prox'=>'prox','qtek'=>'qtek','qwap'=>'qwap','rozo'=>'rozo','sage'=>'sage','sama'=>'sama','sams'=>'sams','sany'=>'sany','sch-'=>'sch-','sec-'=>'sec-','send'=>'send','seri'=>'seri','sgh-'=>'sgh-','shar'=>'shar','sie-'=>'sie-','siem'=>'siem','smal'=>'smal','smar'=>'smar','sony'=>'sony','sph-'=>'sph-','symb'=>'symb','t-mo'=>'t-mo','teli'=>'teli','tim-'=>'tim-','tosh'=>'tosh','treo'=>'treo','tsm-'=>'tsm-','upg1'=>'upg1','upsi'=>'upsi','vk-v'=>'vk-v','voda'=>'voda','vx52'=>'vx52','vx53'=>'vx53','vx60'=>'vx60','vx61'=>'vx61','vx70'=>'vx70','vx80'=>'vx80','vx81'=>'vx81','vx83'=>'vx83','vx85'=>'vx85','wap-'=>'wap-','wapa'=>'wapa','wapi'=>'wapi','wapp'=>'wapp','wapr'=>'wapr','webc'=>'webc','whit'=>'whit','winw'=>'winw','wmlb'=>'wmlb','xda-'=>'xda-',))); // check against a list of trimmed user agents to see if we find a match
				$mobile_browser = true; // set mobile browser to true
				$status = 'Mobile matched on in_array';
				break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it
			default;
				$mobile_browser = false; // set mobile browser to false
				$status = 'Desktop / full capability browser';
				break; // break even though it's the last statement in the switch so there's nothing to break away from but it seems better to include it than exclude it
		} // ends the switch 

		// if redirect (either the value of the mobile or desktop redirect depending on the value of $mobile_browser) is true redirect else we return the status of $mobile_browser
		if($redirect = ($mobile_browser==true) ? $mobileredirect : $desktopredirect){
			header('Location: '.$redirect); // redirect to the right url for this device
			exit;
		} else { 
			// a couple of folkas have asked about the status - that's there to help you debug and understand what the script is doing
			if($mobile_browser==''){
				return $mobile_browser; // will return either true or false 
			}else{
				return array($mobile_browser,$status); // is a mobile so we are returning an array ['0'] is true ['1'] is the $status value
			}
		}
	} // ends function mobile_device_detect

	/**
	 * Méthode qui retourne le type de compression disponible
	 *
	 * @return	stout
	 **/	
	public static function httpEncoding() {
		if( headers_sent() ){
			$encoding = false;
		}elseif( strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false ){
			$encoding = 'x-gzip';
		}elseif( strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false ){
			$encoding = 'gzip';
		}else{
			$encoding = false;
		}
		return $encoding;
	}

	/**
	 * Méthode qui applique la compression gzip avant affichage
	 *
	 * @param	rel2bas		conversion des urls relatives en url absolues. Si conversion contient la racine du site
	 * @return	stout
	 * @return	stout
	 **/
	public static function ob_gzipped_page($rel2abs=false) {

		if($encoding=plxUtils::httpEncoding()) {
			$contents = ob_get_clean();
			if($rel2abs) $contents = plxUtils::rel2abs($rel2abs, $contents);
			header('Content-Encoding: '.$encoding);
			echo("\x1f\x8b\x08\x00\x00\x00\x00\x00");
			$size = strlen($contents);
			$contents = gzcompress($contents, 9);
			$contents = substr($contents, 0, $size);
		} else {
			$contents = ob_get_clean();
			if($rel2abs) $contents = plxUtils::rel2abs($rel2abs, $contents);
		}
		echo $contents;
		exit();
	}

	/**
	 * Méthode qui converti les liens relatifs en liens absolus
	 *
	 * @param	base		url du site qui sera rajoutée devant les liens relatifs
	 * @param	html		chaine de caractères à convertir
	 * @return	string		chaine de caractères modifiée
	 **/
	public static function rel2abs($base, $html) {

		// generate server-only replacement for root-relative URLs
		$server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);
		// on repart les liens ne commençant que part #
		$get = plxUtils::getGets();
		$html = preg_replace('@\<([^>]*) (href|src)="(#[^"]*)"@i', '<\1 \2="' . $get . '\3"', $html);
		// replace root-relative URLs
		$html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="' . $server . '\3"', $html);
		// replace base-relative URLs
		$html = preg_replace('@\<([^>]*) (href|src)="(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

		return $html;

	}

}
?>