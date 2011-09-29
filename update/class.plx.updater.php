<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises � jour
 *
 * @package PLX
 * @author	Stephane F
 **/

define('PLX_UPDATE', PLX_ROOT.'update/');

class plxUpdater {

	public $newVersion = '';
	public $oldVersion = '' ;
	public $allVersions = null;

	public $plxAdmin; # objet plxAdmin

	/**
	 * Constructeur de la classe plxUpdater
	 *
	 * @param	versions	array	liste des versions + script de mise � jour (fichier versions.php)
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($versions) {
		$this->allVersions = $versions;
		$this->plxAdmin = plxAdmin::getInstance();
		$this->getVersions();
	}

	/**
	 * M�thode charg�e de d�marrer les mises � jour
	 *
	 * @param	version		pr�c�dente version de pluxml � mettre � jour, s�lectionner par l'utilisateur
	 * @return	null
	 * @author	St�phane F
	 **/
	public function startUpdate($version='') {

		# suppression des versions qui ont d�j� �t� mises � jour
		$offset = array_search($version, array_keys($this->allVersions));
		if($offset!='') {
			$this->allVersions= array_slice($this->allVersions, $offset, null, true);
		}

		# d�marrage des mises � jour
		if($this->doUpdate())
			$this->updateVersion();
	}

	/**
	 * M�thode qui r�cup�re l'ancien et le nouveau n� de version de pluxml
	 *
	 * @return	null
	 * @author	St�phane F
	 **/
	public function getVersions() {

		# R�cup�re l'ancien n� de version de Pluxml
		if(isset($this->plxAdmin->aConf['version']))
			$this->oldVersion = $this->plxAdmin->aConf['version'];
		if(!isset($this->allVersions[$this->oldVersion]))
			$this->oldVersion='';

		# R�cup�re le nouveau n� de version de PluXml
		if(is_readable(PLX_ROOT.'version')) {
			$f = file(PLX_ROOT.'version');
			$this->newVersion = $f['0'];
		}
	}

	/**
	 * M�thode qui met � jour le n� de version dans le fichier parametres.xml
	 *
	 * @return	null
	 * @author	St�phane F
	 **/
	public function updateVersion() {

		$new_params['version'] = $this->newVersion;
		$this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		printf(L_UPDATE_ENDED.'<br />', $this->newVersion);
	}

	/**
	 * M�thode qui execute les mises � jour �tape par �tape
	 *
	 * @return	stdout
	 * @author	St�phane F
	 **/
	public function doUpdate() {

		$errors = false;
		foreach($this->allVersions as $num_version => $upd_filename) {

			if($upd_filename!='') {

				echo '<p><strong>'.L_UPDATE_INPROGRESS.' '.$num_version.'</strong></p>';
				# inclusion du fichier de mise � jour
				include(PLX_UPDATE.$upd_filename);

				# cr�ation d'un instance de l'objet de mise � jour
				$class_name = 'update_'.str_replace('.', '_', $num_version);
				$class_update = new $class_name($this->plxAdmin);

				# appel des diff�rentes �tapes de mise � jour
				$next = true;
				$step = 1;
				while($next AND !$errors) {
					$method_name = 'step'.$step;
					if(method_exists($class_name, $method_name)) {
						if(!$class_update->$method_name($this->plxAdmin)) {
							$errors = true; # erreur d�tect�e
						} else {
							$step++; # �tape suivante
							# on recharge l'objet plxAdmin pour prendre en compte les �ventuels modifs de la mise � jour
							$this->plxAdmin = plxAdmin::getInstance();
						}
					}
					else $next = false;
				}
				echo '<br />';
			}

		}
		echo '<br />';

		if($errors)
			echo '<p class="error">'.L_UPDATE_ERROR.'</p>';
		else
			echo '<p class="msg">'.L_UPDATE_SUCCESSFUL.'</p>';

		return !$errors;
	}

}

/**
 * Classe plxUpdate responsable d'executer des actions de mises � jour
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxUpdate {

	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par r�f�rence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($plxAdmin) {
		$this->plxAdmin = &$plxAdmin;
	}

	/**
	 * M�thode qui met � jour le fichier parametre.xml en important les nouveaux param�tres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux param�tres avec leur valeur par d�faut.
	 * @return	stdio
	 * @author	St�phane F
	 **/
	public function updateParameters($new_params) {

		# enregistrement des nouveaux param�tes
		$ret = $this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		# on recharge le fichier de config
		$this->plxAdmin->getConfiguration(PLX_CONF);
		# valeur de retour
		return $ret.'<br />';
	}

	/**
	 * M�thode r�cursive qui supprimes tous les dossiers et les fichiers d'un r�pertoire
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
			return @rmdir($deldir);
		}
		return @unlink($deldir);
	}
}
?>