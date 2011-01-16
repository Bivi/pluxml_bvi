<?php

include_once dirname(__FILE__)."/../vendor/markdown.php";

/**
 * Classe plxShow responsable de l'affichage sur stdout
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/
class plxShow {

	public $plxMotor = false; # Objet plxMotor

	/**
	 * Constructeur qui initialise l'objet plxMotor par r�f�rence
	 *
	 * @param	plxMotor	objet plxMotor pass� par r�f�rence
	 * @return	null
	 * @author	Florent MONTHEL
	 **/
	public function __construct(&$plxMotor) {

		$this->plxMotor = &$plxMotor;
	}
	
	/**
	 * M�thode qui affiche les urls r��crites
	 *
	 * @param	url			url � r��crire
	 * @return	stdout
	 * @author	St�phane F
	 **/
	public function urlRewrite($url='') {
	
		echo $this->plxMotor->urlRewrite($url);
	}	

	/**
	 * M�thode qui affiche le type de compression http
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function httpEncoding() {

		if($this->plxMotor->aConf['gzip'] AND $encoding = plxUtils::httpEncoding())
			echo 'Compression '.strtoupper($encoding).' activ&eacute;e';

	}

	/**
	 * M�thode qui affiche l'URL du site
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function racine() {
	
		echo $this->plxMotor->racine;
	}

	/**
	 * M�thode qui retourne le mode d'affichage
	 *
	 * @return	string	mode d'affichage (home, article, categorie, static ou erreur)
	 * @scope	global
	 * @author	Stephane F.
	 **/
	public function mode() {
	
		return $this->plxMotor->mode;
	}	
	
	/**
	 * M�thode qui affiche le charset selon la casse $casse
	 *
	 * @param	casse	casse min ou maj
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function charset($casse='min') {

		if($casse != 'min') # En majuscule
			echo strtoupper(PLX_CHARSET);
		else # En minuscule
			echo strtolower(PLX_CHARSET);
	}

	/**
	 * M�thode qui affiche la version de PluXml
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN et Florent MONTHEL
	 **/
	public function version() {

		echo $this->plxMotor->version;
	}

	/**
	 * M�thode qui affiche la variable get de l'objet plxMotor (variable $_GET globale)
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function get() {

		echo $this->plxMotor->get;
	}

	/**
	 * M�thode qui affiche le temps d'ex�cution de la page
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN et Florent MONTHEL
	 **/
	public function chrono() {

		echo round(plxDate::microtime()-$this->plxMotor->start,3).'s';
	}

	/**
	 * M�thode qui affiche le dossier de stockage du style actif
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function template() {

		echo $this->plxMotor->urlRewrite('themes/'.$this->plxMotor->style);

	}

	/**
	 * M�thode qui affiche le titre de la page selon le mode
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN, Florent MONTHEL, St�phane F
	 **/
	public function pageTitle() {

		if($this->plxMotor->mode == 'home') {
			if(!empty($this->plxMotor->aConf['description']))
				echo plxUtils::strCheck($this->plxMotor->aConf['title'].' - '.$this->plxMotor->aConf['description']);
			else
				echo plxUtils::strCheck($this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'categorie') {
			echo plxUtils::strCheck($this->plxMotor->aConf['title'].' - '.$this->plxMotor->aCats[ $this->plxMotor->cible ]['name']);
			return;
		}
		if($this->plxMotor->mode == 'article') {
			echo plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title').' - '.$this->plxMotor->aConf['title']);
			return;
		}
		if($this->plxMotor->mode == 'static') {
			echo plxUtils::strCheck($this->plxMotor->aConf['title'].' - '.$this->plxMotor->aStats[ $this->plxMotor->cible ]['name']);
			return;
		}
        if($this->plxMotor->mode == 'archives') {
            echo $this->plxMotor->aConf['title'].' - Archives '.plxDate::getCalendar('month', substr($this->plxMotor->cible, 4, 6)).' '.substr($this->plxMotor->cible, 0, 4);
            return;
        }
        if($this->plxMotor->mode == 'tags') {
            echo $this->plxMotor->aConf['title'].' - Tag '.$this->plxMotor->cible;
            return;
        }		
		if($this->plxMotor->mode == 'erreur') {
			echo plxUtils::strCheck($this->plxMotor->aConf['title']).' - '.$this->plxMotor->plxErreur->getMessage();
			return;
		}
	}

	/**
	 * M�thode qui affiche le titre du blog link� (variable $type='link') ou non
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function mainTitle($type='') {

		$title = plxUtils::strCheck($this->plxMotor->aConf['title']);
		if($type == 'link') # Type lien
			echo '<a href="'.$this->plxMotor->urlRewrite().'" title="'.$title.'">'.$title.'</a>';
		else # Type normal
			echo $title;
	}

	/**
	 * M�thode qui affiche le sous-titre du blog
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN et Florent MONTHEL
	 **/
	public function subTitle() {

		echo plxUtils::strCheck($this->plxMotor->aConf['description']);
	}

	/**
	 * M�thode qui affiche le nombre de cat�gories actives du site.
	 *
	 * @param	format	format d'affichage du texte du nombre de cat�gories (#nb)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function nbAllCat($format='#nb cat&eacute;gorie(s)') {

		# Initialisation
		$nb = 0;
		# On verifie qu'il y a des categories
		if($this->plxMotor->aCats) {
			foreach($this->plxMotor->aCats as $k=>$v) {
				if($v['articles'] > 0) # Si on a des articles
					$nb++;
			} # Fin du while
		}
		# On modifie nos motifs
		$txt = str_replace('#nb',$nb,$format);
		# On proc�de � l'affichage
		echo $txt;
	}

	/**
	 * M�thode qui affiche le nombre d'articles du site.
	 *
	 * @param	format	format d'affichage du texte du nombre d'articles (#nb)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function nbAllArt($format="#nb article(s)") {

		# Initialisation
		$nb = 0;
		# Nouvel objet pour compter le nombre d'articles
		$plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_articles']);
		$plxGlob_arts->query('/^[0-9]{4}.[0-9,]*.(.+).xml$/');
		$nb = $plxGlob_arts->count;
		# On modifie nos motifs
		$txt = str_replace('#nb',$nb,$format);
		# On proc�de � l'affichage
		echo $txt;
	}

	/**
	 * M�thode qui affiche le nombre de commentaires du site.
	 *
	 * @param	format	format d'affichage du texte du nombre de commentaires (#nb)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function nbAllCom($format='#nb commentaire(s)') {

		# Initialisation
		$nb = 0;
		# Nouvel objet pour compter le nombre de commentaires
		$plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_commentaires']);
		$plxGlob_coms->query('/^[0-9]{4}.[0-9]{10}-[0-9]+.xml$/');
		$nb = $plxGlob_coms->count;
		# On modifie nos motifs
		$txt = str_replace('#nb',$nb,$format);
		# On proc�de � l'affichage
		echo $txt;
		}

	/**
	 * M�thode qui affiche la liste des cat�gories actives.
	 * Si la variable $extra est renseign�e, un lien vers la 
	 * page d'accueil (nomm� $extra) sera mis en place en premi�re 
	 * position.
	 *
	 * @param	extra	nom du lien vers la page d'accueil
	 * @param	format	format du texte pour chaque cat�gorie (variable : #cat_id, #cat_status, #cat_url, #cat_name, #art_nb)
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function catList($extra='', $format='<li id="#cat_id" class="#cat_status"><a href="#cat_url" title="#cat_name">#cat_name</a></li>') {
		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if($extra != '') {
			$name = str_replace('#cat_id','cat-home',$format);
			$name = str_replace('#cat_url',$this->plxMotor->urlRewrite(),$name);
			$name = str_replace('#cat_name',plxUtils::strCheck($extra),$name);
			$name = str_replace('#cat_status',($this->catId()=='home'?'active':'noactive'), $name);
			$name = str_replace('#art_nb','',$name);
			echo $name;
		}
		# On verifie qu'il y a des categories
		if($this->plxMotor->aCats) {
			foreach($this->plxMotor->aCats as $k=>$v) {
				if($v['articles'] > 0 AND $v['menu'] == 'oui') { # On a des articles
					# On modifie nos motifs
					$name = str_replace('#cat_id','cat-'.intval($k),$format);
					$name = str_replace('#cat_url',$this->plxMotor->urlRewrite('?categorie'.intval($k).'/'.$v['url']),$name);
					$name = str_replace('#cat_name',plxUtils::strCheck($v['name']),$name);
					$name = str_replace('#cat_status',($this->catId()==intval($k)?'active':'noactive'), $name);
					$name = str_replace('#art_nb',$v['articles'],$name);
					echo $name;
				}
			} # Fin du while
		}
	}

	/**
	 * M�thode qui retourne l'id de la cat�gorie en question (sans les 0 suppl�mentaires)
	 *
	 * @return	int ou string
	 * @scope	home,categorie,article
	 * @author	Florent MONTHEL
	 **/
	public function catId() {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'categorie' AND isset($this->plxMotor->aCats[ $this->plxMotor->cible ]))
			return intval($this->plxMotor->cible);
		# On va verifier que la categorie existe en mode article
		if($this->plxMotor->mode == 'article' AND isset($this->plxMotor->aCats[ $this->plxMotor->plxRecord_arts->f('categorie') ]))
			return intval($this->plxMotor->plxRecord_arts->f('categorie'));
		# On va v�rifier si c'est la cat�gorie home
		if($this->plxMotor->mode == 'categorie' OR $this->plxMotor->mode == 'home' OR $this->plxMotor->mode == 'article')
			return 'home';
	}

	/**
	 * M�thode qui affiche le nom de la cat�gorie active (link� ou non)
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function catName($type='') {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'categorie' AND isset($this->plxMotor->aCats[ $this->plxMotor->cible ])) {
			# On recupere les infos de la categorie
			$id = $this->plxMotor->cible;
			$name = plxUtils::strCheck($this->plxMotor->aCats[ $id ]['name']);
			$url = $this->plxMotor->aCats[ $id ]['url'];
			# On effectue l'affichage
			if($type == 'link')
				echo '<a href="'.$this->plxMotor->urlRewrite('?categorie'.intval($id).'/'.$url).'" title="'.$name.'">'.$name.'</a>';
			else
				echo $name;
		}
		# On va verifier que la categorie existe en mode article
		elseif($this->plxMotor->mode == 'article' AND isset($this->plxMotor->aCats[ $this->plxMotor->plxRecord_arts->f('categorie') ])) {
			# On recupere les infos de la categorie
			$id = $this->plxMotor->plxRecord_arts->f('categorie');
			$name = plxUtils::strCheck($this->plxMotor->aCats[ $id ]['name']);
			$url = $this->plxMotor->aCats[ $id ]['url'];
			# On effectue l'affichage
			if($type == 'link')
				echo '<a href="'.$this->plxMotor->urlRewrite('?categorie'.intval($id).'/'.$url).'" title="'.$name.'">'.$name.'</a>';
			else
				echo $name;
		}
		# Mode home
		elseif($this->plxMotor->mode == 'home') {
			if($type == 'link')
				echo '<a href="'.$this->plxMotor->urlRewrite().'" title="'.plxUtils::strCheck($this->plxMotor->aConf['title']).'">Accueil</a>';
			else
				echo 'Accueil';
		} else {
			echo 'Non class&eacute;';
		}
	}

	/**
	 * M�thode qui retourne l'identifiant de l'article en question (sans les 0 suppl�mentaires)
	 *
	 * @return	int
	 * @scope	home,categorie,article
	 * @author	Florent MONTHEL
	 **/
	public function artId() {

		return intval($this->plxMotor->plxRecord_arts->f('numero'));
	}

	/**
	 * M�thode qui affiche l'url de l'article de type relatif ou absolu
	 *
	 * @param	type	type de lien : relatif ou absolu (URL compl�te)
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function artUrl($type='relatif') {

		# Type d'URL
		#####$path = ($type == 'absolu')?$this->plxMotor->racine:'./';
		
		# On affiche l'URL
		$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
		$url = $this->plxMotor->plxRecord_arts->f('url');
		echo $this->plxMotor->urlRewrite('?article'.$id.'/'.$url);	
	}

	/**
	 * M�thode qui affiche le titre de l'article link� (variable $type='link') ou non
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function artTitle($type='') {

		if($type == 'link') { # Type lien
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo '<a href="'.$this->plxMotor->urlRewrite('?article'.$id.'/'.$url).'" title="'.$title.'">'.$title.'</a>';
		} else { # Type normal
			echo plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
		}
	}

	/**
	 * M�thode qui affiche l'auteur de l'article
	 *
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL et Stephane F
	 **/
	public function artAuthorInfos($format='<div class="infos">#art_authorinfos</div>') {
		$infos = $this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['infos'];
		if(trim($infos)!='') echo str_replace('#art_authorinfos', $infos, $format);
	}

	/**
	 * M�thode qui affiche les informations sur l'auteur de l'article
	 *
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Stephane F
	 **/
	public function artAuthor() {

		if(isset($this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['name']))
			$author = $this->plxMotor->aUsers[$this->plxMotor->plxRecord_arts->f('author')]['name'];
		else
			$author = 'inconnu';
		echo plxUtils::strCheck($author);
	}	
	
	/**
	 * M�thode qui affiche la date de publication de l'article selon le format choisi 
	 *
 	 * @param	format	format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_month, #num_year(4), #num_year(2))
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL et Stephane F.
	 **/
	public function artDate($format='#day #num_day #month #num_year(4)') {

		echo plxDate::dateIsoToHum($this->plxMotor->plxRecord_arts->f('date'),$format);

	}

	/**
	 * M�thode qui affiche l'heure de publication de l'article au format 12:45
	 *
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN et Florent MONTHEL
	 **/
	public function artHour() {

		echo plxDate::dateIsoToHum($this->plxMotor->plxRecord_arts->f('date'),'#hour:#minute');
	}

	/**
	 * M�thode qui retourne le num�ro de la cat�gorie de l'article (sans les 0 compl�mentaires)
	 *
	 * @return	int
	 * @scope	home,categorie,article
	 * @author	Stephane F
	 **/	
	public function artCatId() {

		return intval($this->plxMotor->plxRecord_arts->f('categorie'));	 
	}
	
	/**
	 * M�thode qui affiche la liste des cat�gories l'article sous forme de lien
	 * ou la cha�ne de caract�re 'Non class�' si la cat�gorie 
	 * de l'article n'existe pas
	 *
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function artCat() {

		# Initialisation de notre variable interne
		$catIds = explode(',', $this->plxMotor->plxRecord_arts->f('categorie'));
		foreach ($catIds as $idx => $catId) {
			# On verifie que la categorie n'est pas "home"
			if($catId != 'home') {
				# On va verifier que la categorie existe
				if(isset($this->plxMotor->aCats[ $catId ])) {
					# On recupere les infos de la categorie
					$name = plxUtils::strCheck($this->plxMotor->aCats[ $catId ]['name']);
					$url = $this->plxMotor->aCats[ $catId ]['url'];
					if(isset($this->plxMotor->aCats[ $this->plxMotor->cible ]['url']))
						$active = $this->plxMotor->aCats[ $this->plxMotor->cible ]['url']==$url?"active":"noactive";
					else
						$active = "noactive";
					# On effectue l'affichage
					echo '<a class="'.$active.'" href="'.$this->plxMotor->urlRewrite('?categorie'.intval($catId).'/'.$url).'" title="'.$name.'">'.$name.'</a>';
				} else { # La categorie n'existe pas
					echo 'Non class&eacute;';
				}
			} else { # Categorie "home"
				echo '<a class="active" href="'.$this->plxMotor->urlRewrite().'" title="Accueil">Accueil</a>';
			}
			if ($idx!=sizeof($catIds)-1) echo ', ';
		}
	}
	
	/**
	 * M�thode qui affiche la liste des tags l'article sous forme de lien
	 *
	 * @param	format	format du texte pour chaque tag (variable : #tag_status, #tag_url, #tag_name)
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Stephane F
	 **/
	public function artTags($format='<a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>') {

		# Initialisation de notre variable interne
		$taglist = $this->plxMotor->plxRecord_arts->f('tags');
		if(!empty($taglist)) {
			$tags = array_map('trim', explode(',', $taglist));
			foreach($tags as $tag) {
				$t = plxUtils::title2url($tag);
				$name = str_replace('#tag_url',$this->plxMotor->urlRewrite('?tag/'.$t),$format);
				$name = str_replace('#tag_name',plxUtils::strCheck($tag),$name);
				$name = str_replace('#tag_status',(($this->plxMotor->mode=='tags' AND $this->plxMotor->cible==$t)?'active':'noactive'), $name);
				echo $name;
			}
		}
		else echo 'Aucun';
	}

	/**
	 * Méthode qui retourne un lien vers un tag à partir de son nom
	 *
	 * @param	format	format du texte pour chaque tag (variable : #tag_status, #tag_url, #tag_name)
	 *              tag_name nom du tag
	 * @return	string
	 * @scope	home,categorie,article
	 * @author	BVI
	 **/
	public function tag2link($tag, $format='<a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>') {

          $t = plxUtils::title2url($tag);
          $name = str_replace('#tag_url',$this->plxMotor->urlRewrite('?tag/'.$t),$format);
          $name = str_replace('#tag_name',plxUtils::strCheck($tag),$name);
          $name = str_replace('#tag_status',(($this->plxMotor->mode=='tags' AND $this->plxMotor->cible==$t)?'active':'noactive'), $name);
          return $name;
	}

	/**
	 * Méthode qui remplace tous les tags de type {nom de tag} dans un contenu par <a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a>
	 *
	 * @param	txt texte d'entrée
	 * @return	string
	 * @scope	home,categorie,article
	 * @author	BVI
	 **/
        public function replaceTagsCallBack($matches)
        {
          // as usual: $matches[0] is the complete match
          // $matches[1] the match for the first subpattern
          // enclosed in '(...)' and so on
          return $this->tag2link($matches[1]);
        }

	public function replaceTags($txt) {

          return
            preg_replace_callback(
              '/{([^}]+)}/',
              array(&$this, 'replaceTagsCallBack'),
              $txt
              );
	}

	/**
	 * M�thode qui affiche le ch�po de l'article ainsi qu'un lien 
	 * pour lire la suite de l'article. Si l'article n'a pas de chap�, 
	 * le contenu de l'article est affich� (selon param�tres)
	 *
	 * @param	format	format d'affichage du lien pour lire la suite de l'article (#art_title)
	 * @param	content	affichage oui/non du contenu si le chap� est vide
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function artChapo($format="Lire la suite de : #art_title", $content=true) {

		# On verifie qu'un chapo existe
		if($this->plxMotor->plxRecord_arts->f('chapo') != '') {
			# On recupere les infos de l'article
			$id = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo $this->replaceTags(Markdown($this->plxMotor->plxRecord_arts->f('chapo')))."\n";
			if($format) {
				$title = str_replace("#art_title", $title, $format);
				echo '<p><a class="more" href="'.$this->plxMotor->urlRewrite('?article'.$id.'/'.$url).'" title="'.$title.'">'.$title.'</a></p>'."\n";
			}
		} else { # Pas de chapo, affichage du contenu
			if($content === true) {
				echo $this->replaceTags(Markdown($this->plxMotor->plxRecord_arts->f('content')))."\n";
			}
		}
	}

	/**
	 * M�thode qui affiche le chap� (selon param�tres) suivi du contenu de l'article
	 *
	 * @param	chapo	affichage oui/non du chapo
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL et Stephane F
	 **/
	public function artContent($chapo=true) {
	
		if($chapo === true)
			echo $this->replaceTags(Markdown($this->plxMotor->plxRecord_arts->f('chapo')))."\n"; # Chapo
		echo $this->replaceTags(Markdown($this->plxMotor->plxRecord_arts->f('content')))."\n";

	}

	/**
	 * M�thode qui affiche un lien vers le fil RSS ou ATOM des articles
	 * d'une cat�gorie pr�cise (si $categorie renseign�) ou du site tout entier
	 *
	 * @param	type	type de flux : rss ou atom
	 * @param	categorie	identifiant (sans les 0) d'une cat�gorie
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function artFeed($type='atom',$categorie='') {

		if($type == 'rss' AND $categorie != '' AND is_numeric($categorie)) # Fil RSS des articles d'une cat�gorie
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/categorie'.$categorie).'" title="Fil Rss des articles de cette cat&eacute;gorie">Fil des articles de cette cat&eacute;gorie</a>';
		elseif($type == 'rss') # Fil RSS des articles
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss').'" title="Fil Rss des articles">Fil des articles</a>';
		elseif(	$categorie != '' AND is_numeric($categorie)) # Fil ATOM des articles d'une cat�gorie
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?atom/categorie'.$categorie).'" title="Fil Atom des articles de cette cat&eacute;gorie">Fil des articles de cette cat&eacute;gorie</a>';
		else # Fil ATOM des articles
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?atom').'" title="Fil Atom des articles">Fil des articles</a>';
	}

	/**
	 * M�thode qui affiche le nombre de commentaires (sous forme de lien ou non selon le mode)
	 * d'un article
	 *
	 * @param	format	format d'affichage du texte du nombre de commentaires (#nb)
	 * @return	stdout
	 * @scope	home,categorie,article
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function artNbCom($format='#nb commentaire(s)') {

		# On recup�re le nb de commentaire selon le mode
		if($this->plxMotor->mode == 'article') $nb = $this->plxMotor->plxGlob_coms->count;
		else $nb = $this->plxMotor->plxRecord_arts->f('nb_com');
		# A t'on besoin d'afficher le nb de commentaires ?
		if((!$this->plxMotor->aConf['allow_com'] OR !$this->plxMotor->plxRecord_arts->f('allow_com')) AND !$nb)
			return;
		# On modifie nos motifs
		$txt = str_replace('#nb',$nb,$format);
		# On effectue l'affichage selon le mode
		if($this->plxMotor->mode == 'article') {
			echo $txt;
		} else {
			# On recupere les infos de l'article
			$num = intval($this->plxMotor->plxRecord_arts->f('numero'));
			$title = plxUtils::strCheck($this->plxMotor->plxRecord_arts->f('title'));
			$url = $this->plxMotor->plxRecord_arts->f('url');
			# On effectue l'affichage
			echo '<a href="'.$this->plxMotor->urlRewrite('?article'.$num.'/'.$url).'#comments" title="'.$txt.' pour '.$title.'">'.$txt.'</a>';
		}
	}

	/**
	 * M�thode qui affiche la liste des $max derniers articles.
	 * Si la variable $cat_id est renseign�e, seulement les articles de cette cat�gorie seront retourn�s.
	 * On tient compte si la cat�gorie est active
	 *
	 * @param	format	format du texte pour chaque article (variable: #art_id, #art_url, #art_status, #art_author, #art_title, #art_content, #art_content(num), #art_date, #art_hour, #cat_list)
	 * @param	max		nombre d'articles maximum
	 * @param	cat_id	id de la cat�gorie cible (1,home)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function lastArtList($format='<li><a href="#art_url" title="#art_title">#art_title</a></li>',$max=5,$cat_id='') {

		# G�n�ration de notre motif
/*
		if(empty($cat_id))
			$motif = '/^[0-9]{4}.[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		elseif($cat_id == 'home')
			$motif = '/^[0-9]{4}.(home[0-9,]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		else
			$motif = '/^[0-9]{4}.[0-9,]*'.str_pad($cat_id,3,'0',STR_PAD_LEFT).'[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
*/
		if(empty($cat_id))
			$motif = '/^[0-9]{4}.[home|0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		else
			$motif = '/^[0-9]{4}.[home|0-9,]*'.str_pad($cat_id,3,'0',STR_PAD_LEFT).'[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';			
			
		# Nouvel objet plxGlob et r�cup�ration des fichiers
		$plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_articles']);
		if($aFiles = $plxGlob_arts->query($motif,'art','rsort',0,$max,'before')) {
			foreach($aFiles as $v) { # On parcourt tous les fichiers
				$art = $this->plxMotor->parseArticle(PLX_ROOT.$this->plxMotor->aConf['racine_articles'].$v);
				$num = intval($art['numero']);
				$date = $art['date'];
				$content = strip_tags(Markdown($art['chapo']).Markdown($art['content']));

				if(($this->plxMotor->mode == 'article') AND ($art['numero'] == $this->plxMotor->cible))
					$status = 'active';
				else
					$status = 'noactive';
				# Mise en forme de la liste des cat�gories
				$catList = array();
				$catIds = explode(',', $art['categorie']);
				foreach ($catIds as $idx => $catId) {
					if(isset($this->plxMotor->aCats[$catId])) { # La cat�gorie existe
						$catName = plxUtils::strCheck($this->plxMotor->aCats[$catId]['name']);
						$catUrl = $this->plxMotor->aCats[$catId]['url'];
						$catList[] = '<a title="'.$catName.'" href="'.$this->plxMotor->urlRewrite('?categorie'.intval($catId).'/'.$catUrl).'">'.$catName.'</a>';
//					} 
//					elseif($catId=='home') { 
//						$catList[] = '<a href="'.$this->urlRewrite().'">Accueil</a>';
					} else {
						$catList[] = 'Non class&eacute;';
					}
				}
				# On modifie nos motifs
				$row = str_replace('#art_id',$num,$format);
				$row = str_replace('#cat_list', implode(', ',$catList), $row);				
				$row = str_replace('#art_url',$this->plxMotor->urlRewrite('?article'.$num.'/'.$art['url']),$row);
				$row = str_replace('#art_status',$status,$row);
				$row = str_replace('#art_author',plxUtils::strCheck($this->plxMotor->aUsers[$art['author']]['name']),$row);
				$row = str_replace('#art_title',plxUtils::strCheck($art['title']),$row);
				while(preg_match('/#art_content\(([0-9]+)\)/',$row,$capture))
					$row = str_replace('#art_content('.$capture[1].')',plxUtils::strCut($content,$capture[1]),$row);
				$row = str_replace('#art_content',$content,$row);
				$row = str_replace('#art_date',plxDate::dateIsoToHum($date,'#num_day/#num_month/#num_year(4)'),$row);
				$row = str_replace('#art_hour',plxDate::dateIsoToHum($date,'#hour:#minute'),$row);
				# On gen�re notre ligne
				echo $row;
			}
		}
	}

	/**
	 * M�thode qui affiche l'id du commentaire pr�c�d� de la lettre 'c'
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comId() {

		echo 'c'.$this->plxMotor->plxRecord_coms->f('numero');
	}

	/**
	 * M�thode qui affiche l'url du commentaire de type relatif ou absolu
	 *
	 * @param	type	type de lien : relatif ou absolu (URL compl�te)
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function comUrl($type='relatif') {

		# Type d'URL
		#####$path = ($type == 'absolu')?$this->plxMotor->racine:'./';
		
		# On affiche l'URL
		$id = $this->plxMotor->plxRecord_coms->f('numero');
		$artId = $this->plxMotor->plxRecord_coms->f('article');
		echo $this->urlRewrite('?article'.intval($artId).'/#'.'c'.$id);
	}

	/**
	 * M�thode qui affiche l'auteur du commentaires link� ou non 
	 *
	 * @param	type	type d'affichage : link => sous forme de lien
	 * @return	stdout
	 * @scope	article
	 * @author	Anthony GU�RIN et Florent MONTHEL
	 **/
	public function comAuthor($type='') {

		# Initialisation de nos variables interne
		$author = $this->plxMotor->plxRecord_coms->f('author');
		$site = $this->plxMotor->plxRecord_coms->f('site');
		if($type == 'link' AND $site != '') # Type lien
			echo '<a href="'.$site.'" title="'.$author.'">'.$author.'</a>';
		else # Type normal
			echo $author;
	}

	/**
	 * M�thode qui affiche le type du commentaire (admin ou normal)
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comType() {

		echo $this->plxMotor->plxRecord_coms->f('type');
	}

	/**
	 * M�thode qui affiche la date de publication d'un commentaire delon le format choisi
	 *
 	 * @param	format	format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_month, #num_year(2), #num_year(4))	 
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL et Stephane F
	 **/
	public function comDate($format='#day #num_day #month #num_year(4) &agrave; #hour:#minute') {

		echo plxDate::dateIsoToHum($this->plxMotor->plxRecord_coms->f('date'),$format);
	}

	/**
	 * M�thode qui affiche le contenu d'un commentaire
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comContent() {

		echo nl2br($this->plxMotor->plxRecord_coms->f('content'));
	}

	/**
	 * M�thode qui affiche si besoin le message g�n�r� par le syst�me
	 * suite � la cr�ation d'un commentaire
	 *
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comMessage() {

		//if($this->plxMotor->message_com) echo $this->plxMotor->message_com;
		if(isset($_SESSION['msgcom']) AND !empty($_SESSION['msgcom'])) {
			echo $_SESSION['msgcom'];
			$_SESSION['msgcom']='';
		}
			
	}

	/**
	 * M�thode qui affiche si besoin la variable $_GET[$key] suite au d�p�t d'un commentaire
	 *
	 * @param	key		cl� du tableau GET
	 * @param	defaut	valeur par d�faut si variable vide
	 * @return	stdout
	 * @scope	article
	 * @author	Florent MONTHEL
	 **/
	public function comGet($key,$defaut='') {

		if(isset($_SESSION['msg'][$key]) AND !empty($_SESSION['msg'][$key])) {
			echo plxUtils::strCheck($_SESSION['msg'][$key]);
			$_SESSION['msg'][$key]='';
		}
		else echo $defaut;
	
	}

	/**
	 * M�thode qui affiche un lien vers le fil RSS ou ATOM des commentaires
	 * d'un article pr�cis (si $article renseign�) ou du site tout entier
	 *
	 * @param	type	type de flux : rss ou atom
	 * @param	article	identifiant (sans les 0) d'un article
	 * @return	stdout
	 * @scope	global
	 * @author	Anthony GU�RIN, Florent MONTHEL, Stephane F
	 **/
	public function comFeed($type='atom',$article='') {

		if($type == 'rss' AND $article != '' AND is_numeric($article)) # Fil RSS des commentaires d'un article
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/commentaires/article'.$article).'" title="Fil Rss des commentaires de cet article">Fil des commentaires de cet article</a>';
		elseif($type == 'rss') # Fil RSS des commentaires global
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?rss/commentaires').'" title="Fil Rss global des commentaires">Fil des commentaires</a>';
		elseif(	$article != '' AND is_numeric($article)) # Fil ATOM des commentaires d'un article
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?atom/commentaires/article'.$article).'" title="Fil Atom des commentaires de cet article">Fil des commentaires de cet article</a>';
		else # Fil ATOM des commentaires global
			echo '<a href="'.$this->plxMotor->urlRewrite('feed.php?atom/commentaires').'" title="Fil Atom global des commentaires">Fil des commentaires</a>';
	}

	/**
	 * M�thode qui affiche la liste des $max derniers commentaires.
	 * Si la variable $art_id est renseign�e, seulement les commentaires de cet article seront retourn�s.
	 *
	 * @param	format	format du texte pour chaque commentaire (variable: #com_id, #com_url, #com_author, #com_content(num), #com_content, #com_date, #com_hour)
	 * @param	max		nombre de commentaires maximum
	 * @param	art_id	id de l'article cible (24,3)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function lastComList($format='<li><a href="#com_url">#com_author a dit :</a><br/>#com_content(50)</li>',$max=5,$art_id='') {

		# G�n�ration de notre motif
		if(empty($art_id))
			$motif = '/^[0-9]{4}.[0-9]{10}-[0-9]+.xml$/';
		else
			$motif = '/^'.str_pad($art_id,4,'0',STR_PAD_LEFT).'.[0-9]{10}-[0-9]+.xml$/';
		# Nouvel objet plxGlob et r�cup�ration des fichiers
		$plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_commentaires']);
		$aFiles = $plxGlob_coms->query($motif,'com','rsort',0,$max);
		# On parse les fichiers
		if(is_array($aFiles)) { # On a des fichiers
			foreach($aFiles as $v) { # On parcourt tous les fichiers
				$com = $this->plxMotor->parseCommentaire(PLX_ROOT.$this->plxMotor->aConf['racine_commentaires'].$v);

				$url = '?article'.intval($com['article']).'/#c'.$com['numero'];
				$date = $com['date'];
				$content = strip_tags($com['content']);
				# On modifie nos motifs
				$row = str_replace('#com_id',$com['numero'],$format);
				$row = str_replace('#com_url',$this->plxMotor->urlRewrite($url),$row);
				$row = str_replace('#com_author',$com['author'],$row);
				while(preg_match('/#com_content\(([0-9]+)\)/',$row,$capture)) {
					if($com['author'] == 'admin')
						$row = str_replace('#com_content('.$capture[1].')',plxUtils::strCut($content,$capture[1]),$row);
					else
						$row = str_replace('#com_content('.$capture[1].')',plxUtils::strCheck(plxUtils::strCut(plxUtils::strRevCheck($content),$capture[1])),$row);
				}
				$row = str_replace('#com_content',$content,$row);
				$row = str_replace('#com_date',plxDate::dateIsoToHum($date,'#num_day/#num_month/#num_year(4)'),$row);
				$row = str_replace('#com_hour',plxDate::dateIsoToHum($date,'#hour:#minute'),$row);	
				# On gen�re notre ligne
				echo $row;
			}
		}
	}

	/**
	 * M�thode qui affiche la liste des pages statiques.
	 * Si la variable $extra est renseign�e, un lien vers la 
	 * page d'accueil (nomm� $extra) sera mis en place en premi�re position
	 *
	 * @param	extra	nom du lien vers la page d'accueil
	 * @param	format	format du texte pour chaque page (variable : #static_id, #static_status, #static_url, #static_name, #group_di, #group_class, #group_name)
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function staticList($extra='', $format='<li id="#static_id" class="#static_class"><a href="#static_url" class="#static_status" title="#static_name">#static_name</a></li>', $format_group='<li id="#group_id" class="#group_class">#group_name</li>') {
			
		$home = (empty($this->plxMotor->get) AND basename($_SERVER['SCRIPT_NAME'])=="index.php");
		# Si on a la variable extra, on affiche un lien vers la page d'accueil (avec $extra comme nom)
		if($extra != '') {
			$stat = str_replace('#static_id','static-home',$format);
			$stat = str_replace('#static_class','static-group',$stat);
			$stat = str_replace('#static_url',$this->plxMotor->urlRewrite(),$stat);
			$stat = str_replace('#static_name',plxUtils::strCheck($extra),$stat);
			$stat = str_replace('#static_status',($home==true?"active":"noactive"), $stat);
			echo $stat;		
		}
		# On verifie qu'il y a des pages statiques
		if($this->plxMotor->aStats) {
			$group_name='';
			foreach($this->plxMotor->aStats as $k=>$v) {
				if($v['active'] == 1 AND $v['menu'] == 'oui') { # La page  est bien active et dispo ds le menu
					# On modifie nos motifs
					if(!empty($v['group']) AND $group_name!=$v['group']) {
						$group = str_replace('#group_id','static-group-'.plxUtils::title2url($v['group']),$format_group);
						$group = str_replace('#group_class','static-group',$group);
						$group = str_replace('#group_name',plxUtils::strCheck($v['group']),$group);
						echo $group;
					}
					$stat = str_replace('#static_id','static-'.intval($k),$format);
					if(empty($v['group']))
						$stat = str_replace('#static_class','static-group',$stat);
					else
						$stat = str_replace('#static_class','static-menu',$stat);
					$stat = str_replace('#static_url',$this->plxMotor->urlRewrite('?static'.intval($k).'/'.$v['url']),$stat);
					$stat = str_replace('#static_name',plxUtils::strCheck($v['name']),$stat);
					$stat = str_replace('#static_status',(($home===false AND $this->staticId()==intval($k))?'static active':'noactive'), $stat);
					echo $stat;
					$group_name=$v['group']; # pour g�rer la rupture au niveau de l'affichage
				}
			} # Fin du while
		}
	}

	/**
	 * M�thode qui retourne l'id de la page statique active
	 *
	 * @return	int
	 * @scope	static
	 * @author	Florent MONTHEL
	 **/
	public function staticId() {

		# On va verifier que la categorie existe en mode categorie
		if($this->plxMotor->mode == 'static' AND isset($this->plxMotor->aStats[ $this->plxMotor->cible ]))
			return intval($this->plxMotor->cible);
	}

	/**
	 * M�thode qui affiche l'url de la page statique de type relatif ou absolu
	 *
	 * @param	type	type de lien : relatif ou absolu (URL compl�te)
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function staticUrl($type='relatif') {
	
		# Recup�ration ID URL
		$staticId = $this->staticId();
		$staticIdFill = str_pad($staticId,3,'0',STR_PAD_LEFT);
		if(!empty($staticId) AND isset($this->plxMotor->aStats[ $staticIdFill ]))
			echo $this->plxMotor->urlRewrite('?static'.$staticId.'/'.$this->plxMotor->aStats[ $staticIdFill ]['url']);
	}

	/**
	 * M�thode qui affiche le titre de la page statique
	 *
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL
	 **/
	public function staticTitle() {

		echo plxUtils::strCheck($this->plxMotor->aStats[ $this->plxMotor->cible ]['name']);
	}
	
	/**
	 * M�thode qui affiche la date de la derni�re modification de la page statique selon le format choisi
	 *
	 * @param	format    format du texte de la date (variable: #minute, #hour, #day, #month, #num_day, #num_month, #num_year(4), #num_year(2))
	 * @return	stdout
	 * @scope	static
	 * @author	Anthony T.
	 **/
	public function staticDate($format='#day #num_day #month #num_year(4)') {
		
		# On genere le nom du fichier dont on veux r�cup�rer la date
		$file = PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$this->plxMotor->cible;
		$file .= '.'.$this->plxMotor->aStats[ $this->plxMotor->cible ]['url'].'.php';
		# Test de l'existence du fichier
		if(!file_exists($file)) return;
		# On r�cup�re la date de la derni�re modification du fichier qu'on formate
		$date = date('Y-m-d\TH:i:s', filemtime($file)).$this->plxMotor->aConf['delta'];
		echo plxDate::dateIsoToHum($date, $format);
	}

	/**
	 * M�thode qui inclut le code source de la page statique
	 *
	 * @return	stdout
	 * @scope	static
	 * @author	Florent MONTHEL
	 **/
	public function staticContent() {

		# On va verifier que la page a inclure est lisible
		if($this->plxMotor->aStats[ $this->plxMotor->cible ]['readable'] == 1) {
			# On genere le nom du fichier a inclure
			$file = PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$this->plxMotor->cible;
			$file .= '.'.$this->plxMotor->aStats[ $this->plxMotor->cible ]['url'].'.php';
			# Inclusion du fichier
			require $file;
		} else {
			echo '<p>Cette page est actuellement en cours de r&eacute;daction</p>';
		}
			
	}
	
	/**
	 * M�thode qui affiche une page statique en lui passant son id (si cette page est active ou non)
	 *
	 * @param	id	id num�rique de la page statique
	 * @return	stdout
	 * @scope	global
	 * @author	St�phane F
	 **/	
	public function staticInclude($id) {
		
		# On g�n�re un nouvel objet plxGlob
		$plxGlob_stats = plxGlob::getInstance(PLX_ROOT.$this->plxMotor->aConf['racine_statiques']);
		if($files = $plxGlob_stats->query('/^'.str_pad($id,3,'0',STR_PAD_LEFT).'.[a-z0-9-]+.php$/')) {
			include(PLX_ROOT.$this->plxMotor->aConf['racine_statiques'].$files[0]);
		}
	}

	/**
	 * M�thode qui affiche la pagination
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function pagination() {
	
		# On verifie que la variable bypage n'est pas nulle
		if($this->plxMotor->bypage AND $this->plxMotor->plxGlob_arts->count>$this->plxMotor->bypage) {
			# on supprime le n� de page courante dans l'url
			$arg_url = $this->plxMotor->get;
			if(preg_match('/(\/?)(page[0-9]+)$/',$arg_url,$capture)) {
				$arg_url = str_replace($capture[2], '', $arg_url);
			}
			if(!empty($arg_url) AND empty($capture[1])) $arg_url .= '/';
			# Calcul des pages
			$prev_page = $this->plxMotor->page - 1;
			$next_page = $this->plxMotor->page + 1;
			$last_page = ceil($this->plxMotor->plxGlob_arts->count/$this->plxMotor->bypage);
			# Generation des URLs
			$p_url = $this->plxMotor->urlRewrite('?'.$arg_url.'page'.$prev_page); # Page precedente
			$n_url = $this->plxMotor->urlRewrite('?'.$arg_url.'page'.$next_page); # Page suivante
			$l_url = $this->plxMotor->urlRewrite('?'.$arg_url.'page'.$last_page); # Derniere page
			$f_url = $this->plxMotor->urlRewrite('?'.$arg_url.'page1'); # Premiere page
			# On effectue l'affichage
			if($this->plxMotor->page > 2) # Si la page active > 2 on affiche un lien 1ere page
				echo '<span class="p_first"><a href="'.$f_url.'" title="Aller &agrave; la premi&egrave;re page">&lt;&lt;</a></span> | ';
			if($this->plxMotor->page > 1) # Si la page active > 1 on affiche un lien page precedente
				echo '<span class="p_prev"><a href="'.$p_url.'" title="Page pr&eacute;c&eacute;dente">&lt; pr&eacute;c&eacute;dente</a></span> | ';
			# Affichage de la page courante
			echo '<span class="p_page">page '.$this->plxMotor->page.' sur '.$last_page.'</span>';
			if($this->plxMotor->page < $last_page) # Si la page active < derniere page on affiche un lien page suivante
				echo ' | <span class="p_next"><a href="'.$n_url.'" title="Page suivante">suivante &gt;</a></span>';
			if(($this->plxMotor->page + 1) < $last_page) # Si la page active++ < derniere page on affiche un lien derniere page
				echo ' | <span class="p_last"><a href="'.$l_url.'" title="Aller &agrave; la derni&egrave;re page">&gt;&gt;</a></span>';
		}
	}	
	
	/**
	 * M�thode qui affiche la question du capcha
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function capchaQ() {

		echo $this->plxMotor->plxCapcha->q();
	}

	/**
	 * M�thode qui affiche la r�ponse du capcha crypt�e en MD5
	 *
	 * @return	stdout
	 * @scope	global
	 * @author	Florent MONTHEL
	 **/
	public function capchaR() {

		echo $this->plxMotor->plxCapcha->r();
	}

	/**
	 * M�thode qui affiche le message d'erreur de l'objet plxErreur
	 *
	 * @return	stdout
	 * @scope	erreur
	 * @author	Florent MONTHEL
	 **/
	public function erreurMessage() {

		echo $this->plxMotor->plxErreur->getMessage();
	}
	
	/**
	 * M�thode qui affiche la liste de tous les tags.
	 *
	 * @param	format	format du texte pour chaque tag (variable : #tag_status, #tag_url, #tag_name, #nb_art)
	 * @param	max		nombre maxi de tags � afficher
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function tagList($format='<li><a class="#tag_status" href="#tag_url" title="#tag_name">#tag_name</a></li>', $max='5') {

		$time = @date('YmdHi');
		$array=array();
		# On verifie qu'il y a des tags
		if($this->plxMotor->aTags) {
			# On liste les tags sans cr�er de doublon
			foreach($this->plxMotor->aTags as $tag) {
				if($tag['date']<=$time AND $tag['active']) {
					if($tags = array_map('trim', explode(',', $tag['tags']))) {
						foreach($tags as $tag) {
							if($tag!='') {
								if(!isset($array[$tag]))
									$array[$tag]=1;
								else
									$array[$tag]++;
							}
						}
					}
				}
			}
		}
		natsort($array);		
		if(intval($max)>0) $array=array_slice($array, 0, $max, true);
		# On affiche la liste
		$size=0;
		foreach($array as $tagname => $nbtags) {
			$t = plxUtils::title2url($tagname);
			$name = str_replace('#tag_id','tag-'.$size++,$format);
			$name = str_replace('#tag_url',$this->plxMotor->urlRewrite('?tag/'.$t),$name);
			$name = str_replace('#tag_name',plxUtils::strCheck($tagname),$name);
			$name = str_replace('#nb_art',$nbtags,$name);			
			$name = str_replace('#tag_status',(($this->plxMotor->mode=='tags' AND $this->plxMotor->cible==$t)?'active':'noactive'), $name);			
			echo $name;
		}
	}
	
	/**
	 * M�thode qui affiche la liste des archives
	 *
	 * @param	format	format du texte pour l'affichage (variable : #archives_id, #archives_status, #archives_nbart, #archives_url, #archives_name)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
    public function archList($format='<li id="#archives_id"><a class="#archives_status" href="#archives_url" title="#archives_name">#archives_name</a></li>'){
		$curYear=date('Y');
        $array = array();
		if($files = $this->plxMotor->plxGlob_arts->query('/^[0-9]{4}.[home|0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/','art','rsort',0,false,'before')) {
			foreach($files as $id => $filename){
				if(preg_match('/([0-9]{4}).([home|0-9,]*).[0-9]{3}.([0-9]{4})([0-9]{2})([0-9]{6}).([a-z0-9-]+).xml$/',$filename,$capture)){
					if($capture[3]==$curYear) {
						if(!isset($array[$capture[3]][$capture[4]])) $array[$capture[3]][$capture[4]]=1;
						else $array[$capture[3]][$capture[4]]++;
					} else {
						if(!isset($array[$capture[3]])) $array[$capture[3]]=1;
						else $array[$capture[3]]++;
					}
				}
			}
			krsort($array);
			# Affichage pour l'ann�e en cours
			if(isset($array[$curYear])) {
				foreach($array[$curYear] as $month => $nbarts){
					$name = str_replace('#archives_id','archives-'.$curYear.$month,$format);
					$name = str_replace('#archives_name',plxDate::getCalendar('month', $month).' '.$curYear,$name);
					$name = str_replace('#archives_url', $this->plxMotor->urlRewrite('?archives/'.$curYear.'/'.$month), $name);
					$name = str_replace('#archives_nbart',$nbarts,$name);
					$name = str_replace('#archives_status',(($this->plxMotor->mode=="archives" AND $this->plxMotor->cible==$curYear.$month)?'active':'noactive'), $name);
					echo $name;
				}
			}
			# Affichage pour les ann�es pr�c�dentes
			unset($array[$curYear]);
			foreach($array as $year => $nbarts){
				$name = str_replace('#archives_id','archives-'.$year,$format);
				$name = str_replace('#archives_name',$year,$name);
				$name = str_replace('#archives_url', $this->plxMotor->urlRewrite('?archives/'.$year), $name);
				$name = str_replace('#archives_nbart',$nbarts,$name);
				$name = str_replace('#archives_status',(($this->plxMotor->mode=="archives" AND $this->plxMotor->cible==$year)?'active':'noactive'), $name);
				echo $name;
			}
		}
    }
	
	/**
	 * M�thode qui affiche un lien vers la page blog.php
	 *
	 * @param	format	format du texte pour l'affichage (variable : #page_id, #page_status, #page_url, #page_name)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
    public function pageBlog($format='<li id="#page_id"><a class="#page_status" href="#page_url" title="#page_name">#page_name</a></li>') {

		if($this->plxMotor->aConf['homestatic']!='' AND $this->plxMotor->aStats[$this->plxMotor->aConf['homestatic']]['active']) {
			$name = str_replace('#page_id','page-blog',$format);
			$name = str_replace('#page_status',(preg_match('/^blog.php/', basename($_SERVER['SCRIPT_NAME']))?'active':'noactive'),$name);
			$name = str_replace('#page_url','blog.php',$name);
			$name = str_replace('#page_name','Blog',$name);
			echo $name;
		}
	}

	/**
	 * M�thode qui ajoute, s'il existe, le fichier css associ� � un template
	 *
	 * @param	css_dir     r�pertoire de stockage des fichiers css (avec un / � la fin)
	 * @return	stdout
	 * @scope	global
	 * @author	Stephane F
	 **/
	public function templateCss($css_dir='') {
		$theme = 'themes/'.$this->plxMotor->style.'/';
		$css = str_replace('php','css',$this->plxMotor->template);
		if(is_file($theme.$css))
			echo "\t".'<link rel="stylesheet" type="text/css" href="'.$theme.$css.'" media="screen" />'."\n";
	}
}
?>