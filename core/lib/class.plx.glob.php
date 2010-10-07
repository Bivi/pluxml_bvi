<?php

/**
 * Classe plxGlob responsable de la récupération des fichiers à traiter
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Amaury Graillat et Stéphane F.
 **/
class plxGlob {

	public $count = 0; # Le nombre de resultats
	public $aFiles = array(); # Tableau des fichiers

	private $dir = false; # Repertoire a checker
	private $onlyfilename = false; # Booleen indiquant si notre resultat sera relatif ou absolu
	private $rep = false; # Boolean pour ne lister que les dossiers
	
	private static $instance = array();

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @param	dir				repertoire à lire
	 * @param	rep				boolean pour ne prendre que les répertoires sans les fichiers
	 * @param	onlyfilename	boolean pour ne récupérer que le nom des fichiers sans le chemin
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Amaury Graillat
	 **/
	private function __construct($dir,$rep=false,$onlyfilename=true) {

		# On initialise les variables de classe
		$this->dir = $dir;
		$this->rep = $rep;
		$this->onlyfilename = $onlyfilename;
		$this->initCache();
	}

	/**
	 * Méthode qui se charger de créer le Singleton plxGlob
	 *
	 * @param	dir				répertoire à lire
	 * @param	rep				boolean pour ne prendre que les répertoires sans les fichiers
	 * @param	onlyfilename	boolean pour ne récupérer que le nom des fichiers sans le chemin	 
	 * @return	objet			return une instance de la classe plxGlob
	 * @author	Stephane F
	 **/
	public static function getInstance($dir,$rep=false,$onlyfilename=true){
		$basename = basename($dir);
		if (!isset(self::$instance[$basename]))
			self::$instance[$basename] = new plxGlob($dir,$rep,$onlyfilename);
		return self::$instance[$basename];
	}

	/**
	 * Méthode qui se charger de mémoriser le contenu d'un dossier
	 *
	 * @return	null
	 * @author	Amaury Graillat
	 **/
	private function initCache() {
		
		if(is_dir($this->dir)) {
			# On ouvre le repertoire
			if($dh = opendir($this->dir)) {
				# Récupération du dirname
				if($this->onlyfilename) # On recupere uniquement le nom du fichier
					$dirname = '';
				else # On concatene egalement le nom du repertoire
					$dirname = $this->dir;
				# Pour chaque entree du repertoire
				while(false !== ($file = readdir($dh))) {
					if($file[0]!='.') {
						$dir = is_dir($this->dir.'/'.$file);
						if($this->rep AND $dir) {
							$this->aFiles[] = $dirname.$file;
						}
						elseif(!$this->rep AND !$dir) {
							$this->aFiles[] = $file;
						}
					}
				}
				# On ferme la ressource sur le repertoire
				closedir($dh);
			}
		}
	}

	/**
	 * Méthode qui cherche les fichiers correspondants au motif $motif
	 *
	 * @param	motif			motif de recherche des fichiers sous forme d'expression réguliere
	 * @param	tri				type de recherche (article, commentaire ou autre)
	 * @param	publi			recherche des fichiers avant ou après la date du jour
	 * @return	array ou false
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stephane F
	 **/
	private function search($motif,$tri,$publi) {

		$this->count = 0;

		if($this->aFiles) {

			# Pour chaque entree du repertoire
			foreach ($this->aFiles as $file) {

				if(preg_match($motif,$file)) {

					if($tri === 'art') { # Tri selon les dates de publication (article)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						if($publi === 'before' AND $index[3] <= @date('YmdHi'))
							$array[ $index[3].$index[0] ] = $file;
						elseif($publi === 'after' AND $index[3] >= @date('YmdHi'))
							$array[ $index[3].$index[0] ] = $file;
						elseif($publi === 'all')
							$array[ $index[3].$index[0] ] = $file;
						# On verifie que l'index existe
						if(isset($array[ $index[3].$index[0] ]))
							$this->count++; # On incremente le compteur
					}
					elseif($tri === 'com') { # Tri selon les dates de publications (commentaire)
						# On decoupe le nom du fichier
						$index = explode('.',$file);
						# On cree un tableau associatif en choisissant bien nos cles et en verifiant la date de publication
						if($publi === 'before' AND $index[1] <= time())
							$array[ $index[1].$index[0] ] = $file;
						elseif($publi === 'after' AND $index[1] >= time())
							$array[ $index[1].$index[0] ] = $file;
						elseif($publi === 'all')
							$array[ $index[1].$index[0] ] = $file;
						# On verifie que l'index existe
						if(isset($array[ $index[1].$index[0] ]))
							$this->count++; # On incremente le compteur
					}
					else { # Aucun tri
						$array[] = $file;
						# On incremente le compteur
						$this->count++;
					}
				}
			}
		}

		# On retourne le tableau si celui-ci existe
		if($this->count > 0)
			return $array;
		else
			return false;
	}

	/**
	 * Méthode qui retourne un tableau trié, des fichiers correspondants 
	 * au motif $motif, respectant les différentes limites
	 *
	 * @return	array ou false
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function query($motif,$tri='',$ordre='',$depart='0',$limite=false,$publi='all') {

		# Si on a des resultats
		if($rs = $this->search($motif,$tri,$publi)) {

			# Ordre de tri du tableau
			if($ordre === 'sort' AND $tri != '')
				ksort($rs);
			elseif($ordre === 'rsort' AND $tri != '')
				krsort($rs);
			elseif($ordre === 'sort' AND $tri == '')
				sort($rs);
			else
				rsort($rs);

			# On enleve les cles du tableau
			$rs = array_values($rs);
			# On a une limite, on coupe le tableau
			if($limite)
				$rs = array_slice($rs,$depart,$limite);
			# On retourne le tableau
			return $rs;
		}
		# On retourne une valeur négative
		return false;
	}

}
?>