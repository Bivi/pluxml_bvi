<?php
/**
 * Classe plxEditor responsable de l'affichage de la plxToolbar
 *
 * @package PLX
 * @author	Stéphane F
 **/
class plxEditor {

	private $path_editor; # chemin vers le dossier de la plxToolbar

	/**
	 * Constructeur qui initialise la variable de classe
	 *
	 * @param	path_editor		chemin vers le dossier de la plxToolbar
	 * @return	null
	 * @author	Stephane F
	 **/
	public function __construct($path_editor) {
		$this->path_editor = $path_editor;
	}

	/**
	 * Méthode qui ajoute les déclarations nécessaires dans la partie <head> de la page top.php de l'administration
	 *
	 * @param	null
	 * @return	stdout
	 * @author	Stephane F
	 **/
	public function addHeader() {

		# ajoute la déclaration du fichier css et du fichier javascript de la plxToolbar
		echo "\n\t".'<link rel="stylesheet" type="text/css" href="'.$this->path_editor.'style.css" media="screen" />';
		echo "\n\t".'<script type="text/javascript" src="'.$this->path_editor.'plxtoolbar.js"></script>'."\n";

	}

	/**
	 * Méthode qui ajoute les déclarations nécessaires à la fin de la page foot.php de l'administration
	 *
	 * @param	null
	 * @return	stdout
	 * @author	Stephane F
	 **/
	public function addFooter() {

		# On regarde s'il y a des boutons personnels à ajouter dans la plxtoolbar
		if(is_dir(PLX_ROOT.'addons/plxtoolbar.buttons/')) {
			$buttons = plxGlob::getInstance(PLX_ROOT.'addons/plxtoolbar.buttons');
			if($aFiles = $buttons->query('/button.(.*).php$/')) {
				foreach($aFiles as $button) {
					include(PLX_ROOT.'addons/plxtoolbar.buttons/'.$button);
				}
			}
		}
		# Initialisation de la plxToolbar
		echo "\n".'<script type="text/javascript">plxToolbar.init();</script>'."\n";
	}

}
?>