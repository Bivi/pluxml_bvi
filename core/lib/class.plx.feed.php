<?php
include_once dirname(__FILE__)."/../vendor/markdown.php";

/**
 * Classe plxFeed responsable du traitement global des flux de syndication
 *
 * @package PLX
 * @author	Florent MONTHEL, Stephane F
 **/
class plxFeed extends plxMotor {

	/**
	 * Constructeur qui initialise certaines variables de classe
	 * et qui lance le traitement initial
	 *
	 * @param	filename	emplacement du fichier XML de configuration
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
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
		
		$this->get = plxUtils::getGets();
		
		$this->getConfiguration($filename);
		$this->racine = $this->aConf['racine'];
		$this->bypage = $this->aConf['bypage_feed'];
		$this->tri = 'desc';
		$this->clef = (!empty($this->aConf['clef']))?$this->aConf['clef']:'';
		
		$this->plxGlob_arts = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_articles']);
		$this->plxGlob_coms = plxGlob::getInstance(PLX_ROOT.$this->aConf['racine_commentaires']);
		$this->getCategories(PLX_ROOT.$this->aConf['categories']);
		$this->getUsers(PLX_ROOT.$this->aConf['users']);	
	}

	/**
	 * Méthode qui effectue une analyse de la situation et détermine 
	 * le mode à appliquer. Cette méthode alimente ensuite les variables 
	 * de classe adéquates
	 *
	 * @return	null
	 * @author	Florent MONTHEL, Stéphane F
	 **/
	public function fprechauffage() {
	
		if($this->get AND preg_match('/^(atom|rss)$/',$this->get,$capture)) {
			$this->feed = $capture[1]; # Type de flux
			$this->mode = 'article'; # Mode du flux
			# On modifie le motif de recherche
			$this->motif = '/^[0-9]{4}.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		elseif($this->get AND preg_match('/^(atom|rss)\/categorie([0-9]+$)/',$this->get,$capture)) {
			$this->feed = $capture[1]; # Type de flux
			$this->mode = 'article'; # Mode du flux
			# On récupère la catégorie cible
			$this->cible = str_pad($capture[2],3,'0',STR_PAD_LEFT); # On complete sur 3 caracteres
			# On modifie le motif de recherche
			$this->motif = '/^[0-9]{4}.[0-9,]*'.$this->cible.'[0-9,]*.[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		elseif($this->get AND preg_match('/^(atom|rss)\/commentaires$/',$this->get,$capture)) {
			$this->feed = $capture[1]; # Type de flux
			$this->mode = 'commentaire'; # Mode du flux
		}
		elseif($this->get AND preg_match('/^(atom|rss)\/commentaires\/article([0-9]+$)/',$this->get,$capture)) {
			$this->feed = $capture[1]; # Type de flux
			$this->mode = 'commentaire'; # Mode du flux
			# On recupere l'article cible
			$this->cible = str_pad($capture[2],4,'0',STR_PAD_LEFT); # On complete sur 4 caracteres
			# On modifie le motif de recherche
			$this->motif = '/^'.$this->cible.'.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
		elseif($this->get AND preg_match('/^admin([a-zA-Z0-9]+)\/commentaires\/(hors|en)-ligne$/',$this->get,$capture)) {
			$this->feed = 'atom'; # Type de flux
			$this->mode = 'admin'; # Mode du flux
			$this->cible = '-';	# /!\: il ne faut pas initialiser à blanc sinon ça prend par défaut les commentaires en ligne (faille sécurité)
			if ($capture[1] == $this->clef) {
				if($capture[2] == 'hors')
					$this->cible = '_';
				elseif($capture[2] == 'en')
					$this->cible = '';
			}
		} else {
			$this->feed = 'atom'; # Type de flux
			$this->mode = 'article'; # Mode du flux
			# On modifie le motif de recherche
			$this->motif = '/^[0-9]{4}.([0-9,|home]*).[0-9]{3}.[0-9]{12}.[a-z0-9-]+.xml$/';
		}
	}

	/**
	 * Méthode qui effectue le traitement selon le mode du moteur
	 *
	 * @return	null ou redirection si une erreur est détectée
	 * @author	Florent MONTHEL, Stéphane F
	 **/	
	public function fdemarrage() {

		# Flux de commentaires d'un article precis
		if($this->mode == 'commentaire' AND $this->cible) {
			$this->getFiles(); # Recuperation du fichier de l'article cible
			$this->getArticles(); # Recuperation de l'article cible (on le parse)
			if(!$this->plxGlob_arts->count OR !$this->plxRecord_arts->size) { # Aucun article, on redirige
				$this->cible = $this->cible + 0;
				header('Location: '.$this->urlRewrite('?article'.$this->cible.'/'));
				exit;
			} else { # On récupère les commentaires
				$regex = '/^'.$this->cible.'.[0-9]{10}-[0-9]+.xml$/';
				$this->getCommentaires($regex,'rsort',0,$this->bypage);
			}
		}
		# Flux de commentaires global
		elseif($this->mode == 'commentaire') {
			$regex = '/^[0-9]{4}.[0-9]{10}-[0-9]+.xml$/';
			$this->getCommentaires($regex,'rsort',0,$this->bypage);
		}
		# Flux admin
		elseif($this->mode == 'admin') {
			if(empty($this->clef)) { # Clef non initialisée
				header('Content-Type: text/plain');
				echo 'Les URLs privees n\'ont pas ete initialisees dans vos parametres d\'administration !';
				exit;
			}
			# On recupere les commentaires
			$this->getCommentaires('/^'.$this->cible.'[0-9]{4}.[0-9]{10}-[0-9]+.xml$/','rsort',0,$this->bypage);
		}
		# Flux d'articles
		else {
			# Flux des articles d'une catégorie précise
			if($this->cible) {
				# On va tester la catégorie
				if(empty($this->aCats[ $this->cible ])) { # Pas de catégorie, on redirige
					$this->cible = $this->cible + 0;
					header('Location: '.$this->urlRewrite('?categorie'.$this->cible.'/'));
					exit;
				}
			}
			$this->getFiles(); # Recupération des fichier des articles
			$this->getArticles(); # Recupération des articles (on les parse)
		}
		
		# Selon le mode et le feed on appelle la méthode adéquate...
		switch($this->mode.'-'.$this->feed) {
			case 'article-atom' : $this->getAtomArticles(); break;
			case 'article-rss' : $this->getRssArticles(); break;
			case 'commentaire-atom' : $this->getAtomCommentaires(); break;
			case 'commentaire-rss' : $this->getRssCommentaires(); break;
			case 'admin-atom' : $this->getAdminCommentaires(); break;
			default : break;
		}
	}

	/**
	 * Méthode qui affiche le flux atom des articles du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function getAtomArticles() {

		# Initialisation
		$last_updated = '';
		$entry = '';
		if($this->cible) { # Articles d'une catégorie
			$catId = $this->cible + 0;
			$title = $this->aConf['title'].' - '.$this->aCats[ $this->cible ]['name'];
			$link = $this->urlRewrite('?categorie'.$catId.'/'.$this->aCats[ $this->cible ]['url']);
			$link_feed = $this->urlRewrite('feed.php?atom/categorie'.$catId);
		} else { # Articles globaux
			$title = $this->aConf['title'];
			$link = $this->urlRewrite();
			$link_feed = $this->urlRewrite('feed.php?atom');
		}
		# On va boucler sur les articles (si il y'en a)
		if($this->plxRecord_arts) {
			while($this->plxRecord_arts->loop()) {
				# Traitement initial
				if($this->aConf['feed_chapo']) {
					$content = Markdown($this->plxRecord_arts->f('chapo'));
					if(trim($content)=='') $content = Markdown($this->plxRecord_arts->f('content'));
				} else {
					$content = Markdown($this->plxRecord_arts->f('chapo')).Markdown($this->plxRecord_arts->f('content'));
				}
				$content .= $this->aConf['feed_footer'];
				$artId = $this->plxRecord_arts->f('numero') + 0;
				$author = $this->aUsers[$this->plxRecord_arts->f('author')]['name'];
				# Initialisation de notre variable interne
				$categorie = '';
				$catIds = explode(',', $this->plxRecord_arts->f('categorie'));
				foreach ($catIds as $idx => $catId) {
					# On verifie que la categorie n'est pas "home"
					if($catId != 'home') {
						# On va verifier que la categorie existe
						if(isset($this->aCats[ $catId ])) {
							# On recupere les infos de la categorie
							$categorie .= $this->aCats[ $catId ]['name'];
						} else { # La categorie n'existe pas
							$categorie .= 'Non class&eacute;';
						}
					} else { # Categorie "home"
						$categorie .= 'Accueil';
					}
					if ($idx!=sizeof($catIds)-1) $categorie .= ', ';
				}	
				# On check la date de publication
				if($this->plxRecord_arts->f('date') > $last_updated)
					$last_updated = $this->plxRecord_arts->f('date');
				# On affiche le flux dans un buffer
				$entry .= '<entry>'."\n";
				$entry .= "\t".'<title>'.plxUtils::strCheck($this->plxRecord_arts->f('title')).'</title> '."\n";
				$entry .= "\t".'<link href="'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'"/>'."\n";
				$entry .= "\t".'<id>urn:md5:'.md5($this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url'))).'</id>'."\n";
				$entry .= "\t".'<updated>'.$this->plxRecord_arts->f('date').'</updated>'."\n";
				$entry .= "\t".'<author><name>'.plxUtils::strCheck($author).'</name></author>'."\n";
				$entry .= "\t".'<dc:subject>'.plxUtils::strCheck($categorie).'</dc:subject>'."\n";
				$entry .= "\t".'<content type="html">'.plxUtils::strCheck(plxUtils::rel2abs($this->racine,$content)).'</content>'."\n";
				$entry .= '</entry>'."\n";
			}
		}
		
		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		echo '<title type="html">'.plxUtils::strCheck($title).'</title>'."\n";
		echo '<subtitle type="html">'.plxUtils::strCheck($this->aConf['description']).'</subtitle>'."\n";
		echo '<link href="'.$link_feed.'" rel="self" type="application/atom+xml"/>'."\n";
		echo '<link href="'.$link.'" rel="alternate" type="text/html"/>'."\n";
		echo '<updated>'.$last_updated.'</updated>'."\n";
		echo '<id>urn:md5:'.md5($link).'</id>'."\n";
		echo '<generator uri="http://pluxml.org/">PluXml '.$this->version.'</generator>'."\n";
		echo $entry;
		echo '</feed>';
	}

	/**
	 * Méthode qui affiche le flux rss des articles du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL, Stephane F
	 **/
	public function getRssArticles() {

		# Initialisation
		$last_updated = '';
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Articles d'une catégorie
			$catId = $this->cible + 0;
			$title = $this->aConf['title'].' - '.$this->aCats[ $this->cible ]['name'];
			$link = $this->urlRewrite('?categorie'.$catId.'/'.$this->aCats[ $this->cible ]['url']);
		} else { # Articles globaux
			$title = $this->aConf['title'];
			$link = $this->urlRewrite();
		}
		# On va boucler sur les articles (si il y'en a)
		if($this->plxRecord_arts) {
			while($this->plxRecord_arts->loop()) {
				# Traitement initial
				# Traitement initial
				if($this->aConf['feed_chapo']) {
					$content = Markdown($this->plxRecord_arts->f('chapo'));
					if(trim($content)=='') $content = Markdown($this->plxRecord_arts->f('content'));
				} else {
					$content = Markdown($this->plxRecord_arts->f('chapo')).Markdown($this->plxRecord_arts->f('content'));
				}
				$content .= $this->aConf['feed_footer'];				
				$artId = $this->plxRecord_arts->f('numero') + 0;
				$author = $this->aUsers[$this->plxRecord_arts->f('author')]['name'];
				# On check la date de publication
				if($this->plxRecord_arts->f('date') > $last_updated)
					$last_updated = $this->plxRecord_arts->f('date');
				# On affiche le résumé dans un buffer
				$entry_link .= "\t\t\t".'<rdf:li rdf:resource="'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'"/>'."\n";
				# On affiche le flux dans un buffer
				$entry .= '<item rdf:about="'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'">'."\n";
				$entry .= "\t".'<title>'.plxUtils::strCheck($this->plxRecord_arts->f('title')).'</title> '."\n";
				$entry .= "\t".'<link>'.$this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url')).'</link>'."\n";
				$entry .= "\t".'<dc:date>'.$this->plxRecord_arts->f('date').'</dc:date>'."\n";
				$entry .= "\t".'<dc:creator>'.plxUtils::strCheck($author).'</dc:creator>'."\n";
				$entry .= "\t".'<description>'.plxUtils::strCheck(plxUtils::rel2abs($this->racine,$content)).'</description>'."\n";
				$entry .= '</item>'."\n";
			}
		}
		
		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns="http://purl.org/rss/1.0/">'."\n";
		echo '<channel rdf:about="'.$link.'">'."\n";
		echo "\t".'<title>'.plxUtils::strCheck($title).'</title>'."\n";
		echo "\t".'<link>'.$link.'</link>'."\n";
		echo "\t".'<description>'.plxUtils::strCheck($this->aConf['description']).'</description>'."\n";
		echo "\t".'<lastBuildDate>'.$last_updated.'</lastBuildDate>'."\n";
		echo "\t".'<generator>PluXml '.$this->version.'</generator>'."\n";
		echo "\t".'<dc:language>fr</dc:language>'."\n";
		echo  "\t".'<items>'."\n";
		echo "\t\t".'<rdf:Seq>'."\n";
		echo $entry_link;
		echo "\t\t".'</rdf:Seq>'."\n";
		echo "\t".'</items>'."\n";
		echo '</channel>'."\n";
		echo $entry;
		echo '</rdf:RDF>';
	}

	/**
	 * Méthode qui affiche le flux atom des commentaires du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL
	 **/
	public function getAtomCommentaires() {

		# Traitement initial
		$last_updated = '';
		$entry = '';
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$title = $this->aConf['title'].' - '.$this->plxRecord_arts->f('title').' - Commentaires';
			$link = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url'));
			$link_feed = $this->urlRewrite('feed.php?atom/commentaires/article'.$artId);
		} else { # Commentaires globaux
			$title = $this->aConf['title'].' - Commentaires';
			$link = $this->urlRewrite();
			$link_feed = $this->urlRewrite('feed.php?atom/commentaires');
		}
		
		# On va boucler sur les commentaires (si il y'en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				$artId = $this->plxRecord_coms->f('article') + 0;
				if($this->cible) { # Commentaires d'un article
					$title_com = $this->plxRecord_arts->f('title').' - ';
					$title_com .= 'par '.$this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url').'/atom#c'.$this->plxRecord_coms->f('numero'));
				} else { # Commentaires globaux
					$title_com = $this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/#c'.$this->plxRecord_coms->f('numero'));
				}
				# On check la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated)
					$last_updated = $this->plxRecord_coms->f('date');
				# On affiche le flux dans un buffer
				$entry .= '<entry>'."\n";
				$entry .= "\t".'<title type="html">'.plxUtils::strCheck($title_com).'</title> '."\n";
				$entry .= "\t".'<link href="'.$link_com.'"/>'."\n";
				$entry .= "\t".'<id>urn:md5:'.md5($link_com).'</id>'."\n";
				$entry .= "\t".'<updated>'.$this->plxRecord_coms->f('date').'</updated>'."\n";
				$entry .= "\t".'<author><name>'.plxUtils::strCheck($this->plxRecord_coms->f('author')).'</name></author>'."\n";
				$entry .= "\t".'<content type="html">'.plxUtils::strCheck(strip_tags($this->plxRecord_coms->f('content'))).'</content>'."\n";
				$entry .= '</entry>'."\n";
			}
		}
		
		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		echo '<title type="html">'.plxUtils::strCheck($title).'</title>'."\n";
		echo '<subtitle type="html">'.plxUtils::strCheck($this->aConf['description']).'</subtitle>'."\n";
		echo '<link href="'.$link_feed.'" rel="self" type="application/atom+xml"/>'."\n";
		echo '<link href="'.$link.'" rel="alternate" type="text/html"/>'."\n";
		echo '<updated>'.$last_updated.'</updated>'."\n";
		echo '<id>urn:md5:'.md5($link).'</id>'."\n";
		echo '<generator uri="http://pluxml.org/">PluXml '.$this->version.'</generator>'."\n";
		echo $entry;
		echo '</feed>';
	}

	/**
	 * Méthode qui affiche le flux rss des commentaires du site
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL
	 **/
	public function getRssCommentaires() {

		# Traitement initial
		$last_updated = '';
		$entry_link = '';
		$entry = '';
		if($this->cible) { # Commentaires d'un article
			$artId = $this->plxRecord_arts->f('numero') + 0;
			$title = $this->aConf['title'].' - '.$this->plxRecord_arts->f('title').' - Commentaires';
			$link = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url'));
		} else { # Commentaires globaux
			$title = $this->aConf['title'].' - Commentaires';
			$link = $this->urlRewrite();
		}
		
		# On va boucler sur les commentaires (si il y'en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				# Traitement initial
				$artId = $this->plxRecord_coms->f('article') + 0;
				if($this->cible) { # Commentaires d'un article
					$title_com = $this->plxRecord_arts->f('title').' - ';
					$title_com .= 'par '.$this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/'.$this->plxRecord_arts->f('url').'/rss#c'.$this->plxRecord_coms->f('numero'));
				} else { # Commentaires globaux
					$title_com = $this->plxRecord_coms->f('author').' le ';
					$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
					$link_com = $this->urlRewrite('?article'.$artId.'/#c'.$this->plxRecord_coms->f('numero'));
				}
				# On check la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated)
					$last_updated = $this->plxRecord_coms->f('date');
				# On affiche le résumé dans un buffer
				$entry_link .= "\t\t\t".'<rdf:li rdf:resource="'.$link_com.'"/>'."\n";
				# On affiche le flux dans un buffer
				$entry .= '<item rdf:about="'.$link_com.'">'."\n";
				$entry .= "\t".'<title>'.plxUtils::strCheck($title_com).'</title> '."\n";
				$entry .= "\t".'<link>'.$link_com.'</link>'."\n";
				$entry .= "\t".'<dc:date>'.$this->plxRecord_coms->f('date').'</dc:date>'."\n";
				$entry .= "\t".'<dc:creator>'.plxUtils::strCheck($this->plxRecord_coms->f('author')).'</dc:creator>'."\n";
				$entry .= "\t".'<description>'.plxUtils::strCheck(strip_tags($this->plxRecord_coms->f('content'))).'</description>'."\n";
				$entry .= '</item>'."\n";
			}
		}
		
		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns="http://purl.org/rss/1.0/">'."\n";
		echo '<channel rdf:about="'.$link.'">'."\n";
		echo "\t".'<title>'.plxUtils::strCheck($title).'</title>'."\n";
		echo "\t".'<link>'.$link.'</link>'."\n";
		echo "\t".'<description>'.plxUtils::strCheck($this->aConf['description']).'</description>'."\n";
		echo "\t".'<lastBuildDate>'.$last_updated.'</lastBuildDate>'."\n";
		echo "\t".'<generator>PluXml '.$this->version.'</generator>'."\n";
		echo "\t".'<dc:language>fr</dc:language>'."\n";
		echo  "\t".'<items>'."\n";
		echo "\t\t".'<rdf:Seq>'."\n";
		echo $entry_link;
		echo "\t\t".'</rdf:Seq>'."\n";
		echo "\t".'</items>'."\n";
		echo '</channel>'."\n";
		echo $entry;
		echo '</rdf:RDF>';
	}

	/**
	 * Méthode qui affiche le flux atom des commentaires du site pour l'administration
	 *
	 * @return	flux sur stdout
	 * @author	Florent MONTHEL
	 **/
	public function getAdminCommentaires() {

		# Traitement initial
		$last_updated = '';
		$entry = '';
		if($this->cible == '_') { # Commentaires hors ligne
			$link = $this->racine.'core/admin/commentaires_offline.php?page=1';
			$title = $this->aConf['title'].' - Commentaires hors ligne';
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/hors-ligne';
		} else { # Commentaires en ligne
			$link = $this->racine.'core/admin/commentaires_online.php?page=1';
			$title = $this->aConf['title'].' - Commentaires en ligne';
			$link_feed = $this->racine.'feed.php?admin'.$this->clef.'/commentaires/en-ligne';
		}
		
		# On va boucler sur les commentaires (si il y'en a)
		if($this->plxRecord_coms) {
			while($this->plxRecord_coms->loop()) {
				$artId = $this->plxRecord_coms->f('article') + 0;
				$comId = $this->cible.$this->plxRecord_coms->f('article').'.'.$this->plxRecord_coms->f('numero');
				$title_com = $this->plxRecord_coms->f('author').' le ';
				$title_com .= plxDate::dateIsoToHum($this->plxRecord_coms->f('date'),'#day #num_day #month #num_year(4), #hour:#minute');
				$link_com = $this->racine.'core/admin/commentaire.php?c='.$comId;
				# On check la date de publication
				if($this->plxRecord_coms->f('date') > $last_updated)
					$last_updated = $this->plxRecord_coms->f('date');
				# On affiche le flux dans un buffer
				$entry .= '<entry>'."\n";
				$entry .= "\t".'<title type="html">'.plxUtils::strCheck($title_com).'</title> '."\n";
				$entry .= "\t".'<link href="'.$link_com.'"/>'."\n";
				$entry .= "\t".'<id>urn:md5:'.md5($link_com).'</id>'."\n";
				$entry .= "\t".'<updated>'.$this->plxRecord_coms->f('date').'</updated>'."\n";
				$entry .= "\t".'<author><name>'.plxUtils::strCheck($this->plxRecord_coms->f('author')).'</name></author>'."\n";
				$entry .= "\t".'<content type="html">'.plxUtils::strCheck($this->plxRecord_coms->f('content')).'</content>'."\n";
				$entry .= '</entry>'."\n";
			}
		}
		
		# On affiche le flux
		header('Content-Type: text/xml; charset='.PLX_CHARSET);
		echo '<?xml version="1.0" encoding="'.PLX_CHARSET.'" ?>'."\n";
		echo '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
		echo '<title type="html">'.plxUtils::strCheck($title).'</title>'."\n";
		echo '<subtitle type="html">'.plxUtils::strCheck($this->aConf['description']).'</subtitle>'."\n";
		echo '<link href="'.$link_feed.'" rel="self" type="application/atom+xml"/>'."\n";
		echo '<link href="'.$link.'" rel="alternate" type="text/html"/>'."\n";
		echo '<updated>'.$last_updated.'</updated>'."\n";
		echo '<id>urn:md5:'.md5($link).'</id>'."\n";
		echo '<generator uri="http://pluxml.org/">PluXml '.$this->version.'</generator>'."\n";
		echo $entry;
		echo '</feed>';
	}

}
?>