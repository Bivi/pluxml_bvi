<?php

/**
 * Classe plxAdmin responsable des modifications dans l'administration
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F
 **/
class plxAdmin extends plxMotor {

	public $editor = false; # editor

	/**
	 * Constructeur qui appel le constructeur parent
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	public function __construct($filename) {

		parent::__construct($filename);

		# chargement de l'editeur plxtoolbar ou autre
		if(isset($this->aConf['editor'])) {
			$path_editor = PLX_ROOT.($this->aConf['editor']=='plxtoolbar'?'core/':'addons/editor.').$this->aConf['editor'].'/';
			if(is_file($path_editor.$this->aConf['editor'].'.php')) {
				include_once($path_editor.$this->aConf['editor'].'.php');
				if(class_exists('plxEditor')) $this->editor = new plxEditor($path_editor);
			}
		}

	}
	
	/**
	 * Méthode qui récupère le numéro de la page active
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	public function getPage() {
		
		# Initialisation
		$pageName = basename($_SERVER['PHP_SELF']);
		$savePage = preg_match('/admin\/(index|commentaires_online|commentaires_offline).php/', $_SERVER['PHP_SELF']);
		# On check pour avoir le numero de page
		if(!empty($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0)
			$this->page = $_GET['page'];
		elseif($savePage)
			$this->page = !empty($_SESSION['page'][ $pageName ])?intval($_SESSION['page'][ $pageName ]):1;
		# On sauvegarde
		if($savePage) $_SESSION['page'][ $pageName ] = $this->page;
		
	}

	/**
	 * Méthode qui édite le fichier XML de configuration selon le tableau $global et $content
	 *
	 * @param	global	tableau contenant toute la configuration PluXml
	 * @param	content	tableau contenant la configuration à modifier
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function editConfiguration($global,$content) {

		# on mémorise l'état actuel de l'urlrewriting
		$urlrewrinting = isset($global['urlrewriting'])?$global['urlrewriting']:0;

		# Tableau des clés à mettre sous chaîne cdata
		$aCdata = array('title','description','racine','feed_footer');

		# Début du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($content as $k=>$v) {
			$global[ $k ] = $v;
		}
		# On teste la clef
		if(empty($global['clef'])) $global['clef'] = plxUtils::charAleatoire(15);
		foreach($global as $k=>$v) {
			if(in_array($k,$aCdata))
				$xml .= "\t<parametre name=\"$k\"><![CDATA[".$v."]]></parametre>\n";
			else
				$xml .= "\t<parametre name=\"$k\">".$v."</parametre>\n";
		}
		$xml .= "</document>";
		
		# On réinitialise la pagination au cas où modif de bypage_admin
		$_SESSION['page'] = array();
		
		# Si la réécriture d'urls est demandée, on mets en place le fichier .htaccess
		if(isset($content['urlrewriting']) AND $content['urlrewriting']==1 AND $urlrewrinting==0) 
			$this->htaccess('new', $global['racine']);
		else $this->htaccess('update', $global['racine']);

		# On écrit le fichier
		if(plxUtils::write($xml,PLX_CONF))
			return plxMsg::Info('Configuration modifi&eacute;e avec succ&egrave;s');
		else
			return plxMsg::Error('Erreur dans la modification du fichier '.PLX_CONF);

	}

	/**
	 * Méthode qui crée le fichier .htaccess en cas de réécriture d'urls
	 *
	 * @param	action		mise à jour ou création
	 * @param   url			url du site
	 * @return	null
	 * @author	Stephane F
	 **/	

	public function htaccess($action, $url) {

		$base = parse_url($url);

		if($action=='new') {

			$htaccess = '
# BEGIN -- Pluxml
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteBase '.$base['path'].'
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l
# Réécriture des urls
RewriteRule ^([^feed\/].*)$ index.php?$1 [L]
RewriteRule ^feed\/(.*)$ feed.php?$1 [L]
</IfModule>
<Files "version">
    Order allow,deny
    Deny from all
</Files>
# END -- Pluxml
';
		# On écrit le fichier .htaccess à la racine de PluXml
		plxUtils::write($htaccess, PLX_ROOT.'.htaccess');

		}
		elseif($action=='update' AND is_file(PLX_ROOT.'.htaccess')) {
			$htaccess = implode('',file(PLX_ROOT.'.htaccess'));
			if(preg_match('/(RewriteBase(.*))\n/', $htaccess, $capture)) {
				$htaccess = str_replace($capture[1], 'RewriteBase '.$base['path'], $htaccess);
				# On écrit le fichier .htaccess à la racine de PluXml
				plxUtils::write($htaccess, PLX_ROOT.'.htaccess');
			}
		}

	}

	/**
	 * Méthode qui control l'accès à une page en fonction du profil de l'utilisateur connecté
	 *
	 * @param	profil		profil autorisé
	 * @return	null
	 * @author	Stephane F
	 **/
	public function checkProfil($profil) {
		$args = func_get_args();
		if(is_array($args)) {
			if(!in_array($_SESSION['profil'], $args)) {
				plxMsg::Error('Acc&egrave;s interdit');
				header('Location: index.php');
				exit;
			}
		} else {
			if($_SESSION['profil']!=$profil) {
				plxMsg::Error('Acc&egrave;s interdit');
				header('Location: index.php');
				exit;
			}
		}
	}
	
	/**
	 * Méthode qui édite le profil d'un utilisateur
	 *
	 * @param	content	tableau les informations sur l'utilisateur connecté
	 * @return	string
	 * @author	Stéphane F
	 **/
	public function editProfil($content) {

		if(isset($content['profil']) AND trim($content['name'])=='')
			return plxMsg::Error('Veuillez saisir un nom d\'utilisateur');

		if(isset($content['password']) AND (trim($content['password1'])=='' OR trim($content['password1'])!=trim($content['password2'])))
			return plxMsg::Error('Mauvaise confirmation ou mot de passe vide');

		if(isset($content['password']))
			$this->aUsers[$_SESSION['user']]['password'] = md5($content['password1']);

		$this->aUsers[$_SESSION['user']]['name'] = trim($content['name']);
		$this->aUsers[$_SESSION['user']]['infos'] = trim($content['infos']);
			
		# On génére le fichier XML
		$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
		$xml .= "<document>\n";
		foreach($this->aUsers as $user_id => $user) {
			$xml .= "\t".'<user number="'.$user_id.'" active="'.$user['active'].'" profil="'.$user['profil'].'" delete="'.$user['delete'].'">'."\n";
			$xml .= "\t\t".'<login><![CDATA['.trim($user['login']).']]></login>'."\n";
			$xml .= "\t\t".'<name><![CDATA['.trim($user['name']).']]></name>'."\n";
			$xml .= "\t\t".'<infos><![CDATA['.trim($user['infos']).']]></infos>'."\n";
			$xml .= "\t\t".'<password><![CDATA['.$user['password'].']]></password>'."\n";
			$xml .= "\t</user>\n";		
		}
		$xml .= "</document>";
		# On écrit le fichier
		if(plxUtils::write($xml,PLX_ROOT.$this->aConf['users']))
			return plxMsg::Info('Profil utilisateur modifi&eacute; avec succ&egrave;s');
		else
			return plxMsg::Error('Erreur dans la modification du fichier '.PLX_ROOT.$this->aConf['users']);
	}
	
	/**
	 * Méthode qui édite le fichier XML des utilisateurs
	 *
	 * @param	content	tableau les informations sur les utilisateurs
	 * @return	string
	 * @author	Stéphane F
	 **/
	public function editUsers($content) {
		$action = false;
		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idUser'])) {
			foreach($content['idUser'] as $user_id) {
				if($content['selection']=='delete' AND $user_id!='001') {
					$this->aUsers[$user_id]['delete']=1;
					$action = true;
				}
			}
		}
		# mise à jour de la liste des utilisateurs
		elseif(!empty($content['update'])) {
			foreach($content['userNum'] as $user_id) {
				if(trim($content[$user_id.'_name'])!='' AND trim($content[$user_id.'_login'])!='') {
					if(trim($content[$user_id.'_password'])!='')
						$password=md5($content[$user_id.'_password']);
					elseif(isset($content[$user_id.'_newuser']))
						return "Merci de renseigner un mot de passe";
					else
						$password = $this->aUsers[$user_id]['password'];
					$this->aUsers[$user_id] = array(
						'login' => trim($content[$user_id.'_login']),
						'name' => trim($content[$user_id.'_name']),
						'active' => ($_SESSION['user']==$user_id?$this->aUsers[$user_id]['active']:$content[$user_id.'_active']),
						'profil' => ($_SESSION['user']==$user_id?$this->aUsers[$user_id]['profil']:$content[$user_id.'_profil']),
						'password' => $password,
						'delete' => (isset($this->aUsers[$user_id]['delete'])?$this->aUsers[$user_id]['delete']:0),
						'infos' => trim($content[$user_id.'_infos']),
					);
					$action = true;
				}
			}
		}
		# sauvegarde
		if($action) {
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->aUsers as $user_id => $user) {
				$xml .= "\t".'<user number="'.$user_id.'" active="'.$user['active'].'" profil="'.$user['profil'].'" delete="'.$user['delete'].'">'."\n";
				$xml .= "\t\t".'<login><![CDATA['.$user['login'].']]></login>'."\n";
				$xml .= "\t\t".'<name><![CDATA['.$user['name'].']]></name>'."\n";
				$xml .= "\t\t".'<infos><![CDATA['.$user['infos'].']]></infos>'."\n";
				$xml .= "\t\t".'<password><![CDATA['.$user['password'].']]></password>'."\n";
				$xml .= "\t</user>\n";
			}
			$xml .= "</document>";
			# On écrit le fichier
			if(plxUtils::write($xml,PLX_ROOT.$this->aConf['users']))
				return plxMsg::Info('Liste des utilisateurs modifi&eacute;e avec succ&egrave;s');
			else
				return plxMsg::Error('Erreur dans la modification du fichier '.PLX_ROOT.$this->aConf['users']);
		}
	}

	/**
	 * Méthode qui édite le fichier XML des catégories selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel des catégories
	 * @return	string
	 * @author	Stephane F
	 **/
	public function editCategories($content) {
		$action = false;
		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idCategory'])) {
			foreach($content['idCategory'] as $cat_id) {
				unset($this->aCats[$cat_id]);
				$action = true;
			}
		}
		# ajout nouvelle catégorie à partir de la page article
		elseif(!empty($content['new_category'])) {
			$cat_name = $content['new_catname'];
			if($cat_name!='') {
				$this->aCats[$content['new_catid']] = array(
					'name' => $cat_name,
					'url' => plxUtils::title2url($cat_name),
					'tri' => $this->aConf['tri'],
					'bypage' => $this->aConf['bypage'],
					'menu' => 'oui',
					'template' => 'categorie.php'
				);
				$action = true;
			}
		}
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			foreach($content['catNum'] as $cat_id) {
				$cat_name = $content[$cat_id.'_name'];
				if($cat_name!='') {
					$cat_url = (isset($content[$cat_id.'_url'])?trim($content[$cat_id.'_url']):'');
					$cat_url = ($cat_url!='' ? plxUtils::title2url($cat_url) : plxUtils::title2url($cat_name));
					if($cat_url=='') $cat_url = 'nouvelle-categorie';
					$this->aCats[$cat_id] = array(
						'name' => $cat_name,
						'url' => $cat_url,
						'tri' => $content[$cat_id.'_tri'],
						'bypage' => intval($content[$cat_id.'_bypage']),
						'menu' => $content[$cat_id.'_menu'],
						'ordre' => intval($content[$cat_id.'_ordre']),
						'template' => $content[$cat_id.'_template']
					);
					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi			
			if(sizeof($this->aCats)>0) uasort($this->aCats, create_function('$a, $b', 'return $a["ordre"]>$b["ordre"];'));
		}
		# sauvegarde
		if($action) {
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->aCats as $cat_id => $cat) {
				$xml .= "\t<categorie number=\"".$cat_id."\" tri=\"".$cat['tri']."\" bypage=\"".$cat['bypage']."\" menu=\"".$cat['menu']."\" url=\"".$cat['url']."\" template=\"".$cat['template']."\"><![CDATA[".$cat['name']."]]></categorie>\n";
			}
			$xml .= "</document>";
			# On écrit le fichier
			if(plxUtils::write($xml,PLX_ROOT.$this->aConf['categories']))
				return plxMsg::Info('Liste des cat&eacute;gories modifi&eacute;e avec succ&egrave;s');
			else
				return plxMsg::Error('Erreur dans la modification du fichier '.PLX_ROOT.$this->aConf['categories']);
		}
	}

	/**
	 * Méthode qui édite le fichier XML des pages statiques selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel des pages statiques
	 * @return	string
	 * @author	Stephane F.
	 **/
	public function editStatiques($content) {
		$action = false;
		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idStatic'])) {
			foreach($content['idStatic'] as $static_id) {
				$filename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
				if(is_file($filename)) unlink($filename);
				# si la page statique supprimée est la page d'accueil on met à jour le parametre
				if($static_id==$this->aConf['homestatic']) {
					$this->aConf['homestatic']='';
					$this->editConfiguration($this->aConf,$this->aConf);
				}
				unset($this->aStats[$static_id]);
				$action = true;
			}
		}
		# mise à jour de la liste des pages statiques
		elseif(!empty($content['update'])) {
			foreach($content['staticNum'] as $static_id) {
				$stat_name = $content[$static_id.'_name'];
				if($stat_name!='') {
					$stat_url = (isset($content[$static_id.'_url'])?trim($content[$static_id.'_url']):'');
					$stat_url = ($stat_url!=''?plxUtils::title2url($stat_url):plxUtils::title2url($stat_name));
					if($stat_url=='') $stat_url = 'nouvelle-page';
					# On vérifie si on a besoin de renommer le fichier de la page statique
					if($this->aStats[$static_id]['url']!=$stat_url) {
						$oldfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$this->aStats[$static_id]['url'].'.php';
						$newfilename = PLX_ROOT.$this->aConf['racine_statiques'].$static_id.'.'.$stat_url.'.php';
						if(is_file($oldfilename)) rename($oldfilename, $newfilename);
					}
					$this->aStats[$static_id] = array(
						'group' => trim($content[$static_id.'_group']),
						'name' => $stat_name,
						'url' => $stat_url,
						'active' => $content[$static_id.'_active'],
						'menu' => $content[$static_id.'_menu'],
						'ordre' => intval($content[$static_id.'_ordre']),
						'template' => $content[$static_id.'_template']
					);
					$action = true;
				}
			}
			# On va trier les clés selon l'ordre choisi
			if(sizeof($this->aStats)>0) uasort($this->aStats, create_function('$a, $b', 'return $a["ordre"]>$b["ordre"];'));
		}
		# sauvegarde
		if($action) {
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->aStats as $static_id => $static) {
				$xml .= "\t<statique number=\"".$static_id."\" active=\"".$static['active']."\" menu=\"".$static['menu']."\" url=\"".$static['url']."\" template=\"".$static['template']."\"><group><![CDATA[".$static['group']."]]></group><name><![CDATA[".$static['name']."]]></name></statique>\n";
			}
			$xml .= "</document>";
			# On écrit le fichier si une action valide a été faite
			if(plxUtils::write($xml,PLX_ROOT.$this->aConf['statiques']))
				return plxMsg::Info('Liste des pages statiques modifi&eacute;e avec succ&egrave;s');
			else
				return plxMsg::Error('Erreur dans la modification du fichier '.PLX_ROOT.$this->aConf['statiques']);
		}
	}

	/**
	 * Méthode qui lit le fichier d'une page statique
	 *
	 * @param	num	numero du fichier de la page statique
	 * @return	string	contenu de la page
	 * @author	Stephane F.
	 **/
	public function getFileStatique($num) {

		# Emplacement de la page
		$filename = PLX_ROOT.$this->aConf['racine_statiques'].$num.'.'.$this->aStats[ $num ]['url'].'.php';
		if(file_exists($filename) AND filesize($filename) > 0) {
			if($f = fopen($filename, 'r')) {
				$content = fread($f, filesize($filename));
				fclose($f);
				# On retourne le contenu
				return $content;
			}
		}
		return null;
	}

	/**
	 * Méthode qui sauvegarde le contenu d'une page statique
	 *
	 * @param	content	données à sauvegarder
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function editFileStatique($content) {

		# Génération du nom du fichier
		$filename = PLX_ROOT.$this->aConf['racine_statiques'].$content['id'].'.'.$this->aStats[ $content['id'] ]['url'].'.php';
		# On écrit le fichier
		if(plxUtils::write($content['content'],$filename))
			return plxMsg::Info('Code source de la page statique modifi&eacute; avec succ&egrave;s');
		else
			return plxMsg::Error('Erreur dans la modification du fichier '.$filename);
	}

	/**
	 *  Méthode qui retourne le prochain id d'un article
	 *
	 * @return	string		id d'un nouvel article sous la forme 0001
	 * @author	Stephane F.
	 **/		
	public function nextIdArticle() {

		if(!$aFiles = $this->plxGlob_arts->query('/^[0-9{4}].(.*).xml$/','','rsort',0,1))
			return '0001';
		else {
			$tmp = $this->artInfoFromFilename($aFiles['0']);
			return str_pad($tmp['artId']+1,4, '0', STR_PAD_LEFT);
		}
	}
	
	/**
	 * Méthode qui effectue une création ou mise a jour d'un article
	 *
	 * @param	content	données saisies de l'article
	 * @param	&id	retourne le numero de l'article 
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/	
	public function editArticle($content, &$id) {

		# Détermine le numero de fichier si besoin est
		if($id == '0000' OR $id == '')
			$id = $this->nextIdArticle();
		# Vérification de l'intégrité de l'identifiant
		if(!preg_match('/^[0-9]{4}$/',$id))
			return 'Identifiant d\'article invalide !';
		# Génération de notre url d'article
		if(trim($content['url']) == '')
			$content['url'] = plxUtils::title2url($content['title']);
		else
			$content['url'] = plxUtils::title2url($content['url']);
		# URL vide après le passage de la fonction ;)
		if($content['url'] == '') $content['url'] = 'nouvel-article';
		# Génération du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		$xml .= "\t".'<title><![CDATA['.trim($content['title']).']]></title>'."\n";
		$xml .= "\t".'<allow_com>'.$content['allow_com'].'</allow_com>'."\n";
		$xml .= "\t".'<template><![CDATA['.$content['template'].']]></template>'."\n";		
		$xml .= "\t".'<chapo><![CDATA['.trim($content['chapo']).']]></chapo>'."\n";
		$xml .= "\t".'<content><![CDATA['.trim($content['content']).']]></content>'."\n";
		$xml .= "\t".'<tags><![CDATA['.trim($content['tags']).']]></tags>'."\n";				
		$xml .= "</document>\n";
		
		# A t'on besoin de supprimer un fichier ?
		if($globArt = $this->plxGlob_arts->query('/^'.$id.'.(.*).xml$/','','sort',0,1,'all')) {
			if(file_exists(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0'])) # Un fichier existe, on le supprime
				@unlink(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
		}
		# On genère le nom de notre fichier
		$time = $content['year'].$content['month'].$content['day'].substr(str_replace(':','',$content['time']),0,4);
		if(!preg_match('/^[0-9]{12}$/',$time)) $time = @date('YmdHi'); # Check de la date au cas ou...
		if(empty($content['catId'])) $content['catId']=array('000'); # Catégorie non classée
		$filename = PLX_ROOT.$this->aConf['racine_articles'].$id.'.'.implode(',', $content['catId']).'.'.trim($content['author']).'.'.$time.'.'.$content['url'].'.xml';
		# On va mettre à jour notre fichier
		if(plxUtils::write($xml,$filename)) {
			# mise à jour de la liste des tags
			$this->aTags[$id] = array('tags'=>trim($content['tags']), 'date'=>$time, 'active'=>intval(!in_array('draft', $content['catId'])));
			$this->editTags();
			if($content['artId'] == '0000' OR $content['artId'] == '')
				return plxMsg::Info('Article cr&eacute;&eacute; avec succ&egrave;s');
			else
				return plxMsg::Info('Article mis &agrave; jour avec succ&egrave;s');
		} else {
			return plxMsg::Error('Erreur lors de la sauvegarde de l\'article');
		}
	}

	/**
	 * Méthode qui supprime un article et les commentaires associés
	 *
	 * @param	id	numero de l'article à supprimer
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function delArticle($id) {

		# Vérification de l'intégrité de l'identifiant
		if(!preg_match('/^[0-9]{4}$/',$id))
			return 'Identifiant d\'article invalide !';
		# Variable d'état
		$resDelArt = $resDelCom = true;
		# Suppression de l'article
		if($globArt = $this->plxGlob_arts->query('/^'.$id.'.(.*).xml$/')) {
			@unlink(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
			$resDelArt = !file_exists(PLX_ROOT.$this->aConf['racine_articles'].$globArt['0']);
		}
		# Suppression des commentaires
		if($globComs = $this->plxGlob_coms->query('/^_?'.$id.'.(.*).xml$/')) {
			for($i=0; $i<$this->plxGlob_coms->count; $i++) {
				@unlink(PLX_ROOT.$this->aConf['racine_commentaires'].$globComs[$i]);
				$resDelCom = (!file_exists(PLX_ROOT.$this->aConf['racine_commentaires'].$globComs[$i]) AND $resDelCom);
			}
		}
		# On renvoi le résultat
		if($resDelArt AND $resDelCom) {
			# mise à jour de la liste des tags
			if(isset($this->aTags[$id])) {
				unset($this->aTags[$id]);
				$this->editTags();
			}
			return plxMsg::Info('Suppression effectu&eacute;e avec succ&egrave;s');
		}
		else
			return plxMsg::Error('Une erreur est survenue pendant la suppression');
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	contenu du nouveau commentaire
	 * @return	booléen
	 * @author	Florent MONTHEL
	 **/
	public function newCommentaire($artId,$content) {

		# On génère le contenu du commentaire
		$author = plxUtils::strCheck($this->aUsers[$_SESSION['user']]['name']);
		$contenu = strip_tags(trim($content),'<a>,<strong>');
		$date = time();
		$site = $this->racine;
		$ip = plxUtils::getIp();
		# On genere le nom du fichier selon l'existence ou non d'un fichier du meme nom
		$i = 0;
		do { # On boucle en testant l'existence du fichier (cas de plusieurs commentaires/sec pour un article)
			$i++;
			$filename = PLX_ROOT.$this->aConf['racine_commentaires'].$artId.'.'.$date.'-'.$i.'.xml';
		} while(file_exists($filename));
		# On peut creer le commentaire
		if($this->addCommentaire($filename,$author,'admin',$ip,'',$site,$contenu)) # Commentaire OK
			return true;
		else
			return false;
	}

	/**
	 * Méthode qui effectue une mise a jour d'un commentaire
	 *
	 * @param	content	données du commentaire à mettre à jour
	 * @param	id	identifiant du commentaire
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/		
	public function editCommentaire($content, $id) {

		# Génération du nom du fichier
		$filename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		if(!file_exists($filename)) # Commentaire inexistant
			return plxMsg::Error('Le commentaire demand&eacute; n\'existe pas ou n\'existe plus');
		# On récupère les infos du commentaire
		$com = $this->parseCommentaire($filename);
		# On le remplace
		if($com['type'] != 'admin')
			$content['content'] = plxUtils::strCheck($content['content']);
		else
			$content['content'] = strip_tags($content['content'],'<a>,<strong>');
		$this->delCommentaire($id);
		$this->addCommentaire($filename,$com['author'],$com['type'],$com['ip'],$com['mail'],$com['site'],$content['content']);
		if(is_readable($filename))
			return plxMsg::Info('Commentaire modifi&eacute; avec succ&egrave;s');
		else
			return plxMsg::Error('Erreur lors de la mise &agrave; jour du commentaire');
	}
	
	/**
	 * Méthode qui supprime un commentaire
	 *
	 * @param	id	identifiant du commentaire à supprimer
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/	
	public function delCommentaire($id) {
	
		# Génération du nom du fichier
		$filename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		# Suppression du commentaire
		if(file_exists($filename)) {
			@unlink($filename);
		}
		if(!file_exists($filename))
			return plxMsg::Info('Suppression effectu&eacute;e avec succ&egrave;s');
		else
			return plxMsg::Error('Une erreur est survenue pendant la suppression');
	}
	
	/**
	 * Méthode qui permet de modérer ou valider un commentaire
	 *
	 * @param	id	identifiant du commentaire à traiter (que l'on retourne)
	 * @return	string
	 * @author	Stephane F. et Florent MONTHEL
	 **/	
	public function modCommentaire(&$id) {
	
		# Génération du nom du fichier
		$oldfilename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		if(!file_exists($oldfilename)) # Commentaire inexistant
			return plxMsg::Error('Le commentaire demand&eacute; n\'existe pas ou n\'existe plus');
		# Modérer ou valider ?
		if(preg_match('/^_/',$id)) {
			$type = 'val';
			$id = str_replace('_', '', $id);
		} else {
			$type = 'mod';
			$id = '_'.$id;
		}
		# Génération du nouveau nom de fichier
		$newfilename = PLX_ROOT.$this->aConf['racine_commentaires'].$id.'.xml';
		# On renomme le fichier
		@rename($oldfilename,$newfilename);
		# Contrôle
		if(is_readable($newfilename)) {
			if($type == 'val')
				return plxMsg::Info('Validation effectu&eacute;e avec succ&egrave;s');
			else
				return plxMsg::Info('Mod&eacute;ration effectu&eacute;e avec succ&egrave;s');
		} else {
			if($type == 'val')
				return plxMsg::Error('Une erreur est survenue pendant la validation');
			else
				return plxMsg::Error('Une erreur est survenue lors de la mod&eacute;ration');
		}	
	}

	/**
	 * Méthode qui sauvegarde la liste des tags dans fichier XML 
	 * selon le contenu de la variable de classe $aTags
	 *
	 * @param	null
	 * @return	null
	 * @author	Stephane F
	 **/
	public function editTags() {
	
		# Génération du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($this->aTags as $id => $tag) {
			$xml .= "\t".'<article number="'.$id.'" date="'.$tag['date'].'" active="'.$tag['active'].'"><![CDATA['.$tag['tags'].']]></article>'."\n";
		}
		$xml .= "</document>";

		# On écrit le fichier
		plxUtils::write($xml, PLX_ROOT.$this->aConf['tags']);

	}

	/**
	 * Méthode qui vérifie sur le site de PluXml la dernière version et la compare avec celle en local
	 *
	 * @return	string
	 * @author	Florent MONTHEL et Amaury GRAILLAT
	 **/
	public function checkMaj() {

		# La fonction est active ?
		if(!ini_get('allow_url_fopen')) return 'Impossible de v&eacute;rifier les mises &agrave; jour tant que \'allow_url_fopen\' est d&eacute;sactiv&eacute; sur ce syst&egrave;me';

		# Requete HTTP sur le site de PluXml
		$fp = @fopen('http://telechargements.pluxml.org/latest-version', 'r');
		$latest_version = trim(@fread($fp, 16));
		@fclose($fp);
		if($latest_version == '')
			return plxMsg::Error('La v&eacute;rification de mise &agrave; jour a &eacute;chou&eacute;e pour une raison inconnue');

		# Comparaison
		if(version_compare($this->version, $latest_version, ">="))
			return plxMsg::Info('Vous utilisez la derni&egrave;re version de PluXml ('.$this->version.')');
		else
		 	return plxMsg::Info('Une nouvelle version de PluXml est sortie ! Vous pouvez la t&eacute;l&eacute;charger sur <a href="http://pluxml.org/">PluXml.org</a>');
	}

}
?>