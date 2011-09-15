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

	public $aConf = array(); # Tableau de configuration
	public $aCats = array(); # Tableau de toutes les catégories
	public $aStats = array(); # Tableau de toutes les pages statiques
	public $aTags = array(); # Tableau des tags
	public $aUsers = array(); #Tableau des utilisateurs

	public $plxGlob_arts = null; # Objet plxGlob des articles
	public $plxGlob_coms = null; # Objet plxGlob des commentaires
	public $plxRecord_arts = null; # Objet plxRecord des articles
	public $plxRecord_arts_size = 0; # Nombre d'articles total
	public $plxRecord_coms = null; # Objet plxRecord des commentaires
	public $plxCapcha = null; # Objet plxCapcha
	public $plxErreur = null; # Objet plxErreur
	public $plxPlugins = null; # Objet plxPlugins

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
			printf(L_FILE_VERSION_REQUIRED, PLX_ROOT);
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
		$this->style = $this->aConf['style'];
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage'];
		$this->tri = $this->aConf['tri'];
		$this->tri_coms = $this->aConf['tri_coms'];
		# On récupère le chemin de l'url
		$var = parse_url($this->racine);
		$this->path_url = str_replace(ltrim($var['path'], '\/'), '', ltrim($_SERVER['REQUEST_URI'], '\/'));

		# Traitement des plugins
		$this->plxPlugins = new plxPlugins(PLX_ROOT.$this->aConf['plugins'], $this->aConf['default_lang']);
		$this->plxPlugins->loadPlugins();
		# Traitement sur les répertoires des articles et des commentaires
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
		# Récupération des données dans les autres fichiers xml
		$this->getCategories(PLX_ROOT.$this->aConf['categories']);
		$this->getStatiques(PLX_ROOT.$this->aConf['statiques']);
		$this->getTags(PLX_ROOT.$this->aConf['tags']);
		$this->getUsers(PLX_ROOT.$this->aConf['users']);
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorConstruct'));
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

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorPreChauffageBegin'))) return;

		if($mode != '' AND $motif != '') {
			$this->mode = $mode; # Mode
			$this->motif = $motif; # Motif de recherche
			$this->bypage = $bypage; # Nombre d'article par page
			$this->template = $mode.'.php';
		}
		elseif($this->get AND preg_match('/^preview\/?/',$this->get) AND isset($_SESSION['preview'])) {
			$this->mode = 'preview';
			$this->template = 'article.php';
			if($this->aConf['capcha'] == 1) # On cree notre objet capcha si besoin est
				$this->plxCapcha = new plxCapcha();
		}
		elseif($this->get AND preg_match('/^404\/?/',$this->get)) {
			$this->plxErreur = new plxErreur(L_DOCUMENT_NOT_FOUND);
			$this->mode = 'erreur';
			$this->template = 'erreur.php';
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
					$this->bypage = $this->aCats[ $this->cible ]['bypage'];
			}
			else $this->template = 'erreur.php';
		}
		elseif($this->get AND preg_match('/^static([0-9]+)\//',$this->get,$capture)) {
			$this->mode = 'static'; # Mode static
			$this->cible = str_pad($capture[1],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			$this->bypage = NULL; # Pas de pagination pour ce mode bien sur ;)
			$this->template = isset($this->aStats[ $this->cible ]) ? $this->aStats[ $this->cible ]['template'] : 'erreur.php';
		}
		elseif($this->get AND preg_match('/^(telechargement|download)\/(.+)$/',$this->get,$capture)) {
			$this->mode = 'telechargement'; # Mode telechargement
			$this->cible = $capture[2];
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
				$this->plxErreur = new plxErreur(L_ARTICLE_NO_TAG);
				$this->mode = "erreur";
				$this->template = "erreur.php";
			} else {
				$this->motif = '/('.implode('|', $ids).').[home|0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
				$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			}
		}
        elseif($this->get AND preg_match('/^archives\/([0-9]{4})[\/]?([0-9]{2})?[\/]?([0-9]{2})?/',$this->get,$capture)) {
            $this->mode = 'archives';
			$this->template = 'archives.php';
			$this->bypage = $this->aConf['bypage_archives'];
            $search = $this->cible = $capture[1];
			if(!empty($capture[2])) $search = $this->cible .= $capture[2];
			else $search = $this->cible . '[0-9]{2}';
			if(!empty($capture[3])) $search = $this->cible .= $capture[3];
			else $search = $this->cible . '[0-9]{2}';
			$this->motif = '/^[0-9]{4}.[home|0-9,]*.[0-9]{3}.'.$search.'[0-9]{4}.[a-z0-9-]+.xml$/';
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
			$this->bypage = $this->aConf['bypage']; # Nombre d'article par page
			# On regarde si on a des articles en mode "home"
			if($this->plxGlob_arts->query('/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/')) {
				$this->motif = '/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
			} else { # Sinon on recupere tous les articles
				$this->motif = '/^[0-9]{4}.[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
			}
		}
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorPreChauffageEnd'));
	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function demarrage() {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorDemarrageBegin'))) return;

		if($this->mode == 'home' OR $this->mode == 'categorie' OR $this->mode == 'archives' OR $this->mode == 'tags') {
			if($this->mode == 'categorie' AND empty($this->aCats[ $this->cible ])) { # Catégorie inexistante
				$this->plxErreur = new plxErreur(L_UNKNOWN_CATEGORY);
				$this->mode = 'erreur';
				return;
			}
			$this->getPage(); # Recuperation de la page
			if(!$this->getArticles()) { # Aucun article
				$this->plxErreur = new plxErreur(L_NO_ARTICLE_PAGE);
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
		elseif($this->mode == 'preview') {
			$this->mode='article';
			$this->plxRecord_arts = new plxRecord($_SESSION['preview']);
			$this->template=$this->plxRecord_arts->f('template');
			return;
		}
		elseif($this->mode == 'article') {
			if(!$this->getArticles()) { # Aucun article
				$this->plxErreur = new plxErreur(L_UNKNOWN_ARTICLE);
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
				eval($this->plxPlugins->callHook('plxMotorDemarrageNewCommentaire'));
				if($retour[0] == 'c') { # Le commentaire a été publié
					header('Location: '.$url.'/#'.$retour);
				} elseif($retour == 'mod') { # Le commentaire est en modération
					$_SESSION['msgcom'] = L_COM_IN_MODERATION;
					header('Location: '.$url.'/#form');
				} else {
					$_SESSION['msgcom'] = $retour;
					$_SESSION['msg']['name'] = plxUtils::unSlash($_POST['name']);
					$_SESSION['msg']['site'] = plxUtils::unSlash($_POST['site']);
					$_SESSION['msg']['mail'] = plxUtils::unSlash($_POST['mail']);
					$_SESSION['msg']['content'] = plxUtils::unSlash($_POST['content']);
					eval($this->plxPlugins->callHook('plxMotorDemarrageCommentSessionMessage'));
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
				$this->plxErreur = new plxErreur(L_UNKNOWN_STATIC);
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
		elseif($this->mode == 'telechargement') {
			# On va verifier que la page existe vraiment
			if(!$this->sendTelechargement($this->cible)) {
				$this->plxErreur = new plxErreur(L_DOCUMENT_NOT_FOUND);
				$this->mode = 'erreur';
				$this->template = 'erreur.php';
				return;
			}
		}
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorDemarrageEnd'));

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
		if(!isset($this->aConf['bypage_archives'])) $this->aConf['bypage_archives'] = 5;
		if(!isset($this->aConf['userfolders'])) $this->aConf['userfolders'] = 0;
		if(!isset($this->aConf['tags'])) $this->aConf['tags'] = 'data/configuration/tags.xml';
		if(!isset($this->aConf['users'])) $this->aConf['users'] = 'data/configuration/users.xml';
		if(!isset($this->aConf['plugins'])) $this->aConf['plugins'] = 'data/configuration/plugins.xml';
		if(!isset($this->aConf['meta_description'])) $this->aConf['meta_description'] = '';
		if(!isset($this->aConf['meta_keywords'])) $this->aConf['meta_keywords'] = '';
		if(!isset($this->aConf['default_lang'])) $this->aConf['default_lang'] = DEFAULT_LANG;
	}

	/**
	 * Méthode qui parse le fichier des catégories et alimente
	 * le tableau aCats
	 *
	 * @param	filename	emplacement du fichier XML des catégories
	 * @return	null
	 * @author	Stéphane F
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
		if(isset($iTags['categorie']) AND isset($iTags['name'])) {
			$nb = sizeof($iTags['name']);
			$size=ceil(sizeof($iTags['categorie'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['categorie'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation du nom de la catégorie
				$this->aCats[$number]['name'] = isset($iTags['name'][$i])?$values[$iTags['name'][$i]]['value']:'';
				# Recuperation du nom de la description
				$this->aCats[$number]['description'] = isset($iTags['description'][$i])?$values[$iTags['description'][$i]]['value']:'';
				# Recuperation du meta description
				$this->aCats[$number]['meta_description'] = isset($iTags['meta_description'][$i])?$values[$iTags['meta_description'][$i]]['value']:'';
				# Recuperation du meta keywords
				$this->aCats[$number]['meta_keywords'] = isset($iTags['meta_keywords'][$i])?$values[$iTags['meta_keywords'][$i]]['value']:'';
				# Recuperation de l'url de la categorie
				$this->aCats[$number ]['url']=strtolower($attributes['url']);
				# Recuperation du tri de la categorie si besoin est
				$this->aCats[$number ]['tri']=isset($attributes['tri'])?$attributes['tri']:$this->aConf['tri'];
				# Recuperation du nb d'articles par page de la categorie si besoin est
				$this->aCats[$number ]['bypage']=isset($attributes['bypage'])?$attributes['bypage']:$this->bypage;
				# Recuperation du fichier template
				$this->aCats[$number ]['template']=isset($attributes['template'])?$attributes['template']:'categorie.php';
				# Récuperation état affichage de la catégorie dans le menu
				$this->aCats[$number ]['menu']=isset($attributes['menu'])?$attributes['menu']:'oui';
				# Recuperation du nombre d'article de la categorie
				$motif = '/^[0-9]{4}.[home,|0-9,]*'.$number.'[0-9,]*.[0-9]{3}.[0-9]{12}.[A-Za-z0-9-]+.xml$/';
				$arts = $this->plxGlob_arts->query($motif);
				$this->aCats[$number]['articles'] = ($arts?sizeof($arts):0);
				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetCategories'));
			}
		}
	}

	/**
	 * Méthode qui parse le fichier des pages statiques et alimente
	 * le tableau aStats
	 *
	 * @param	filename	emplacement du fichier XML des pages statiques
	 * @return	null
	 * @author	Stéphane F
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
		if(isset($iTags['statique']) AND isset($iTags['name'])) {
			$nb = sizeof($iTags['name']);
			$size=ceil(sizeof($iTags['statique'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['statique'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation du nom de la page statique
				$this->aStats[$number]['name'] = isset($iTags['name'][$i])?$values[$iTags['name'][$i]]['value']:'';
				# Recuperation du meta description
				$this->aStats[$number]['meta_description'] = isset($iTags['meta_description'][$i])?$values[$iTags['meta_description'][$i]]['value']:'';
				# Recuperation du meta keywords
				$this->aStats[$number]['meta_keywords'] = isset($iTags['meta_keywords'][$i])?$values[$iTags['meta_keywords'][$i]]['value']:'';
				# Recuperation du groupe de la page statique
				$this->aStats[$number]['group'] = isset($iTags['group'][$i])?$values[$iTags['group'][$i]]['value']:'';
				# Recuperation de l'url de la page statique
				$this->aStats[$number]['url'] = strtolower($attributes['url']);
				# Recuperation de l'etat de la page
				$this->aStats[$number]['active'] = intval($attributes['active']);
				# On affiche la page statique dans le menu ?
				$this->aStats[$number]['menu'] = isset($attributes['menu'])?$attributes['menu']:'oui';
				# recuperation du fichier template
				$this->aStats[$number]['template'] = isset($attributes['template'])?$attributes['template']:'static.php';
				# On verifie que la page statique existe bien
				$file = PLX_ROOT.$this->aConf['racine_statiques'].$number.'.'.$attributes['url'].'.php';
				# On test si le fichier est lisible
				$this->aStats[$number]['readable'] = (is_readable($file) ? 1 : 0);
				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetStatiques'));
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
		if(isset($iTags['user']) AND isset($iTags['login'])) {
			$nb = sizeof($iTags['login']);
			$size=ceil(sizeof($iTags['user'])/$nb);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$attributes = $values[$iTags['user'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				$this->aUsers[$number]['active'] = $attributes['active'];
				$this->aUsers[$number]['delete'] = $attributes['delete'];
				$this->aUsers[$number]['profil'] = $attributes['profil'];
				$this->aUsers[$number]['login'] = isset($iTags['login'][$i])?$values[ $iTags['login'][$i]]['value']:'';
				$this->aUsers[$number]['name'] = isset($iTags['name'][$i])?$values[ $iTags['name'][$i]]['value']:'';
				$this->aUsers[$number]['password'] = isset($iTags['password'][$i])?$values[$iTags['password'][$i] ]['value']:'';
				$this->aUsers[$number]['salt'] = isset($iTags['salt'][$i])?$values[$iTags['salt'][$i] ]['value']:'';
				$this->aUsers[$number]['infos'] = isset($iTags['infos'][$i])?$values[$iTags['infos'][$i]]['value']:'';
				$this->aUsers[$number]['email'] = isset($iTags['email'][$i])?$values[$iTags['email'][$i]]['value']:'';
				$lang = isset($iTags['lang'][$i]) ? $values[$iTags['lang'][$i]]['value'] : '';
				$this->aUsers[$number]['lang'] = $lang!='' ? $lang : $this->aConf['default_lang'];
				# Hook plugins
				eval($this->plxPlugins->callHook('plxMotorGetUsers'));
			}
		}
	}

	/**
	 * Méthode qui selon le paramètre tri retourne sort ou rsort (tri PHP)
	 *
	 * @param	tri	asc ou desc
	 * @return	string
	 * @author	Stéphane F.
	 **/
	protected function mapTri($tri) {

		if($tri=='desc')
			return 'rsort';	
		elseif($tri=='asc')
			return 'sort';
		elseif($tri=='alpha')
			return 'alpha';
		else
			return 'rsort';

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
	 * Méthode qui récupere la liste des  articles
	 *
	 * @param	publi	before, after ou all => on récupère tous les fichiers (date) ?
	 * @return	boolean	vrai si articles trouvés, sinon faux
	 * @author	Stéphane F
	 **/
	public function getArticles($publi='before') {

		# On fait notre traitement sur notre tri
		$ordre = $this->mapTri($this->tri);
		# On calcule la valeur start
		$start = $this->bypage*($this->page-1);
		# On recupere nos fichiers (tries) selon le motif, la pagination, la date de publication
		if($aFiles = $this->plxGlob_arts->query($this->motif,'art',$ordre,$start,$this->bypage,$publi)) {
			# on mémorise le nombre total d'articles trouvés
			foreach($aFiles as $k=>$v) # On parcourt tous les fichiers
				$array[$k] = $this->parseArticle(PLX_ROOT.$this->aConf['racine_articles'].$v);
			# On stocke les enregistrements dans un objet plxRecord
			$this->plxRecord_arts = new plxRecord($array);
			return true;
		}
		else return false;
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
		$art['meta_description'] = (isset($iTags['meta_description']))?trim($values[ $iTags['meta_description'][0] ]['value']):'';
		$art['meta_keywords'] = (isset($iTags['meta_keywords']))?trim($values[ $iTags['meta_keywords'][0] ]['value']):'';
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
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorParseArticle'));
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

		if($coms = $this->plxGlob_coms->query($motif,'com','sort',0,false,$publi))
			return sizeof($coms);
		else
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
		if(preg_match('/(_?)([0-9]{4}).([0-9]{10})-([0-9])+.xml$/',$filename,$capture)) {
			return array(
				'comStatus'	=> $capture[1],
				'artId'		=> $capture[2],
				'comDate'	=> $capture[3],
				'comId'		=> $capture[3].'-'.$capture[4]
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
		if(isset($iTags['type']))
			$com['type'] = (isset($values[ $iTags['type'][0] ]['value']))?trim($values[ $iTags['type'][0] ]['value']):'normal';
		else
			$com['type'] = 'normal';
		$com['ip'] = (isset($values[ $iTags['ip'][0] ]['value']))?trim($values[ $iTags['ip'][0] ]['value']):'';
		$com['mail'] = (isset($values[ $iTags['mail'][0] ]['value']))?trim($values[ $iTags['mail'][0] ]['value']):'';
		$com['site'] = (isset($values[ $iTags['site'][0] ]['value']))?trim($values[ $iTags['site'][0] ]['value']):'';
		$com['content'] = trim($values[ $iTags['content'][0] ]['value']);
		# Informations obtenues en analysant le nom du fichier
		$tmp = $this->comInfoFromFilename($filename);
		$com['status'] = $tmp['comStatus'];
		$com['numero'] = $tmp['comId'];
		$com['article'] = $tmp['artId'];
		$com['date'] = plxDate::timestampToIso($tmp['comDate'],$this->aConf['delta']);
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorParseCommentaire'));
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
			return true;
		}
		else return false;
	}

	/**
	 * Méthode qui crée un nouveau commentaire pour l'article $artId
	 *
	 * @param	artId	identifiant de l'article en question
	 * @param	content	tableau contenant les valeurs du nouveau commentaire
	 * @return	string
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function newCommentaire($artId,$content) {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorNewCommentaire'))) return;
		# On verifie que le capcha est correct
		if($this->aConf['capcha'] == 0 OR $content['rep2'] == sha1($content['rep'])) {
			if(!empty($content['name']) AND !empty($content['content'])) { # Les champs obligatoires sont remplis
				$comment=array();
				$comment['type'] = 'normal';
				$comment['author'] = plxUtils::strCheck(trim($content['name']));
				$comment['content'] = plxUtils::strCheck(trim($content['content']));
				# On verifie le mail
				$comment['mail'] = (plxUtils::checkMail(trim($content['mail'])))?trim($content['mail']):'';
				# On verifie le site
				$comment['site'] = (plxUtils::checkSite(trim($content['site'])))?trim($content['site']):'';
				# On recupere l'adresse IP du posteur
				$comment['ip'] = plxUtils::getIp();
				# On genere le nom du fichier selon l'existence ou non d'un fichier du meme nom
				$date = time();
				$i = 0;
				do { # On boucle en testant l'existence du fichier (cas de plusieurs commentaires/sec pour un article)
					$i++;
					if($this->aConf['mod_com']) # On modere le commentaire => underscore
						$comment['filename'] = PLX_ROOT.$this->aConf['racine_commentaires'].'_'.$artId.'.'.$date.'-'.$i.'.xml';
					else # On publie le commentaire directement
						$comment['filename'] = PLX_ROOT.$this->aConf['racine_commentaires'].$artId.'.'.$date.'-'.$i.'.xml';
				} while(file_exists($comment['filename']));
				# On peut creer le commentaire
				if($this->addCommentaire($comment)) { # Commentaire OK
					if($this->aConf['mod_com']) # En cours de moderation
						return 'mod';
					else # Commentaire publie directement, on retourne son identifiant
						return 'c'.$date.'-'.$i;
				} else { # Erreur lors de la création du commentaire
					return L_NEWCOMMENT_ERR;
				}
			} else { # Erreur de remplissage des champs obligatoires
				return L_NEWCOMMENT_FIELDS_REQUIRED;
			}
		} else { # Erreur de verification capcha
			return L_NEWCOMMENT_ERR_ANTISPAM;
		}
	}

	/**
	 * Méthode qui crée physiquement le fichier XML du commentaire
	 *
	 * @param	comment	array avec les données du commentaire à ajouter
	 * @return	booléen
	 * @author	Anthony GUÉRIN, Florent MONTHEL et Stéphane F
	 **/
	public function addCommentaire($content) {

		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorAddCommentaire'))) return;
		# On genere le contenu de notre fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<comment>\n";
		$xml .= "\t<author><![CDATA[".plxUtils::cdataCheck($content['author'])."]]></author>\n";
		$xml .= "\t<type>".$content['type']."</type>\n";
		$xml .= "\t<ip>".$content['ip']."</ip>\n";
		$xml .= "\t<mail><![CDATA[".plxUtils::cdataCheck($content['mail'])."]]></mail>\n";
		$xml .= "\t<site><![CDATA[".plxUtils::cdataCheck($content['site'])."]]></site>\n";
		$xml .= "\t<content><![CDATA[".plxUtils::cdataCheck($content['content'])."]]></content>\n";
		# Hook plugins
		eval($this->plxPlugins->callHook('plxMotorAddCommentaireXml'));
		$xml .= "</comment>\n";
		# On ecrit ce contenu dans notre fichier XML
		return plxUtils::write($xml,$content['filename']);
	}

	/**
	 * Méthode qui parse le fichier des tags et alimente
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
		# Hook plugins
		if(eval($this->plxPlugins->callHook('plxMotorSendDownload'))) return;
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

	/**
	 * Méthode qui comptabilise le nombre d'articles du site.
	 *
	 * @param	select	critere de recherche: draft, published, all, n° categories séparés par un |
	 * @param	userid	filtre sur les articles d'un utilisateur donné
	 * @return	integer	nombre d'articles
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbArticles($select='all', $userId='[0-9]{3}') {

		$nb = 0;
		if($select == 'all')
			$motif = '[home|draft|0-9,]*';
		elseif($select=='published')
			$motif = '[home|0-9,]*';
		elseif($select=='draft')
			$motif = '[\w,]*[draft][\w,]*';
		else
			$motif = $select;

		if($arts = $this->plxGlob_arts->query('/^[0-9]{4}.('.$motif.').'.$userId.'.[0-9]{12}.[a-z0-9-]+.xml$/'))
			$nb = sizeof($arts);

		return $nb;
	}

	/**
	 * Méthode qui comptabilise le nombre de commentaires du site
	 *
	 * @param	select	critere de recherche: all, online, offline
	 * @return	integer	nombre d'articles
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function nbComments($select='online') {

		$nb = 0;
		if($select == 'all')
			$motif = '/^_?[0-9]{4}.(.*).xml$/';
		elseif($select=='offline')
			$motif = '/^_[0-9]{4}.(.*).xml$/';
		elseif($select=='online')
			$motif = '/^[0-9]{4}.(.*).xml$/';
		else
			$motif = '/^_?'.$select.'.(.*).xml$/';

		if($coms = $this->plxGlob_coms->query($motif))
			$nb = sizeof($coms);

		return $nb;
	}
}
?>