<?php

/**
 * Classe plxMedias regroupant les fonctions pour gérer la librairie des medias
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxMedias {

	public $aContent = array(); # contenu d'un dossier (dossier + fichier)
	public $imgQuality = 80; # qualite image
	public $miniWidth = 200; # largeur des miniatures
	public $miniHeight = 100; # hauteur des miniatures
	public $maxUpload = array(); # taille maxi des images
	
	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	dir	répertoire racine contenant les fichiers à charger
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($dir) {
	
		# Initialisation
		$this->path = $dir;
		# Taille maxi pour l'upload de fichiers sur le serveur
		$maxUpload = strtoupper(ini_get("upload_max_filesize"));
		$this->maxUpload['display'] = str_replace('M', ' Mo', $maxUpload);
		$this->maxUpload['display'] = str_replace('K', ' Ko', $this->maxUpload['display']);
		if(substr_count($maxUpload, 'K')) $this->maxUpload['value'] = str_replace('K', '', $maxUpload) * 1024;
		elseif(substr_count($maxUpload, 'M')) $this->maxUpload['value'] = str_replace('M', '', $maxUpload) * 1024 * 1024;
		else $this->maxUpload['value'] = 0;
	}
	
	/**
	 * Méthode récursive qui retourne un tableau de tous les dossiers et sous dossiers dans un répertoire
	 *
	 * @param	dir		repertoire de lecture
	 * @param	level	profondeur du repertoire
	 * @return	folders	tableau contenant la liste de tous les dossiers et sous dossiers
	 * @author	Stephane F
	 **/
	public function privGetAllDirs($dir,$level=0) {

		# Initialisation
		$folders = array();
		# Ouverture
		if($handle = opendir($this->path.$dir)) {
			while (FALSE !== ($folder = readdir($handle))) {
				if($folder[0] != '.') {
					if(is_dir($this->path.($dir!=''?$dir.'/':$dir).$folder)) {
						$dir = (substr($dir, -1)!='/' AND $dir!='') ? $dir.'/' : $dir;
						$folders[] = array(
								'level' => $level,
								'name' => $folder,
								'path' => $dir.$folder
							);
						$folders = array_merge($folders, $this->privGetAllDirs($dir.$folder, $level+1) );
					}
				}
            }	
			closedir($handle);
        }
       # On retourne le tableau
		return $folders;
	}	

	/**
	 * Méthode récursive qui supprimes tous les dossiers et les fichiers d'un répertoire
	 *
	 * @param	deldir	répertoire de suppression
	 * @return	boolean	résultat de la suppression 
	 * @author	Stephane F
	 **/
	public function privDelDir($deldir) { #fonction récursive
	
		if(is_dir($this->path.$deldir) AND !is_link($this->path.$deldir)) {
			if($dh = @opendir($this->path.$deldir)) {
				while(FALSE !== ($file = readdir($dh))) {
					if($file != '.' AND $file != '..') {
						$this->privDelDir(($deldir!='' ? $deldir.'/' : '').$file);
					} 
				}
				closedir($dh);
			}
			return rmdir($this->path.$deldir);
		}
		return unlink($this->path.$deldir);
	}	

	/**
	 * Méthode qui déplace un fichier (et sa vignette si elle existe dans le cas d'une image)
	 *
	 * @param	dir			répertoire de lecture
	 * @param	src_file	fichier source
	 * @param	dst_path	chemin de destination du déplacement du fichier
	 * @return	boolean		résultat du déplacement
	 * @author	Stephane F
	 **/
	public function privMoveFile($dir,$src_file,$dst_path) {
		
		# Initialisation
		if($dst_path == '') $dst_path = $this->path;
		$dst_path = (substr($dst_path, -1) != '/' AND $dst_path != '') ? $dst_path.'/' : $dst_path;			
		$result = true;
		# Déplacement du fichier
		if(is_readable($this->path.$src_file)) {
			$result = rename($this->path.$src_file, $dst_path.basename($src_file));
		}
		# Déplacement de la miniature
		if($result AND is_readable($this->path.$src_file.'.tb')) {
			$result = rename($this->path.$src_file.'.tb', $dst_path.basename($src_file).'.tb');
		}
		# On retourne le booleen
		return $result;
	}
	
	/**
	 * Méthode qui régénère une miniature
	 *
	 * @param	file	image source
	 * @return	boolean	résultat de la création
	 * @author	Stephane F
	 **/
	public function privMakeThumb($file) {

		# On supprime la miniature si elle existe
		@unlink($file.'.tb');
		# on recrée la miniature
		$return = @plxUtils::makeThumb($file, $file.'.tb',$this->miniWidth,$this->miniHeight,$this->imgQuality);
		@chmod($file.'.tb',0644);
		return $return;
	}

	/**
	 * Méthode qui retourne la liste des dossiers et des fichiers d'un répertoire
	 *
	 * @param	dir	répertoire de lecture
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getDirContent($dir) {

		# Pour remonter au niveau - 1 (si besoin est)
		if($dir != '') {
			$d = dirname($dir) == '.' ? '' : dirname($dir);
			$this->aContent['folders'][ $d ] = '../';
		}
		# Ajout des dossiers et des fichiers du répertoire demandé
		if($handle = @opendir($this->path.$dir)) {
			while(FALSE !== ($file = readdir($handle))) {
				if($file[0] != '.') {
					# Fichier
					if(is_file($this->path.$dir.$file)) {
						if(substr($file,-3) != '.tb')
							$this->aContent['files'][ $dir.$file ] = array('name' => $file, 'date' => filemtime($this->path.$dir.$file));
					}
					# Dossier
					elseif (is_dir($this->path.$dir.$file)) {
						$this->aContent['folders'][ $dir.$file ] = $file;
					}
				}
            }
			closedir($handle);
        }
		# On tri le contenu
		ksort($this->aContent);
    }
	
	/**
	 * Méthode qui formate l'affichage de la liste déroulante des dossiers où peut être déplacé un fichier
	 *
	 * @param	dir		répertoire de lecture
	 * @return	string	chaine formatée à afficher
	 * @author	Stephane F
	 **/	
	public function getDirs($dir) {
	
		# Initialisation
		$folders = $this->privGetAllDirs($this->path);
		$str  = "\n".'<option value="0">D&eacute;placer ? &nbsp; </option>';
		$str .= "\n".'<option value="">|. (racine) &nbsp; </option>';
		# Dir non vide
		if(!empty($folders)) {	
			foreach($folders as $k => $v) {
				if($this->path.$dir != $v['path']) {
					if($v['level'] != 0) {
						$prefixe = '|';
						$i = 0;
						while($i < $v['level']) {
							$prefixe .= '&nbsp;&nbsp;';
							$i++;
						}
						$prefixe .= '';
					} else {
						$prefixe = '|';
					}
					$str .= "\n".'<option value="'.$v['path'].'">'.$prefixe.$v['name'].'</option>';
				}
			}
		}
		# On retourne la chaine
		return $str;
	}
	
	/**
	 * Méthode qui crée un nouveau dossier
	 *
	 * @param	dir		répertoire source
	 * @param	newdir	nom du répertoire à créer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function newDir($dir, $newdir) {
	
		# Initialisation
		$newdir = $this->path.$dir.plxUtils::title2filename(trim($newdir));
		if(!is_dir($newdir)) { # Si le dossier n'existe pas on le créer
			if(!@mkdir($newdir,0755))
				return plxMsg::Error('Impossible de cr&eacute;er le dossier');
			else
				return plxMsg::Info('Dossier cr&eacute;&eacute; avec succ&egrave;s ');
		} else {
			return plxMsg::Error('Ce dossier existe d&eacute;j&agrave;');
		}

    }
	
	/**
	 * Méthode qui supprime le contenu d'un dossier
	 *
	 * @param	deldir	répertoire à supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function delDir($deldir) {
		
		# Utilisation des méthodes privées
		if($this->privDelDir($deldir))
			return plxMsg::Info('Dossier supprim&eacute; avec succ&egrave;s');
		else
			return plxMsg::Error('Erreur pendant la suppression du dossier');
	}

	/**
	 * Méthode qui supprime un fichier (et sa vignette si elle existe dans le cas d'une image)
	 *
	 * @param	delfile	fichier à supprimer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function delFile($delfile) {

		if(!@unlink($this->path.$delfile)) # Erreur de suppression
			$result = plxMsg::Error('Impossible de supprimer le fichier (probl&egrave;me d\'&eacute;criture dans le dossier '.$delfile.')');
		else # Ok
			$result = plxMsg::Info('Fichier supprim&eacute; avec succ&egrave;s');
		# Suppression de la vignette
		if(is_file($this->path.$delfile.'.tb')) @unlink($this->path.$delfile.'.tb');
		return $result;

	}
	
	/**
	 * Méthode qui déplace une ou plusieurs fichiers à la fois
	 *
	 * @param	dir		répertoire de lecture
	 * @param   files	liste des fichier à déplacer
	 * @return  boolean	faux si erreur sinon vrai
	 * @author	Stephane F
	 **/
	public function moveFile($dir,$files) {

		# Initialisation
		$result = true;
		$count = 0;
		# On a des fichiers
		if($files) {
			foreach($files as $src => $dst) {
				if($dst != '0') {
					$count++;
					$result = $result AND $this->privMoveFile($dir, $src, $dst);
				}
			}
			# On a des résultats
			if($result) {
				if ($count>1)
					return plxMsg::Info('Fichiers d&eacute;plac&eacute;s avec succ&egrave;s');
				else
					return plxMsg::Info('Fichier d&eacute;plac&eacute; avec succ&egrave;s');
 			} else {
				if($count>1)
					return plxMsg::Error('Erreur pendant le d&eacute;eplacement des fichiers');
				else
					return plxMsg::Error('Erreur pendant le d&eacute;eplacement du fichier');
			}
		}
	
	}
	
	/**
	 * Méthode qui envoi un fichier sur le serveur
	 *
	 * @param	dir		répertoire de lecture
	 * @param	files	données sur les fichiers à uploader de type $_FILES
	 * @param	medias	type de medias uploadé (images ou documents)
	 * @return  msg		message contenant le résultat de l'envoie du fichier
	 * @author	Stephane F
	 **/
	public function upload($dir, $files, $medias) {

		$nbfile = 0;
		if(($nbfiles = sizeof($files['name'])) > 0) {
			for($numfile=0;$numfile<$nbfiles;$numfile++) {
				if($files['name'][$numfile]!='') {
					# controle de l'extension du fichier si envoie d'une image
					$ext = strtolower(strrchr(basename($files['name'][$numfile]),'.'));
					if($medias=='images' AND !in_array($ext, array('.gif', '.jpg', '.png')))
						$msg_error = 'V&eacute;rifiez le format de vos images: gif, jpg et png uniquement';
					elseif($medias=='documents' AND (substr($ext,0,4)=='.php' OR in_array($ext, array('.cgi'))))
						$msg_error = "Type de fichier non autoris&eacute;: php, cgi";
						
					else {
						# On teste l'existence du fichier et on formate le nom du fichier
						$i = 0;
						$upFile = $this->path.$dir.'/'.plxUtils::title2filename(basename($files['name'][$numfile]));
						while(file_exists($upFile)) {
							$upFile = $this->path.$dir.'/'.$i.plxUtils::title2filename(basename($files['name'][$numfile]));
							$i++;
						}
						if($files['size'][$numfile] > $this->maxUpload['value']) {
							$msg_error = 'La taille d\'un fichier est sup&eacute;rieure &agrave; '.$this->maxUpload['display'];
						} elseif(!@move_uploaded_file($files['tmp_name'][$numfile],$upFile)) { # Erreur de copie
							$msg_error = 'Impossible d\'envoyer les fichiers (probl&egrave;me d\'&eacute;criture dans le dossier)';
						} else { # Ok
							@chmod($upfile,0644);
							@plxUtils::makeThumb($upFile, $upFile.'.tb',$this->miniWidth,$this->miniHeight,$this->imgQuality);
							@chmod($upfile.'.tb',0644);
							$nbfile++;
						}
					}
				}
				# Si erreur détectée on retourne le message
				if(!empty($msg_error)) return plxMsg::Error($msg_error);
				if(!empty($msg_info)) return plxMsg::Info($msg_info);
			}
			if($nbfile>1)
				return plxMsg::Info('Fichiers envoy&eacute;s avec succ&egrave;s');
			else
				return plxMsg::Info('Fichier envoy&eacute; avec succ&egrave;s');
		}
	}

	/**
	 * Méthode qui recrée toutes les vignettes d'un dossier
	 *
	 * @param	dir		répertoire de lecture
	 * @return  msg		message contenant le résultat du traitement
	 * @author	Stephane F
	 **/
	public function makeThumbs($dir) {

		# Ajout des dossiers et des fichiers du répertoire demandé
		if($handle = @opendir($this->path.$dir)) {
			$res = true;
			while (FALSE !== ($filename = readdir($handle))) {
				$file = $this->path.$dir.'/'.$filename;
				if (is_file($file) AND $filename[0] != '.' AND substr(strrchr($filename, '.'), 1)!='tb') {
					# On recrée la miniature avec la méthode privée"
					$res = ($this->privMakeThumb($file) AND $res);
				}
			}
			closedir($handle);
			if ($res) return plxMsg::Info('Miniatures recr&eacute;&eacute;es');
			else return plxMsg::Error('Erreur pendant la cr&eacute;ation des miniatures');
		}
		return plxMsg::Info('Aucune miniature &agrave; cr&eacute;er');
	}	
	
	/**
	 * Méthode qui recrée la vignettes d'une image
	 *
	 * @param	filename	nom de l'image
	 * @return  msg			message contenant le résultat du traitement
	 * @author	Stephane F
	 **/
	public function makeThumb($filename) {
		
		# Initialisation
		$file = $this->path.$dir.'/'.$filename;
		# Test
		if(file_exists($file) AND substr(strrchr($filename, '.'), 1) != 'tb') {
			# On recrée la miniature
			if ($this->privMakeThumb($file)) return 'Miniature recr&eacute;&eacute;e avec succ&egrave;s';
			else return plxMsg::Error('Erreur pendant la cr&eacute;ation de la miniature');
		} else {
			return plxMsg::Info('Aucune miniature &agrave; cr&eacute;er');
		}
	}
	
}
?>