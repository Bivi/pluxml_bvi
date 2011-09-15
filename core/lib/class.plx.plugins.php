<?php
/**
 * Classe plxPlugins responsable de la gestion des plugins
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxPlugins {

	public $aHooks=array(); # tableau de tous les hooks des plugins � executer
	public $filename=''; # chemin pour acc�der au fichier de configuration plugins.xml
	public $aPlugins=array(); #tableau contenant les plugins

	private $default_lang; # langue par defaut utilis�e par PluXml

	/**
	 * Constructeur de la classe plxPlugins
	 *
	 * @param	filename		emplacement du fichier XML de configuration plugins.xml
	 * @param	default_lang	langue par d�faut utilis�e par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($filename, $default_lang='') {
		$this->filename=$filename;
		$this->default_lang=$default_lang;
		$this->loadConfig();
	}

	/**
	 * M�thode qui renvoit une instance d'un plugin
	 *
	 * @param	plugName	nom du plugin
	 * @return	object		object de type plxPlugin / false en cas d'erreur
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getInstance($plugName) {
		$filename = PLX_PLUGINS.$plugName.'/'.$plugName.'.php';
		if(is_file($filename)) {
			include_once($filename);
			if (class_exists($plugName)) {
				return new $plugName($this->default_lang);
			}
		}
		return false;
	}

	/**
	 * M�thode qui recupere la liste des plugins dans le dossier plugins
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getList() {

		$dirs = plxGlob::getInstance(PLX_PLUGINS, true);
		if(sizeof($dirs->aFiles)>0) {
			foreach($dirs->aFiles as $plugName) {
				if(!isset($this->aPlugins[$plugName]) OR !is_object($this->aPlugins[$plugName]['instance'])) {
					if($instance=$this->getInstance($plugName)) {
						$this->aPlugins[$plugName]['instance'] = $instance;
						$this->aPlugins[$plugName]['instance']->getInfos();
						$activate=(isset($this->aPlugins[$plugName]['activate']))?$this->aPlugins[$plugName]['activate']:0;
						$this->aPlugins[$plugName]['activate'] = $activate;
					}
				} else {
					$this->aPlugins[$plugName]['instance']->getInfos();
				}
			}
		}
	}

	/**
	 * M�thode qui charge le fichier plugins.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadConfig() {

		if(!is_file($this->filename)) return;
		# Mise en place du parseur XML
		$data = implode('',file($this->filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "plugin"
		if(isset($iTags['plugin'])) {
			# On compte le nombre de tags "plugin"
			$nb = sizeof($iTags['plugin']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				$name = $values[$iTags['plugin'][$i] ]['attributes']['name'];
				$activate = $values[$iTags['plugin'][$i] ]['attributes']['activate'];
				$value = isset($values[$iTags['plugin'][$i]]['value']) ? $values[$iTags['plugin'][$i]]['value'] : '';
				$this->aPlugins[$name] = array(
					'activate' 	=> $activate,
					'title'		=> $value,
					'instance'	=> null,
				);
			}
		}
	}

	/**
	 * M�thode qui v�rifie les pre-requis d'un plugin
	 *
	 * @param	requirements	chaine contenant les pre-requis d'un plugin
	 * @return	boolean			resultat du control / TRUE = ok
	 * @author	Stephane F
	 **/
	public function checkRequirements($requirements) {
		if(trim($requirements)!='') {
			$list=explode(',',$requirements);
			# on verifie que pour chaque pr�-requis le plugin correspondant est actif
			foreach($list as $requirement) {
				$r = trim($requirement);
				if(!isset($this->aPlugins[$r]['activate']) OR $this->aPlugins[$r]['activate']==0)
					return false;
			}
		}
		return true;
	}

	/**
	 * M�thode qui sauvegarde le fichier plugins.xml
	 *
	 * @param	content		array content $_POST
	 * @return	boolean		resultat de la sauvegarde / TRUE = ok
	 * @author	Stephane F
	 **/
	public function saveConfig($content) {

		foreach($content['plugName'] as $plugName => $activate) {
			if(isset($content['action'][$plugName])) {
				if($content['action'][$plugName]=='on') {
					if($instance = $this->getInstance($plugName)) {
						if($content['selection']=='activate') {
							# recuperation des infos dans le fichiers infos.xml du plugin
							//$instance->getInfos();
							# si toutes les conditions sont ok pour activer le plugin
							//if($this->checkRequirements($instance->getInfo('requirements'))) {
								$this->aPlugins[$plugName]['activate']=1;
								$this->aPlugins[$plugName]['title']=$content['plugTitle'][$plugName];
								if(method_exists($plugName, 'OnActivate')) $instance->OnActivate();
							//}
						}
						elseif($content['selection']=='deactivate') {
							$this->aPlugins[$plugName]['activate']=0;
							$this->aPlugins[$plugName]['title']='';
							if(method_exists($plugName, 'OnDeactivate')) $instance->OnDeactivate();
						}
					}
				}
			}
		}

		# D�but du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($this->aPlugins as $k=>$v) {
			$title=isset($v['title'])?$v['title']:'';
			$xml .= "\t<plugin name=\"$k\" activate=\"".intval($v['activate'])."\"><![CDATA[".plxUtils::cdataCheck($title)."]]></plugin>\n";
		}
		$xml .= "</document>";

		# On �crit le fichier
		if(plxUtils::write($xml,$this->filename))
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		else
			return plxMsg::Error(L_SAVE_ERR.' '.$this->filename);

	}

	/**
	 * M�thode qui charge les plugins en cr�ant une instance plxPlugin des plugins
	 * et r�cupere les hooks des plugins
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadPlugins() {

		foreach($this->aPlugins as $plugName=>$plugAttrs) {
			if($plugAttrs['activate']) {
				if($instance=$this->getInstance($plugName)) {
					$this->aPlugins[$plugName]['instance'] = $instance;
					$this->aHooks = array_merge_recursive($this->aHooks, $this->aPlugins[$plugName]['instance']->getHooks());
				}
			}
		}
	}

	/**
	 * M�thode qui execute les hooks des plugins
	 *
	 * @param	hookname	nom du hook � appliquer
	 * @return	null
	 * @author	Stephane F
	 **/
	public function callHook($hookName) {

		if(isset($this->aHooks[$hookName])) {
			ob_start();
			foreach($this->aHooks[$hookName] as $callback) {
				 $this->aPlugins[$callback['class']]['instance']->$callback['method']();
			}
			return '?>'.ob_get_clean().'<?php ';
		}
	}

	/**
	 * M�thode r�cursive qui supprime tous les dossiers et les fichiers d'un r�pertoire
	 *
	 * @param	deldir	r�pertoire de suppression
	 * @return	boolean	r�sultat de la suppression
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) { #fonction r�cursive

		if(is_dir($deldir) AND !is_link($deldir)) {
			if($dh = @opendir($deldir)) {
				while(FALSE !== ($file = readdir($dh))) {
					if($file != '.' AND $file != '..') {
						$this->deleteDir(($deldir!='' ? $deldir.'/' : '').$file);
					}
				}
				closedir($dh);
			}
			return rmdir($deldir);
		}
		return unlink($deldir);
	}

}

/**
 * Classe plxPlugin destin� � cr�er un plugin
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxPlugin {

	protected $aInfos=array();  # tableau des infos sur le plugins venant du fichier infos.xml
	protected $aParams=array(); # tableau des param�tres sur le plugins venant du fichier parameters.xml
	protected $aHooks=array(); # tableau des hooks du plugin
	protected $aLang=array(); # tableau contenant les cl�s de traduction de la langue courant de PluXml

	protected $plug=array(); # tableau contenant des infos diverses pour el fonctionnement du plugin
	protected $adminProfil=''; # profil(s) utilisateur(s) autoris�(s) � acceder � la page admin.php du plugin
	protected $configProfil=''; # profil(s) utilisateur(s) autoris�(s) � acceder � la page config.php du plugin
	protected $default_lang=DEFAULT_LANG; # langue par defaut de PluXml

	/**
	 * Constructeur de la classe plxPlugin
	 *
	 * @param	default_lang	langue par d�faut utilis�e par PluXml
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($default_lang='') {
		$this->default_lang = $default_lang;
		$plugName= get_class($this);
		$this->plug = array(
			'dir' 			=> PLX_PLUGINS,
			'name' 			=> $plugName,
			'filename'		=> PLX_PLUGINS.$plugName.'/'.$plugName.'.php',
			'parameters.xml'=> PLX_PLUGINS.$plugName.'/parameters.xml',
			'infos.xml'		=> PLX_PLUGINS.$plugName.'/infos.xml'
		);
		$this->loadLang(PLX_PLUGINS.$plugName.'/lang/'.$this->default_lang.'.php');
		$this->loadParams();
	}

	/**
	 * M�thode qui renvoit le(s) profil(s) utilisateur(s) autoris�(s) � acceder � la page admin.php du plugin
	 *
	 * @return	string		profil(s) utilisateur(s)
	 * @author	Stephane F
	 **/
	public function getAdminProfil() {
		return $this->adminProfil;
	}

	/**
	 * M�thode qui m�morise le(s) profil(s) utilisateur(s) autoris�(s) � acceder � la page admin.php du plugin
	 *
	 * @param	profil		profil(s) (PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR, PROFIL_WRITER)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setAdminProfil($profil) {
		$this->adminProfil=func_get_args();
	}

	/**
	 * M�thode qui renvoit le(s) profil(s) utilisateur(s) autoris�(s) � acceder � la page config.php du plugin
	 *
	 * @return	string		profil(s) utilisateur(s)
	 * @author	Stephane F
	 **/
	public function getConfigProfil() {
		return $this->configProfil;
	}

	/**
	 * M�thode qui m�morise le(s) profil(s) utilisateur(s) autoris�(s) � acceder � la page config.php du plugin
	 *
	 * @param	profil		profil(s) (PROFIL_ADMIN, PROFIL_MANAGER, PROFIL_MODERATOR, PROFIL_EDITOR, PROFIL_WRITER)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setConfigProfil($profil) {
		$this->configProfil=func_get_args();
	}

	/**
	 * M�thode qui retourne les hooks d�finis dans le plugin
	 *
	 * @return	array		tableau des hooks du plugin
	 * @author	Stephane F
	 **/
	public function getHooks() {
		return $this->aHooks;
	}

	/**
	 * M�thode qui charge le fichier de langue par d�faut du plugin
	 *
	 * @param	filename	fichier de langue � charger
	 * @return	null
	 * @author	Stephane F
	 **/
	private function loadLang($filename) {
		if(!is_file($filename)) return;
		include($filename);
		$this->aLang=$LANG;
	}

	/**
	 * M�thode qui affiche une cl� de traduction dans la langue par d�faut de PluXml
	 *
	 * @param	key		cl� de traduction � r�cuperer
	 * @return	stdio
	 * @author	Stephane F
	 **/
	public function lang($key='') {
		if(isset($this->aLang[$key]))
			echo $this->aLang[$key];
		else
			echo $key;
	}

	/**
	 * M�thode qui retourne une cl� de traduction dans la langue par d�faut de PluXml
	 *
	 * @param	key		cl� de traduction � r�cuperer
	 * @return	string	cl� de traduite
	 * @author	Stephane F
	 **/
	public function getLang($key='') {
		if(isset($this->aLang[$key]))
			return $this->aLang[$key];
		else
			return $key;
	}

	/**
	 * M�thode qui charge le fichier des parametres du plugin parameters.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function loadParams() {

		if(!is_file($this->plug['parameters.xml'])) return;

		# Mise en place du parseur XML
		$data = implode('',file($this->plug['parameters.xml']));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		# On verifie qu'il existe des tags "parameter"
		if(isset($iTags['parameter'])) {
			# On compte le nombre de tags "parameter"
			$nb = sizeof($iTags['parameter']);
			# On boucle sur $nb
			for($i = 0; $i < $nb; $i++) {
				if(isset($values[$iTags['parameter'][$i]]['attributes']['name'])) {
					$name=$values[$iTags['parameter'][$i]]['attributes']['name'];
					$type=isset($values[$iTags['parameter'][$i]]['attributes']['type'])?$values[$iTags['parameter'][$i]]['attributes']['type']:'numeric';
					$value=isset($values[$iTags['parameter'][$i]]['value'])?$value=$values[$iTags['parameter'][$i]]['value']:'';
					$this->aParams[$name] = array(
						'type'	=> $type,
						'value'	=> $value
					);
				}
			}
		}
	}

	/**
	 * M�thode qui sauvegarde le fichier des parametres du plugin parameters.xml
	 *
	 * @return	boolean		resultat de la sauvegarde / TRUE = ok
	 * @author	Stephane F
	 **/
	public function saveParams() {

		# D�but du fichier XML
		$xml = "<?xml version='1.0' encoding='".PLX_CHARSET."'?>\n";
		$xml .= "<document>\n";
		foreach($this->aParams as $k=>$v) {
			switch($v['type']) {
				case 'numeric':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\">".intval($v['value'])."</parameter>\n";
					break;
				case 'string':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\">".plxUtils::cdataCheck(plxUtils::strCheck($v['value']))."</parameter>\n";
					break;
				case 'cdata':
					$xml .= "\t<parameter name=\"$k\" type=\"".$v['type']."\"><![CDATA[".plxUtils::cdataCheck($v['value'])."]]></parameter>\n";
					break;
			}
		}
		$xml .= "</document>";

		# On �crit le fichier
		if(plxUtils::write($xml,$this->plug['parameters.xml']))
			return plxMsg::Info(L_SAVE_SUCCESSFUL);
		else
			return plxMsg::Error(L_SAVE_ERR.' '.$this->plug['parameters.xml']);
	}

	/**
	 * M�thode qui renvoie le tableau des param�tres
	 *
	 * @return	array		tableau aParams
	 * @author	Stephane F
	 **/
	public function getParams() {
		if(sizeof($this->aParams)>0)
			return $this->aParams;
		else
			return false;
	}

	/**
	 * M�thode qui renvoie la valeur d'un parametre du fichier parameters.xml
	 *
	 * @param	param	nom du parametre � recuperer
	 * @return	string	valeur du parametre
	 * @author	Stephane F
	 **/
	public function getParam($param) {
		return (isset($this->aParams[$param])? $this->aParams[$param]['value']:'');
	}

	/**
	 * M�thode qui modifie la valeur d'un parametre du fichier parameters.xml
	 *
	 * @param	param	nom du parametre � recuperer
	 * @param	value	valeur du parametre
	 * @type	type 	type du parametre (numeric, string, cdata)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function setParam($param, $value,$type='') {

		if(in_array($type,array('numeric','string','cdata')))
			$this->aParams[$param]['type']=$type;

		if($this->aParams[$param]['type']=='numeric')
				$this->aParams[$param]['value']=intval($value);
			else
				$this->aParams[$param]['value']=$value;
	}

	/**
	 * M�thode qui recupere les donn�es du fichier infos.xml
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function getInfos() {

		if(!is_file($this->plug['infos.xml'])) return;

		# Mise en place du parseur XML
		$data = implode('',file($this->plug['infos.xml']));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		$this->aInfos = array(
			'title'			=> (isset($iTags['title']) AND isset($values[$iTags['title'][0]]['value']))?$values[$iTags['title'][0]]['value']:'',
			'author'		=> (isset($iTags['author']) AND isset($values[$iTags['author'][0]]['value']))?$values[$iTags['author'][0]]['value']:'',
			'version'		=> (isset($iTags['version']) AND isset($values[$iTags['version'][0]]['value']))?$values[$iTags['version'][0]]['value']:'',
			'date'			=> (isset($iTags['date']) AND isset($values[$iTags['date'][0]]['value']))?$values[$iTags['date'][0]]['value']:'',
			'site'			=> (isset($iTags['date']) AND isset($values[$iTags['site'][0]]['value']))?$values[$iTags['site'][0]]['value']:'',
			'description'	=> (isset($iTags['description']) AND isset($values[$iTags['description'][0]]['value']))?$values[$iTags['description'][0]]['value']:'',
			'requirements'	=> (isset($iTags['requirements']) AND isset($values[$iTags['requirements'][0]]['value']))?$values[$iTags['requirements'][0]]['value']:'',
			);

	}

	/**
	 * M�thode qui renvoie la valeur d'un parametre du fichier infos.xml
	 *
	 * @param	param	nom du parametre � recuperer
	 * @return	string	valeur de l'info
	 * @author	Stephane F
	 **/
	public function getInfo($param) {
		return (isset($this->aInfos[$param])?$this->aInfos[$param]:'');
	}

	/**
	 * M�thode qui ajoute un hook � executer
	 *
	 * @param	hookname		nom du hook
	 * @param	userfunction	nom de la fonction du plugin � executer
	 * @return	null
	 * @author	Stephane F
	 **/
	public function addHook($hookname, $userfunction) {
		if(method_exists(get_class($this), $userfunction)) {
			$this->aHooks[$hookname][]=array(
				'class'		=> get_class($this),
				'method'	=> $userfunction
			);
		}
	}

}
?>