<?php

/**
 * Classe plxMotor responsable du traitement global du script
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
 **/
class plxMotor {

	public $version = false; # Version de PluXml
	public $start = false; # Microtime du debut de l'execution de PluXml
	public $get = false; # Donnees variable GET
	public $racine = false; # Url de PluXml
	public $path_url = false; # chemin de l'url du site
	public $style = false; # Dossier contenant le thème
	public $tri; # Tri d'affichage des articles
	public $tri_coms; # Tri d'affichage des commentaires
	public $bypage = false; # Pagination des articles
	public $page = 1; # Numéro de la page
	public $motif = false; # Motif de recherche
	public $mode = false; # Mode de traitement
	public $template = false; # template d'affichage
	public $cible = false; # Article, categorie ou page statique cible
	public $message_com = false; # Message à la création d'un commentaire

	public $aConf = array(); # Tableau de configuration
	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aFiles = array(); # Tableau de fichiers à traiter
	public $aTags = array(); # Tableau des tags
	public $aUsers = array(); #Tableau des utilisateurs

	public $plxGlob_arts = null; # Objet plxGlob des articles
	public $plxGlob_coms = null; # Objet plxGlob des commentaires
	public $plxRecord_arts = null; # Objet plxRecord des articles
	public $plxRecord_coms = null; # Objet plxRecord des commentaires
	public $plxCapcha = null; # Objet plxCapcha
	public $plxErreur = null; # Objet plxErreur
	
	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function __construct($filename) {

		# Version de PluXml
		if(!is_readable(PLX_ROOT.'version')) {
			header('Content-Type: text/plain charset=UTF-8');
			echo 'Le fichier "'.PLX_ROOT.'version" est necessaire au fonctionnement de PluXml';
			exit;
		}
		$f = file(PLX_ROOT.'version');
		$this->version = $f['0'];		
			
		# Traitement initial
		$this->start = plxDate::microtime();
		$this->get = plxUtils::getGets();
		# On parse le fichier de configuration
		$this->getConfiguration($filename);
		# On vérifie s'il faut faire une mise à jour
		if((!isset($this->aConf['version']) OR $this->version!=$this->aConf['version']) AND !defined('PLX_UPDATER')) {
			header('Location: '.PLX_ROOT.'update/index.php');
			exit;
		}
		# Chargement des variables
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage'];
		$this->tri = $this->aConf['tri'];
		$this->tri_coms = $this->aConf['tri_coms'];
		# On récupère le chemin de l'url
		$var = parse_url($this->racine);
		$this->path_url = str_replace(ltrim($var['path'], '\/'), '', ltrim($_SERVER['REQUEST_URI'], '\/'));
		
		# Definition du thème à afficher
		if(plxUtils::mobileDetect() AND !empty($this->aConf['style_mobile']) AND is_dir(PLX_ROOT.'themes/'.$this->aConf['style_mobile']))
			$this->style = $this->aConf['style_mobile'];
		else
			$this->style = $this->aConf['style'];
		# Traitement sur les répertoires des articles et des commentaires
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
		# On récupère les catégories et les pages statiques
		$this->getCategories(PLX_ROOT.$this->aConf['categories']);
		$this->getStatiques(PLX_ROOT.$this->aConf['statiques']);
		$this->getTags(PLX_ROOT.$this->aConf['tags']);
		$this->getUsers(PLX_ROOT.$this->aConf['users']);	
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine 
	 * le mode à appliquer. Cette méthode alimente ensuite les variables 
	 * de classe adéquates
	 *
	 * @param	mode	mode du moteur à appliquer
	 * @param	motif	motif de recherche à appliquer
	 * @param	bypage	pagination (nombre d'articles) à appliquer
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function prechauffage($mode='',$motif='',$bypage='') {

		if($mode != '' AND $motif != '') {
			$this->mode = $mode; # Mode
			$this->motif = $motif; # Motif de recherche
			$this->bypage = $bypage; # Nombre d'article par page
			$this->template = $mode.'.php';
		}
		elseif($this->get AND preg_match('/^article([0-9]+)\//',$this->get,$capture)) {
			$this->mode = 'article'; # Mode article
			$this->template = 'article.php';
			$this->cible = str_pad($capture[1],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			$this->motif = '/^'.$this->cible.'.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/'; # Motif de recherche
			$this->bypage = NULL; # Pas de pagination pour ce mode bien sur
			if($this->aConf['capcha'] == 1) # On cree notre objet capcha si besoin est
				$this->plxCapcha = new plxCapcha();
		}
		elseif($this->get AND preg_match('/^categorie([0-9]+)\//',$this->get,$capture)) {
			$this->mode = 'categorie'; # Mode categorie
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			$this->motif = '/^[0-9]{4}.[home|0-9,]*'.$this->cible.'[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/'; # Motif de recherche			
			if(!empty($this->aCats[ $this->cible ])) {
				$this->template = $this->aCats[ $this->cible ]['template'];			
				$this->tri = $this->aCats[ $this->cible ]['tri']; # Recuperation du tri des articles
				# On a une pagination particuliere pour la categorie (bypage != 0)
				if($this->aCats[ $this->cible ]['bypage'] > 0)
					$this->bypage = ceil($this->aCats[ $this->cible ]['bypage']);
			}
			else $this->template = 'erreur.php';
		}			
		elseif($this->get AND preg_match('/^static([0-9]+)\//',$this->get,$capture)) {
			$this->mode = 'static'; # Mode static
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres			
			$this->bypage = NULL; # Pas de pagination pour ce mode bien sur ;)
			$this->template = isset($this->aStats[ $this->cible ]) ? $this->aStats[ $this->cible ]['template'] : 'erreur.php';
		}
		elseif($this->get AND preg_match('/^telechargement\/(.+)$/',$this->get,$capture)) {
			$this->mode = 'telechargement'; # Mode telechargement
			$this->cible = $capture[1];	
			$this->bypage = NULL; # Pas de pagination pour ce mode bien sur ;)
		}
		elseif($this->get AND preg_match('/^tag\/([a-z0-9-]+)/',$this->get,$capture)) {
			$this->mode = 'tags'; # Affichage en mode home
			$this->template = 'tags.php';
			$this->cible = $capture[1];
			$ids = array();
			$time = @date('YmdHi');
			foreach($this->aTags as $idart => $tag) {
				if($tag['date']<=$time) {
					$tags = array_map("trim", explode(',', $tag['tags']));
					$tags = array_map(array('plxUtils', 'title2url'), $tags);
					if(in_array($this->cible, $tags)) {
						if(!isset($ids[$idart])) $ids[$idart] = $idart;
					}
				}
			}
			if(sizeof($ids)==0) {
				$this->plxErreur = new plxErreur('Aucun article pour ce mot cl&eacute; !');
				$this->mode = "erreur";
				$this->template = "erreur.php";
			} else {
				$this->motif = '/('.implode('|', $ids).').[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
				$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			}
		}
        elseif($this->get AND preg_match('/^archives\/([0-9]{4})[\/]?([0-9]{2})?/',$this->get,$capture)) {
            $this->mode = 'archives';
			$this->template = 'archives.php';
            $this->cible = $capture[1];
			if(empty($capture[2])) $this->cible .= '[0-9]{2}';
			else $this->cible .= $capture[2];
            $this->motif = '/^[0-9]{4}.[0-9,]*.[0-9]{3}.'.$this->cible.'[0-9]{6}.[a-z0-9-]+.xml$/';
        }
		elseif(!$this->get AND !defined('PLX_BLOG') AND $this->aConf['homestatic']!='' AND $this->aStats[$this->aConf['homestatic']]['active']) {
			$this->mode = 'static'; # Mode static
			$this->cible = $this->aConf['homestatic'];
			$this->template = $this->aStats[ $this->cible ]['template'];
			$this->bypage = NULL; # Pas de pagination pour ce mode bien sur ;)
		}
		else {
			$this->mode = 'home';
			$this->template = 'home.php';
			# On regarde si on a des articles en mode "home"		
			if($this->plxGlob_arts->query('/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/')) {
				$this->motif = '/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
				$this->bypage = NULL; # Tous les articles sur une page
			} else { # Sinon on recupere tous les articles
				$this->motif = '/^[0-9]{4}.[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
			}
		}

	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function demarrage() {

		if($this->mode == 'home' OR $this->mode == 'categorie' OR $this->mode == 'archives' OR $this->mode == 'tags') {
			if($this->mode == 'categorie' AND empty($this->aCats[ $this->cible ])) { # Catégorie inexistante
				$this->plxErreur = new plxErreur('Cette cat&eacute;gorie est inexistante !');
				$this->mode = 'erreur';
				return;
			}
			$this->getPage(); # Recuperation de la page
			$this->getFiles('before'); # Recuperation des fichiers
			$this->getArticles(); # Recuperation des articles
			if(!$this->plxGlob_arts->count OR !$this->plxRecord_arts->size) { # Aucun article
				$this->plxErreur = new plxErreur('Aucun article pour cette page !');
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
		elseif($this->mode == 'article') {
			$this->getFiles('before'); # Recuperation des fichiers
			$this->getArticles(); # Recuperation des articles
			if(!$this->plxGlob_arts->count OR !$this->plxRecord_arts->size) { # Aucun article
				$this->plxErreur = new plxErreur('Cet article n\'existe pas ou n\'existe plus !');
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
			# On a validé le formulaire commentaire
			if(!empty($_POST) AND $this->plxRecord_arts->f('allow_com') AND $this->aConf['allow_com']) {
				# On récupère le retour de la création
				$retour = $this->newCommentaire($this->cible,plxUtils::unSlash($_POST));
				# Url de l'article
				$url = $this->urlRewrite('?article'.intval($this->plxRecord_arts->f('numero')).'/'.$this->plxRecord_arts->f('url'));
				if($retour[0] == 'c') { # Le commentaire a été publié
					header('Location: '.$url.'/#'.$retour);
				} elseif($retour == 'mod') { # Le commentaire est en modération
					$_SESSION['msgcom'] = 'Le commentaire est en cours de mod&eacute;ration par l\'administrateur de ce site';
					header('Location: '.$url.'/#form');
				} else {
					$_SESSION['msgcom'] = $retour;
					$_SESSION['msg'] = array(
							'name' => plxUtils::unSlash($_POST['name']),
							'site' => plxUtils::unSlash($_POST['site']),
							'mail' => plxUtils::unSlash($_POST['mail']),
							'content' => plxUtils::unSlash($_POST['content'])
						);
					header('Location: '.$url.'/#form');
				}
				exit;
			}
			# Récupération des commentaires
			$this->getCommentaires('/^'.$this->cible.'.[0-9]{10}-[0-9]+.xml$/',$this->mapTri($this->tri_coms));
			$this->template=$this->plxRecord_arts->f('template');
		}
		elseif($this->mode == 'static') {
			# On va verifier que la page existe vraiment
			if(!isset($this->aStats[ $this->cible ]) OR intval($this->aStats[ $this->cible ]['active']) != 1) {
				$this->plxErreur = new plxErreur('Cette page n\'existe pas ou n\'existe plus !');
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
		elseif($this->mode == 'telechargement') {
			# On va verifier que la page existe vraiment
			if(!$this->sendTelechargement($this->cible)) {
				$this->plxErreur = new plxErreur('Le document sp&eacute;cifi&eacute; est introuvable');
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
	}
	
	/**
	 * Méthode qui parse le fichier de configuration et alimente 
	 * le tableau aConf
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function getConfiguration($filename) {

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "parametre"
		if(isset($iTags['parametre'])) {
			# On compte le nombre de tags "parametre"
			$nb = sizeof($iTags['parametre']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				if(isset($values[ $iTags['parametre'][$i] ]['value'])) # On a une valeur pour ce parametre
					$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = $values[ $iTags['parametre'][$i] ]['value'];
				else # On n'a pas de valeur
					$this->aConf[ $values[ $iTags['parametre'][$i] ]['attributes']['name'] ] = '';
			}
		}
			
		# On gère la non regression en cas d'ajout de paramètres sur une version de pluxml déjà installée
		if(!isset($this->aConf['tri_coms'])) $this->aConf['tri_coms'] = $this->aConf['tri'];
		if(!isset($this->aConf['bypage_admin_coms'])) $this->aConf['bypage_admin_coms'] = 10;
		if(!isset($this->aConf['tags'])) $this->aConf['tags'] = 'data/configuration/tags.xml';
		if(!isset($this->aConf['users'])) $this->aConf['users'] = 'data/configuration/users.xml';		
	}

	/**
	 * Méthode qui parse le fichier des catégories et alimente 
	 * le tableau aCats
	 *
	 * @param	filename	emplacement du fichier XML des catégories
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function getCategories($filename) {

		if(!is_file($filename)) return;
	
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "categorie"
		if(isset($iTags['categorie'])) {
			# On compte le nombre de tags "categorie"
			$nb = sizeof($iTags['categorie']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				# Recuperation du nom de la categorie
				$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['name']
				= $values[ $iTags['categorie'][$i] ]['value'];
				# Recuperation de l'url de la categorie
				$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['url']
				= strtolower($values[ $iTags['categorie'][$i] ]['attributes']['url']);
				# Recuperation du tri de la categorie si besoin est
				if(isset($values[ $iTags['categorie'][$i] ]['attributes']['tri']))
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['tri']
					= $values[ $iTags['categorie'][$i] ]['attributes']['tri'];
				else # Tri par defaut
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['tri']
					= $this->aConf['tri'];
				# Recuperation du nb d'articles par page de la categorie si besoin est
				if(isset($values[ $iTags['categorie'][$i] ]['attributes']['bypage']))
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['bypage']
					= $values[ $iTags['categorie'][$i] ]['attributes']['bypage'];
				else # Nb d'articles par page par defaut
					$array[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['bypage']
					= $this->bypage;
				# recuperation du fichier template 
				if(isset($values[ $iTags['categorie'][$i] ]['attributes']['template']))
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['template']
					= $values[ $iTags['categorie'][$i] ]['attributes']['template'];
				else
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['template'] = 'categorie.php';
				# On affiche la categorie dans le menu ?
				if(isset($values[ $iTags['categorie'][$i] ]['attributes']['menu']))
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['menu']
					= $values[ $iTags['categorie'][$i] ]['attributes']['menu'];
				else
					$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['menu'] = 'oui';
				# Recuperer du nombre d'article de la categorie
				$motif = '/^[0-9]{4}.[home,|0-9,]*'.$values[ $iTags['categorie'][$i] ]['attributes']['number'].'[0-9,]*.[0-9]{3}.[0-9]{12}.[A-Za-z0-9-]+.xml$/';
				$this->aCats[ $values[ $iTags['categorie'][$i] ]['attributes']['number'] ]['articles']
				= ($this->plxGlob_arts->query($motif))?$this->plxGlob_arts->count:0;

			}
		}
	}

	/**
	 * Méthode qui parse le fichier des pages statiques et alimente 
	 * le tableau aStats
	 *
	 * @param	filename	emplacement du fichier XML des pages statiques
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function getStatiques($filename) {

		if(!is_file($filename)) return;
			
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "statique"
		if(isset($iTags['statique']) AND isset($iTags['name'])) {
			# On compte le nombre de tags "statique"
			$nb = sizeof($iTags['name']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$number = $values[ $iTags['statique'][$i*2] ]['attributes']['number'];
				# Recuperation du groupe de la page statique
				$this->aStats[$number]['group'] = isset($values[ $iTags['statique'][$i] ])?$values[ $iTags['group'][$i] ]['value']:'';			
				# Recuperation du nom de la page statique
				$this->aStats[$number]['name'] = isset($values[ $iTags['statique'][$i] ])?$values[ $iTags['name'][$i] ]['value']:'';
				# Recuperation de l'url de la page statique
				$this->aStats[$number]['url'] = strtolower($values[ $iTags['statique'][$i*2] ]['attributes']['url']);
				# Recuperation de l'etat de la page
				$this->aStats[$number]['active'] = intval($values[ $iTags['statique'][$i*2] ]['attributes']['active']);
				# On affiche la page statique dans le menu ?
				if(isset($values[ $iTags['statique'][$i*2] ]['attributes']['menu']))
					$this->aStats[$number]['menu'] = $values[ $iTags['statique'][$i*2] ]['attributes']['menu'];
				else
					$this->aStats[$number]['menu'] = 'oui';
				# recuperation du fichier template 
				if(isset($values[ $iTags['statique'][$i*2] ]['attributes']['template']))
					$this->aStats[$number]['template'] = $values[ $iTags['statique'][$i*2] ]['attributes']['template'];
				else
					$this->aStats[$number]['template'] = 'static.php';
				# On verifie que la page statique existe bien
				$file = PLX_ROOT.$this->aConf['racine_statiques'].$number.'.'.$values[ $iTags['statique'][$i*2] ]['attributes']['url'].'.php';
				# On test si le fichier est lisible
				$this->aStats[$number]['readable'] = (is_readable($file) ? 1 : 0);
			}
		}
	}
	
	/**
	 * Méthode qui parse le fichier des utilisateurs
	 *
	 * @param	filename	emplacement du fichier XML des passwd
	 * @return	array		tableau des utilisateurs
	 * @author	Stephane F
	 **/
	public function getUsers($filename) {

		if(!is_file($filename)) return;

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		$array = array();
		# On verifie qu'il existe des tags "user"
		if(isset($iTags['user']) AND isset($iTags['login'])) {
			# On compte le nombre d'utilisateur
			$nb = sizeof($iTags['login']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$number = $values[$iTags['user'][$i*6] ]['attributes']['number'];
				$array[$number]['active'] = $values[ $iTags['user'][$i*6] ]['attributes']['active'];
				$array[$number]['delete'] = $values[ $iTags['user'][$i*6] ]['attributes']['delete'];
				$array[$number]['profil'] = $values[ $iTags['user'][$i*6] ]['attributes']['profil'];
				$array[$number]['login'] = isset($values[ $iTags['login'][$i] ])?$values[ $iTags['login'][$i] ]['value']:'';
				$array[$number]['name'] = isset($values[ $iTags['name'][$i] ])?$values[ $iTags['name'][$i] ]['value']:'';
				$array[$number]['password'] = isset($values[ $iTags['password'][$i] ])?$values[ $iTags['password'][$i] ]['value']:'';
				$array[$number]['infos'] = isset($values[ $iTags['infos'][$i] ])?$values[ $iTags['infos'][$i] ]['value']:'';
			}
		}
		# On retourne le tableau
		$this->aUsers = $array;
	}

	/**
	 * Méthode qui selon le paramètre tri retourne sort ou rsort (tri PHP)
	 *
	 * @param	tri	asc ou desc
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	protected function mapTri($tri) {
		
		# On transforme le tri en rsort ou sort
		return preg_match('/asc/',$tri)?'sort':'rsort';
	}
	
	/**
	 * Méthode qui récupère le numéro de la page active
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stephane F
	 **/
	protected function getPage() {
	
		# On check pour avoir le numero de page
		if(preg_match('/page([0-9]*)/',$this->get,$capture))
			$this->page = $capture[1];
		else
			$this->page = 1;	
	}

	/**
	 * Méthode qui enregistre dans le tableau aFiles tous les articles 
	 * respectants le motif de recherche et les différents paramètres 
	 *
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	null
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function getFiles($publi='before') {

		# On fait notre traitement sur notre tri
		$ordre = $this->mapTri($this->tri);
		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		$this->aFiles = $this->plxGlob_arts->query($this->motif,'art',$ordre,$start,$this->bypage,$publi);
	}

	/**
	 * Méthode qui enregistre dans un objet plxRecord tous les articles
	 * des fichiers du tableau aFiles
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function getArticles() {

		if(is_array($this->aFiles)) { # On a des fichiers
			foreach($this->aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[ $k ] = $this->parseArticle(PLX_ROOT.$this->aConf['racine_articles'].$v);
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_arts = new plxRecord($array);
		}
	}

	/**
	 * Méthode qui retourne les informations $output en analysant 
	 * le nom du fichier de l'article $filename
	 *
	 * @param	filename	fichier de l'article à traiter
	 * @return	array		information à récupérer
	 * @author	Stephane F
	 **/	
	protected function artInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		if(preg_match('/([0-9]{4}).([0-9,|home|draft]*).([0-9]{3}).([0-9]{12}).([a-z0-9-]+).xml$/',$filename,$capture)) {
			return array(
				'artId'		=> $capture[1],
				'catId'		=> $capture[2],
				'usrId'		=> $capture[3],
				'artDate'	=> $capture[4],
				'artUrl'	=> $capture[5]
			);
		}
	}

	/**
	 * Méthode qui parse l'article du fichier $filename
	 * 
	 * @param	filename	fichier de l'article à parser
	 * @return	array
	 * @author	Anthony GUÉRIN, Florent MONTHEL, Stéphane F
	 **/
	public function parseArticle($filename) {

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);		
		# Recuperation des valeurs de nos champs XML
		$art['title'] = trim($values[ $iTags['title'][0] ]['value']);
		$art['allow_com'] = trim($values[ $iTags['allow_com'][0] ]['value']);
		$art['template'] = (isset($iTags['template'])?trim($values[ $iTags['template'][0] ]['value']):'article.php');
		$art['chapo'] = (isset($values[ $iTags['chapo'][0] ]['value']))?trim($values[ $iTags['chapo'][0] ]['value']):'';
		$art['content'] = (isset($values[ $iTags['content'][0] ]['value']))?trim($values[ $iTags['content'][0] ]['value']):'';
		$art['tags'] = (isset($values[ $iTags['tags'][0] ]['value']))?trim($values[ $iTags['tags'][0] ]['value']):'';		
		# Informations obtenues en analysant le nom du fichier
		$art['filename'] = $filename;
		$tmp = $this->artInfoFromFilename($filename);
		$art['numero'] = $tmp['artId'];
		$art['author'] = $tmp['usrId'];
		$art['categorie'] = $tmp['catId'];
		$art['url'] = $tmp['artUrl'];
		$art['date'] = plxDate::dateToIso($tmp['artDate'],$this->aConf['delta']);
		# On recupere le nombre de commentaires de cet article si besoin est
		if($this->mode != 'article') { # En mode article, on a cette information autrement
			$motif = '/^'.$art['numero'].'.[0-9]{10}.[0-9]+.xml$/';
			$art['nb_com'] = $this->getNbCommentaires($motif);
		}
		# On retourne le tableau
		return $art;
	}

	/**
	 * Méthode qui retourne le nombre de commentaires respectants le motif $motif et le paramètre $publi
	 *
	 * @param	motif	motif de recherche des commentaires
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	integer
	 * @author	Florent MONTHEL
	 **/
	public function getNbCommentaires($motif,$publi='before') {

		# On a des resultats
		if($this->plxGlob_coms->query($motif,'com','sort',0,false,$publi))
			return $this->plxGlob_coms->count;
		else # On a rien
			return 0;
	}

	/**
	 * Méthode qui retourne les informations $output en analysant 
	 * le nom du fichier du commentaire $filename
	 *
	 * @param	filename	fichier du commentaire à traiter
	 * @return	array		information à récupérer
	 * @author	Stephane F
	 **/
	protected function comInfoFromFilename($filename) {

		# On effectue notre capture d'informations
		if(preg_match('/_?([0-9]{4}).([0-9]{10})-([0-9])+.xml$/',$filename,$capture)) {
			return array(
				'artId'		=> $capture[1],
				'comDate'	=> $capture[2],
				'comId'		=> $capture[2].'-'.$capture[3]
			);
		}
	}

	/**
	 * Méthode qui parse le commentaire du fichier $filename
	 *
	 * @param	filename	fichier du commentaire à parser
	 * @return	array
	 * @author	Florent MONTHEL
	 **/
	public function parseCommentaire($filename) {

		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);	
		# Recuperation des valeurs de nos champs XML
		$com['author'] = trim($values[ $iTags['author'][0] ]['value']);
		$com['type'] = (isset($values[ $iTags['type'][0] ]['value']))?trim($values[ $iTags['type'][0] ]['value']):'normal';
		$com['ip'] = (isset($values[ $iTags['ip'][0] ]['value']))?trim($values[ $iTags['ip'][0] ]['value']):'';
		$com['mail'] = (isset($values[ $iTags['mail'][0] ]['value']))?trim($values[ $iTags['mail'][0] ]['value']):'';
		$com['site'] = (isset($values[ $iTags['site'][0] ]['value']))?trim($values[ $iTags['site'][0] ]['value']):'';
		$com['content'] = trim($values[ $iTags['content'][0] ]['value']);	
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->comInfoFromFilename($filename);
		$com['numero'] = $tmp['comId'];
		$com['article'] = $tmp['artId'];
		$com['date'] = plxDate::timestampToIso($tmp['comDate'],$this->aConf['delta']);	
		# On retourne le tableau
		return $com;
	}

	/**
	 * Méthode qui enregistre dans un objet plxRecord tous les commentaires 
	 * respectant le motif $motif et la limite $limite
	 *
	 * @param	motif	motif de recherche des commentaires
	 * @param	ordre	ordre du tri : sort ou rsort
	 * @param	start	commencement
	 * @param	limite	nombre de commentaires à retourner
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	public function getCommentaires($motif,$ordre='sort',$start=0,$limite=false,$publi='before') {
		
		# On recupère les fichiers des commentaires
		$aFiles = $this->plxGlob_coms->query($motif,'com',$ordre,$start,$limite,$publi);
		if($aFiles) { # On a des fichiers
			foreach($aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[ $k ] = $this->parseCommentaire(PLX_ROOT.$this->aConf['racine_commentaires'].$v);
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_coms = new plxRecord($array);
		}
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	tableau contenant les valeurs du nouveau commentaire
	 * @return	string
	 * @author	Florent MONTHEL
	 **/
	public function newCommentaire($artId,$content) {

		# On verifie que le capcha est correct, si besoin est
		if($this->aConf['capcha'] == 0 OR $content['rep2'] == md5($this->plxCapcha->gds.$content['rep'])) {
			if(!empty($content['name']) AND !empty($content['content'])) { # Les champs obligatoires sont remplis
				$author = plxUtils::strCheck(trim($content['name']));
				$contenu = plxUtils::strCheck(trim($content['content']));
				$date = time();
				# On verifie le mail
				$mail = (plxUtils::checkMail(trim($content['mail'])))?trim($content['mail']):'';
				# On verifie le site
				$site = (plxUtils::checkSite(trim($content['site'])))?trim($content['site']):'';
				# On recupere l'adresse IP du posteur
				$ip = plxUtils::getIp();
				# On genere le nom du fichier selon l'existence ou non d'un fichier du meme nom
				$i = 0;
				do { # On boucle en testant l'existence du fichier (cas de plusieurs commentaires/sec pour un article)
					$i++;
					if($this->aConf['mod_com']) # On modere le commentaire => underscore
						$filename = PLX_ROOT.$this->aConf['racine_commentaires'].'_'.$artId.'.'.$date.'-'.$i.'.xml';
					else # On publie le commentaire directement
						$filename = PLX_ROOT.$this->aConf['racine_commentaires'].$artId.'.'.$date.'-'.$i.'.xml';
				} while(file_exists($filename));
				# On peut creer le commentaire
				if($this->addCommentaire($filename,$author,'normal',$ip,$mail,$site,$contenu)) { # Commentaire OK
					if($this->aConf['mod_com']) # En cours de moderation
						return 'mod';
					else # Commentaire publie directement, on retourne son identifiant
						return 'c'.$date.'-'.$i;
				} else { # Erreur lors de la création du commentaire
					return 'Une erreur s\'est produite lors de la publication de ce commentaire';
				}
			} else { # Erreur de remplissage des champs obligatoires
				return 'Merci de remplir tous les champs obligatoires requis';
			}
		} else { # Erreur de verification capcha
			return 'La v&eacute;rification anti-spam a &eacute;chou&eacute;';
		}
	}
	
	/**
	 * Méthode qui crée physiquement le fichier XML du commentaire
	 *
	 * @param	filename	fichier du commentaire à créer
	 * @param	author	auteur du commmentaire
	 * @param	type	type du commmentaire (admin ou normal)
	 * @param	ip	adresse IP posteuse du commmentaire
	 * @param	mail	mail de l'auteur du commmentaire
	 * @param	site	site de l'auteur du commmentaire
	 * @param	contenu	contenu du commmentaire
	 * @return	booléen
	 * @author	Anthony GUÉRIN et Florent MONTHEL
	 **/
	public function addCommentaire($filename,$author,$type,$ip,$mail,$site,$contenu) {

		# On genere le contenu de notre fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<comment>\n";
		$xml .= "\t<author><![CDATA[$author]]></author>\n";
		$xml .= "\t<type>$type</type>\n";
		$xml .= "\t<ip>$ip</ip>\n";
		$xml .= "\t<mail><![CDATA[$mail]]></mail>\n";
		$xml .= "\t<site><![CDATA[$site]]></site>\n";
		$xml .= "\t<content><![CDATA[$contenu]]></content>\n";
		$xml .= "</comment>\n";
		# On ecrit ce contenu dans notre fichier XML
		return plxUtils::write($xml,$filename);
	}
	
	/**
	 * Méthode qui parse le fichier des tags et aliment 
	 * le tableau aTags
	 *
	 * @param	filename	emplacement du fichier XML contenant les tags		
	 * @return	null
	 * @author	Stephane F.
	 **/
	public function getTags($filename) {

		if(!is_file($filename)) return;
	
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		$array = array();
		# On verifie qu'il existe des tags "file"
		if(isset($iTags['article'])) {
			# On compte le nombre de tags "file"
			$nb = sizeof($iTags['article']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				if(isset($values[ $iTags['article'][$i] ]['value']))
					$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['tags'] = trim($values[ $iTags['article'][$i] ]['value']);
				else
					$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['tags'] = '';
				$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['date'] = $values[ $iTags['article'][$i] ]['attributes']['date'];
				$array[ $values[ $iTags['article'][$i] ]['attributes']['number'] ]['active'] = $values[ $iTags['article'][$i] ]['attributes']['active'];
			}
		}
		# Mémorisation de la liste des tags
		$this->aTags = $array;
	}
	
	/**
	 * Méthode qui lance le téléchargement d'un document
	 *
	 * @param	cible	cible de téléchargement cryptée
	 * @return	booleen
	 * @author	Stephane F. et Florent MONTHEL
	 **/
	public function sendTelechargement($cible) {
	
		# On décrypte le nom du fichier
		$file = PLX_ROOT.$this->aConf['documents'].plxEncrypt::decryptId($cible);

		# On lance le téléchargement et on check le répertoire documents
		if(@file_exists($file) AND preg_match('#^'.str_replace('\\', '/', realpath(PLX_ROOT.$this->aConf['documents']).'#'), str_replace('\\', '/', realpath($file)))) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: no-cache');
			header('Content-Length: '.filesize($file));
			readfile($file);
			exit;
		} else { # On retourne false
			return false;
		}

	}

	/**
	 * Méthode qui réécrit les urls pour supprimer le ?
	 *
	 * @param	url		url à réécrire
	 * @return	string	url réécrite
	 * @author	Stéphane F
	 **/
	public function urlRewrite($url='') {

		if($url=='') return $this->racine;

		preg_match('/^([0-9a-z\_\-\.\/]+)?[\?]?([0-9a-z\_\-\.\/]+)?[\#]?(.*)$/i', $url, $args);

		if($this->aConf['urlrewriting']) {
			$new_url  = str_replace('index.php', '', $args[1]);
			$new_url  = str_replace('feed.php', 'feed/', $new_url);
			$new_url .= !empty($args[2])?$args[2]:'';
			if(empty($new_url))	$new_url = $this->path_url;
			$new_url .= !empty($args[3])?'#'.$args[3]:'';
			return $this->racine.$new_url;
		} else {
			if(empty($args[1]) AND !empty($args[2])) $args[1] = 'index.php';
			$new_url  = !empty($args[1])?$args[1]:$this->path_url;
			$new_url .= !empty($args[2])?'?'.$args[2]:'';
			$new_url .= !empty($args[3])?'#'.$args[3]:'';
			return $this->racine.$new_url;
		}
	}

}
?>