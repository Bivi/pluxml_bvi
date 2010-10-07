<?php

/**
 * Classe plxCapcha responsable du traitement antispam
 *
 * @package PLX
 * @author	Anthony GUÉRIN, Stéphane F
 **/
class plxCapcha {

	public $gds = false; # Grain de sel du hachage

	private $min = false; # Longueur min du mot
	private $max = false; # Longueur max du mot
	private $word = false; # Mot du capcha
	private $num = false; # Numero de la lettre selectionne
	private $numletter = false; # Traduction du numero de la lettre

	/**
	 * Constructeur qui initialise les variables de classe
	 *
	 * @return	null
	 * @author	Anthony GUÉRIN
	 **/
	public function __construct() {

		# Initialisation des variables de classe
		$this->min = 4;
		$this->max = 6;
		$this->gds = 'f5z9Rez6EZ';
		$this->word = $this->createWord();
		$this->num = $this->chooseNum();
		$this->numletter = $this->num2letter();
	}

	/**
	 * Méthode qui génère un mot
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN
	 **/
	public function createWord() {

		# On genere une taille compris entre min et max
		$size = mt_rand($this->min,$this->max);
		# Definition de l'alphabet
		$alphabet = 'abcdefghijklmnopqrstuvwxyz';
		$size_a = strlen($alphabet);
		# On genere un tableau word
		for($i = 0; $i < $size; $i++)
			$word[ $i ] = $alphabet[ mt_rand(0,$size_a-1) ];
		# On serialise le tableau et on retourne la valeur
		return implode('',$word);
	}

	/**
	 * Méthode qui choisit un numéro de lettre dans le mot chois
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function chooseNum() {

		# On choisit un numero entre 1 et la taille du mot
		return mt_rand(1,strlen($this->word));
	}

	/**
	 * Méthode qui convertit le numéro en chaîne de caractère
	 *
	 * @return	int
	 * @author	Anthony GUÉRIN
	 **/
	public function num2letter() {

		# Num = derniere lettre du mot
		if($this->num == strlen($this->word))
			return 'derni&egrave;re';
		# On genere un tableau associatif
		$array = array(
			'1' => 'premi&egrave;re',
			'2' => 'deuxi&egrave;me',
			'3' => 'troisi&egrave;me',
			'4' => 'quatri&egrave;me',
			'5' => 'cinqui&egrave;me',
			'6' => 'sizi&egrave;me',
			'7' => 'septi&egrave;me',
			'8' => 'huiti&egrave;me',
			'9' => 'neuvi&egrave;me',
			'10' => 'dixi&egrave;me');
		# La valeur existe dans le tableau
		if(isset($array[ $this->num ]))
			return $array[ $this->num ];
		else # Sinon on retourne une valeur generique
			return $this->num.'.&egrave;me';
	}

	/**
	 * Méthode qui génère la question du capcha
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN, Stéphane F
	 **/
	public function q() {

		# Generation de la question capcha
		return 'Quelle est la <span class="capcha-letter">'.$this->numletter.'</span> lettre du mot <span class="capcha-word">'.$this->word.'</span> ?';
	}

	/**
	 * Méthode qui retourne la réponse du capcha (grain de sel + md5)
	 *
	 * @return	string
	 * @author	Anthony GUÉRIN
	 **/
	public function r() {

		# Generation du hash de la reponse
		return md5($this->gds.$this->word[ $this->num-1 ]);
	}

}
?>