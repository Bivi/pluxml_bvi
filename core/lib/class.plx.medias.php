<?php

/**
 * Classe plxMedias regroupant les fonctions pour g�rer la librairie des medias
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxMedias {

	public $path = null; # chemin vers les m�dias
	public $dir = null;
	public $aDirs = array(); # liste des dossiers et sous dossiers
	public $aFiles = array(); # liste des fichiers d'un dossier
	public $maxUpload = array(); # taille maxi des images


	public $thumbQuality = 60; # qualite image
	public $thumbWidth = 60; # largeur des miniatures
	public $thumbHeight = 60; # hauteur des miniatures

	public $img_exts = '/\.(jpg|gif|png|bmp|jpeg)$/i';
	public $doc_exts = '/\.(7z|aiff|asf|avi|csv|doc|docx|fla|flv|gz|gzip|mid|mov|mp3|mp4|mpc|mpeg|mpg|ods|odt|pdf|ppt|pptx|pxd|qt|ram|rar|rm|rmi|rmvb|rtf|swf|sxc|sxw|tar|tgz|txt|wav|wma|wmv|xls|xlsx|zip)$/i';

	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	path	r�pertoire racine des m�dias
	 * @param	dir		dossier de recherche
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($path, $dir) {

		# Initialisation
		$this->path = $path;
		$this->dir = $dir;

		# Cr�ation du dossier r�serv� � l'utilisateur connect� s'il n'existe pas
		if(!is_dir($this->path)) {
			if(!@mkdir($this->path,0755))
				return plxMsg::Error(L_PLXMEDIAS_MEDIAS_FOLDER_ERR);
		}
		# Cr�ation du dossier r�serv� aux miniatures
		if(!is_dir($this->path.'.thumbs/'.$this->dir)) {
			@mkdir($this->path.'.thumbs/'.$this->dir,0755,true);
		}

		$this->aDirs = $this->_getAllDirs($this->path);
		$this->aFiles = $this->_getDirFiles($this->dir);

		# Taille maxi pour l'upload de fichiers sur le serveur
		$maxUpload = strtoupper(ini_get("upload_max_filesize"));
		$this->maxUpload['display'] = str_replace('M', ' Mo', $maxUpload);
		$this->maxUpload['display'] = str_replace('K', ' Ko', $this->maxUpload['display']);
		if(substr_count($maxUpload, 'K')) $this->maxUpload['value'] = str_replace('K', '', $maxUpload) * 1024;
		elseif(substr_count($maxUpload, 'M')) $this->maxUpload['value'] = str_replace('M', '', $maxUpload) * 1024 * 1024;
		else $this->maxUpload['value'] = 0;
	}

	/**
	 * M�thode r�cursive qui retourne un tableau de tous les dossiers et sous dossiers dans un r�pertoire
	 *
	 * @param	dir		repertoire de lecture
	 * @param	level	profondeur du repertoire
	 * @return	folders	tableau contenant la liste de tous les dossiers et sous dossiers
	 * @author	Stephane F
	 **/
	private function _getAllDirs($dir,$level=0) {

		# Initialisation
		$folders = array();
		# Ouverture et lecture du dossier demand�
		if($handle = opendir($dir)) {
			while (FALSE !== ($folder = readdir($handle))) {
				if($folder[0] != '.') {
					if(is_dir(($dir!=''?$dir.'/':$dir).$folder)) {
						$dir = (substr($dir, -1)!='/' AND $dir!='') ? $dir.'/' : $dir;
						$path = str_replace($this->path, '',$dir.$folder.'/');
						$folders[] = array(
								'level' => $level,
								'name' => $folder,
								'path' => $path
							);

						# Cr�ation du dossier r�serv� aux miniatures
						if(!is_dir($path.'.thumbs')) {
							@mkdir($path.'.thumbs',0755,true);
						}
						$folders = array_merge($folders, $this->_getAllDirs($dir.$folder, $level+1) );
					}
				}
            }
			closedir($handle);
        }
		# On retourne le tableau
		return $folders;
	}

	/**
	 * M�thode qui retourne la liste des des fichiers d'un r�pertoire
	 *
	 * @param	dir		r�pertoire de lecture
	 * @return	files	tableau contenant la liste de tous les fichiers d'un dossier
	 * @author	Stephane F
	 **/
	private function _getDirFiles($dir) {

		# Initialisation
		$files = array();
		# Ouverture et lecture du dossier demand�
		if($handle = @opendir($this->path.$dir)) {
			while(FALSE !== ($file = readdir($handle))) {
				$thumName = plxUtils::thumbName($file);
				if($file[0] != '.' AND !preg_match('/index.htm/i', $file) AND !preg_match('/^(.*\.)tb.([^.]+)$/D', $file)) {
					if(is_file($this->path.$dir.$file)) {
						$ext = strtolower(strrchr($this->path.$dir.$file,'.'));
						$_thumb1=file_exists($this->path.'.thumbs/'.$dir.$file);
						if(!$_thumb1 AND in_array($ext, array('.gif', '.jpg', '.png'))) {
							$_thumb1 = plxUtils::makeThumb($this->path.$dir.$file, $this->path.'.thumbs/'.$dir.$file, $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
						}
						$_thumb2=false;
						if(is_file($this->path.$dir.$thumName)) {
							$_thumb2 = array(
								'infos' => @getimagesize($this->path.$dir.$thumName),
								'filesize'	=> filesize($this->path.$dir.$thumName)
							);
						}
						$files[$file] = array(
							'.thumb'	=> $_thumb1 ? $this->path.'.thumbs/'.$dir.$file : PLX_CORE.'admin/theme/images/file.png',
							'name' 		=> $file,
							'path' 		=> $this->path.$dir.$file,
							'date' 		=> filemtime($this->path.$dir.$file),
							'filesize' 	=> filesize($this->path.$dir.$file),
							'extension'	=> $ext,
							'infos' 	=> @getimagesize($this->path.$dir.$file),
							'thumb' 	=> $_thumb2
						);
					}
				}
            }
			closedir($handle);
        }
		# On tri le contenu
		ksort($files);
		# On retourne le tableau
		return $files;
    }

	/**
	 * M�thode qui formate l'affichage de la liste d�roulante des dossiers
	 *
	 * @return	string	chaine format�e � afficher
	 * @author	Stephane F
	 **/
	public function contentFolder() {

		$str  = "\n".'<select class="folder" id="folder" size="1" name="folder">'."\n";
		$selected = (empty($this->dir)?'selected="selected" ':'');
		$str .= '<option '.$selected.'value=".">|. ('.L_PLXMEDIAS_ROOT.') &nbsp; </option>'."\n";
		# Dir non vide
		if(!empty($this->aDirs)) {
			foreach($this->aDirs as $k => $v) {
				$prefixe = '|&nbsp;&nbsp;';
				$i = 0;
				while($i < $v['level']) {
					$prefixe .= '&nbsp;&nbsp;';
					$i++;
				}
				$selected = ($v['path']==$this->dir?'selected="selected" ':'');
				$str .= '<option '.$selected.'value="'.$v['path'].'">'.$prefixe.$v['name'].'</option>'."\n";
			}
		}
		$str  .= '</select>'."\n";

		# On retourne la chaine
		return $str;
	}


	/**
	 * M�thode qui supprime un fichier (et sa vignette si elle existe dans le cas d'une image)
	 *
	 * @param	files	liste des fichier � supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function deleteFiles($files) {

		$count = 0;
		foreach($files as $file) {
			# protection pour ne pas supprimer un fichier en dehors de $this->path.$this->dir
			$file=basename($file);
			if(!@unlink($this->path.$this->dir.$file)) {
				$count++;
			} else {
				# Suppression de la vignette
				if(is_file($this->path.'.thumbs/'.$this->dir.$file))
					@unlink($this->path.'.thumbs/'.$this->dir.$file);
				# Suppression de la miniature
				$thumName = plxUtils::thumbName($file);
				if(is_file($this->path.$this->dir.$thumName))
					@unlink($this->path.$this->dir.$thumName);
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Info(L_PLXMEDIAS_DELETE_FILE_SUCCESSFUL);
			else
				return plxMsg::Error(L_PLXMEDIAS_DELETE_FILE_ERR);
		}
		else {
			if($count==0)
				return plxMsg::Info(L_PLXMEDIAS_DELETE_FILES_SUCCESSFUL);
			else
				return plxMsg::Error(L_PLXMEDIAS_DELETE_FILES_ERR);
		}
	}


	/**
	 * M�thode r�cursive qui supprimes tous les dossiers et les fichiers d'un r�pertoire
	 *
	 * @param	deldir	r�pertoire de suppression
	 * @return	boolean	r�sultat de la suppression
	 * @author	Stephane F
	 **/
	private function _deleteDir($deldir) { #fonction r�cursive

		if(is_dir($deldir) AND !is_link($deldir)) {
			if($dh = @opendir($deldir)) {
				while(FALSE !== ($file = readdir($dh))) {
					if($file != '.' AND $file != '..') {
						$this->_deleteDir($deldir.'/'.$file);
					}
				}
				closedir($dh);
			}
			return rmdir($deldir);
		}
		return unlink($deldir);
	}

	/**
	 * M�thode qui supprime un dossier et son contenu
	 *
	 * @param	deleteDir	r�pertoire � supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) {

		# suppression des miniatures
		$this->_deleteDir($this->path.'.thumbs/'.$deldir);

		# suppression du dossier
		if($this->_deleteDir($this->path.$deldir))
			return plxMsg::Info(L_PLXMEDIAS_DEL_FOLDER_SUCCESSFUL);
		else
			return plxMsg::Error(L_PLXMEDIAS_DEL_FOLDER_ERR);
	}

	/**
	 * M�thode qui cr�e un nouveau dossier
	 *
	 * @param	newdir	nom du r�pertoire � cr�er
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function newDir($newdir) {

		$newdir = $this->path.$this->dir.$newdir;

		if(!is_dir($newdir)) { # Si le dossier n'existe pas on le cr�er
			if(!@mkdir($newdir,0755))
				return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_NEW_FOLDER_SUCCESSFUL);
		} else {
			return plxMsg::Error(L_PLXMEDIAS_NEW_FOLDER_EXISTS);
		}
    }

	/**
	 * M�thode qui envoi un fichier sur le serveur
	 *
	 * @param	file	fichier � uploader
	 * @param	resize	taille du fichier � redimensionner si renseign�
	 * @param	thumb	taille de la miniature � cr�er si renseign�
	 * @return  msg		message contenant le r�sultat de l'envoi du fichier
	 * @author	Stephane F
	 **/
	private function _uploadFile($file, $resize, $thumb) {

		if($file['name'] == '')
			return false;

		if($file['size'] > $this->maxUpload['value'])
			return L_PLXMEDIAS_WRONG_FILESIZE;

		if(!preg_match($this->img_exts, $file['name']) AND !preg_match($this->doc_exts, $file['name']))
			return L_PLXMEDIAS_WRONG_FILEFORMAT;

		# On teste l'existence du fichier et on formate le nom du fichier pour �viter les doublons
		$i = 0;
		$upFile = $this->path.$this->dir.plxUtils::title2filename($file['name']);
		while(file_exists($upFile)) {
			$upFile = $this->path.$this->dir.$i.plxUtils::title2filename($file['name']);
			$i++;
		}

		if(!@move_uploaded_file($file['tmp_name'],$upFile)) { # Erreur de copie
			return L_PLXMEDIAS_UPLOAD_ERR;
		} else { # Ok
			if(preg_match($this->img_exts, $file['name'])) {
				plxUtils::makeThumb($upFile, $this->path.'.thumbs/'.$this->dir.basename($upFile), $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
				if($resize)
					plxUtils::makeThumb($upFile, $upFile, $resize['width'], $resize['height'], 80);
				if($thumb)
					plxUtils::makeThumb($upFile, plxUtils::thumbName($upFile), $thumb['width'], $thumb['height'], 80);
			}
		}
		return L_PLXMEDIAS_UPLOAD_SUCCESSFUL;
	}

	/**
	 * M�thode qui envoi une liste de fichiers sur le serveur
	 *
	 * @param	files	fichiers � uploader
	 * @param	post	parametres
	 * @return  msg		resultat de l'envoi des fichiers
	 * @author	Stephane F
	 **/
	public function uploadFiles($files, $post) {
		$count=0;
		foreach($files as $file) {
			$resize = false;
			$thumb = false;
			if(!empty($post['resize'])) {
				if($post['resize']=='user') {
					$resize = array('width' => intval($post['user_w']), 'height' => intval($post['user_h']));
				} else {
					list($width,$height) = explode('x', $post['resize']);
					$resize = array('width' => $width, 'height' => $height);
				}
			}
			if(!empty($post['thumb'])) {
				if($post['thumb']=='user') {
					$thumb = array('width' => intval($post['thumb_w']), 'height' => intval($post['thumb_h']));
				} else {
					list($width,$height) = explode('x', $post['thumb']);
					$thumb = array('width' => $width, 'height' => $height);
				}
			}
			if($res=$this->_uploadFile($file, $resize, $thumb)) {
				switch($res) {
					case L_PLXMEDIAS_WRONG_FILESIZE:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILESIZE);
						break;
					case L_PLXMEDIAS_WRONG_FILEFORMAT:
						return plxMsg::Error(L_PLXMEDIAS_WRONG_FILEFORMAT);
						break;
					case L_PLXMEDIAS_UPLOAD_ERR:
						return plxMsg::Error(L_PLXMEDIAS_UPLOAD_ERR);
						break;
					case L_PLXMEDIAS_UPLOAD_SUCCESSFUL:
						$count++;
						break;
				}
			}
		}

		if($count==1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOAD_SUCCESSFUL);
		elseif($count>1)
			return plxMsg::Info(L_PLXMEDIAS_UPLOADS_SUCCESSFUL);
	}

	/**
	 * M�thode qui d�place une ou plusieurs fichiers
	 *
	 * @param   files		liste des fichier � d�placer
	 * @param	src_dir		r�pertoire source
	 * @param	dst_dir		r�pertoire destination
	 * @return  boolean		faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function moveFiles($files, $src_dir, $dst_dir) {

		if($dst_dir=='.') $dst_dir='';

		$count = 0;
		foreach($files as $file) {
			# protection pour ne pas d�placer un fichier en dehors de $this->path.$this->dir
			$file=basename($file);

			# D�placement du fichier
			if(is_readable($this->path.$src_dir.$file)) {
				$result = rename($this->path.$src_dir.$file, $this->path.$dst_dir.$file);
				$count++;
			}
			# D�placement de la miniature
			$thumbName = plxUtils::thumbName($file);
			if($result AND is_readable($this->path.$src_dir.$thumbName)) {
				$result = rename($this->path.$src_dir.$thumbName, $this->path.$dst_dir.$thumbName);
			}
			# D�placement de la vignette
			if($result AND is_readable($this->path.'.thumbs/'.$src_dir.$file)) {
				$result = rename($this->path.'.thumbs/'.$src_dir.$file, $this->path.'.thumbs/'.$dst_dir.$file);
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_MOVE_FILE_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_MOVE_FILE_SUCCESSFUL);
		}
		else {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_MOVE_FILES_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_MOVE_FILES_SUCCESSFUL);
		}

	}

	/**
	 * M�thode qui recr�er les vignettes dans .thumb
	 *
	 * @param   files		liste des fichier � d�placer
	 * @param	dir			r�pertoire source des images
	 * @return  boolean		faux si erreur sinon vrai
	 * @author	Stephane F
	 **/	
	public function doThumbs($files, $dir) {

		# Cr�ation du dossier r�serv� aux vignettes
		if(!is_dir($this->path.'.thumbs/'.$dir)) {
			@mkdir($this->path.'.thumbs/'.$dir,0755,true);
		}

		$count = 0;
		foreach($files as $file) {
			$file=basename($file);
			if(is_file($this->path.$dir.$file)) {
				$ext = strtolower(strrchr($this->path.$dir.$file,'.'));
				if(in_array($ext, array('.gif', '.jpg', '.png'))) {
					$_thumb1 = plxUtils::makeThumb($this->path.$dir.$file, $this->path.'.thumbs/'.$dir.$file, $this->thumbWidth, $this->thumbHeight, $this->thumbQuality);
					$count++;
				}
			}
		}

		if(sizeof($files)==1) {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_RECREATE_THUMB_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_RECREATE_THUMB_SUCCESSFUL);
		}
		else {
			if($count==0)
				return plxMsg::Error(L_PLXMEDIAS_RECREATE_THUMBS_ERR);
			else
				return plxMsg::Info(L_PLXMEDIAS_RECREATE_THUMBS_SUCCESSFUL);
		}

	}
}
?>
