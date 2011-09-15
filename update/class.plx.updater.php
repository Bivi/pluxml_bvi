<?php

/**
 * Classe plxUpdater responsable du gestionnaire des mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/

define('PLX_UPDATE', PLX_ROOT.'update/');

class plxUpdater {

	public $newVersion = '';
	public $oldVersion = '' ;
	public $updateList = array(); # liste des mises à jour disponibles

	public $plxAdmin; # objet plxAdmin

	/**
	 * Constructeur de la classe plxUpdater
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct() {

		$this->plxAdmin = new plxAdmin(PLX_CONF);

		$this->checkVersions();
		$this->getUpdateList();
	}

	/**
	 * Méthode qui chargé de démarrer les mises à jour
	 *
	 * @param	version		précédente version de pluxml à mettre à jour, sélectionner par l'utilisateur
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function start($version='') {
		if($this->update($version))
			$this->updateVersion();
	}

	/**
	 * Méthode qui recherche les n° de version de pluxml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function checkVersions() {

		# Rencherche ancien n° de version de Pluxml à mettre à jour
		if(isset($this->plxAdmin->aConf['version']))
			$this->oldVersion = $this->plxAdmin->aConf['version'];

		# Nouvelle version de PluXml
		if(is_readable(PLX_ROOT.'version')) {
			$f = file(PLX_ROOT.'version');
			$this->newVersion = $f['0'];
		}
	}

	/**
	 * Méthode qui met à jour le n° de version dans le fichier parametres.xml
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function updateVersion() {

		$new_params['version'] = $this->newVersion;
		$this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params);
		printf(L_UPDATE_ENDED.'<br />', $this->newVersion);
	}

	/**
	 * Méthode qui récupère la liste des mises à jour disponibles
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function getUpdateList() {

		$plxUpdates = plxGlob::getInstance(PLX_UPDATE);
		if($updates = $plxUpdates->query('/^update_[0-9\-\.]+.php$/')) {
			foreach($updates as $filename) {
				preg_match('/^(update_([0-9\-\.]+)).php$/', $filename, $capture);
				$this->updateList[$capture[2]] = array(
					'filename' 		=> $filename,
					'class_name'	=> str_replace('.', '_', $capture[1])
				);
			}
		}
		ksort($this->updateList);

	}

	/**
	 * Méthode qui execute les mises à jour étape par étape à partir des fichiers disponibles
	 *
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function update($version='') {

		$errors = false;
		foreach($this->updateList as $num_version => $update) {

			if(($this->oldVersion!='' AND version_compare($num_version,$this->oldVersion,'>')>0) OR ($version!='' AND version_compare($num_version,$version,'>')>0)) {

				echo '<p><strong>'.L_UPDATE_INPROGRESS.' '.$num_version.'</strong></p>';
				# inclusion du fichier de mise à jour
				include(PLX_UPDATE.$update['filename']);
				# création d'un instance de l'objet de mise à jour
				$class_update = new $update['class_name']($this->plxAdmin); # création d'une instance de l'objet de mise à jour
				# appel des différentes étapes de mise à jour
				$next = true;
				$step = 1;
				while($next AND !$errors) {
					$method_name = 'step'.$step;
					if(method_exists($update['class_name'], $method_name)) {
						if(!$class_update->$method_name($this->plxAdmin)) {
							$errors = true; # erreur déctectée
						} else {
							$step++; # étape suivante
							# on recharge l'objet plxAdmin pour prendre en compte les éventuels modifs de la mise à jour
							$this->plxAdmin = new plxAdmin(PLX_CONF);
						}
					}
					else $next = false;
				}
				echo '<br />';
			}
		}

		if($errors)
			echo '<p class="error">'.L_UPDATE_ERROR.'</p>';
		else
			echo '<p class="msg">'.L_UPDATE_SUCCESSFUL.'</p>';

		return !$errors;
	}

}

/**
 * Classe plxUpdate responsable d'executer des actions de mises à jour
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxUpdate {

	protected $plxAdmin; # objet de type plxAdmin

	/**
	 * Constructeur qui initialise l'objet plxAdmin par référence
	 *
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($plxAdmin) {
		$this->plxAdmin = &$plxAdmin;
	}

	/**
	 * Méthode qui met à jour le fichier parametre.xml en important les nouveaux paramètres
	 *
	 * @param	new_params		tableau contenant la liste des nouveaux paramètres avec leur valeur par défaut.
	 * @return	null
	 * @author	Stéphane F
	 **/
	public function updateParameters($new_params) {

		$update_require = false;

		foreach($new_params as $k => $v)	{
			if(!isset($this->plxAdmin->aConf[$k]))
				$update_require = true;
			else
				$new_params[$k] = $this->plxAdmin->aConf[$k];
		}
		if($update_require) {
			return $this->plxAdmin->editConfiguration($this->plxAdmin->aConf, $new_params).'<br />';
		}

	}

	/**
	 * Méthode récursive qui supprimes tous les dossiers et les fichiers d'un répertoire
	 *
	 * @param	deldir	répertoire de suppression
	 * @return	boolean	résultat de la suppression
	 * @author	Stephane F
	 **/
	public function deleteDir($deldir) { #fonction récursive

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