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

	private $plxAdmin; # objet plxAdmin

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
		echo "Mise &agrave; jour de la version ".$this->newVersion.' termin&eacute;e.<br />';
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
		krsort($this->updateList);
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
			if(floatval($num_version) > floatval($this->oldVersion) AND floatval($num_version) > floatval($version)) {
				echo '<p><strong>Applications des mises &agrave jour version '.floatval($num_version).'</strong></p>';
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
			echo '<p class="error">Une erreur s\'est produite pendant la mise &agrave; jour.</p>';
		else
			echo '<p class="msg">Toutes les mises &agrave; jour ont &eacute;t&eacute; appliqu&eacute;es avec succ&egrave;s !</p>';

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
}
?>