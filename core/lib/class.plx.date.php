<?php

/**
 * Classe plxDate rassemblant les fonctions utiles � PluXml
 * concernant la manipulation des dates
 *
 * @package PLX
 * @author	Stephane F
 **/
class plxDate {

	/**
	 * M�thode qui retourne le timestamp UNIX actuel avec les microsecondes
	 *
	 * @return	timestamp	valeur du timestamp actuel
	 **/
	public static function microtime() {

		$t = explode(' ',microtime());
		return $t[0]+$t[1];
	}

	/**
	 * M�thode qui retourne le libell� du mois ou du jour pass� en param�tre
	 *
	 * @param	key		constante: 'day' ou 'month'
	 * @param	value	numero du mois ou du jour
	 * @return	string	libell� du mois ou du jour
	 **/
	public static function getCalendar($key, $value) {

		$aMonth = array(
			'01' => 'janvier',
			'02' => 'f&eacute;vrier',
			'03' => 'mars',
			'04' => 'avril',
			'05' => 'mai',
			'06' => 'juin',
			'07' => 'juillet',
			'08' => 'ao&ucirc;t',
			'09' => 'septembre',
			'10' => 'octobre',
			'11' => 'novembre',
			'12' => 'd&eacute;cembre');	
		$aDay = array(
			'1' => 'lundi',
			'2' => 'mardi',
			'3' => 'mercredi',
			'4' => 'jeudi',
			'5' => 'vendredi',
			'6' => 'samedi',
			'0' => 'dimanche');
	
		switch ($key) {
			case 'day':
				return $aDay[ $value ]; break;
			case 'month':
				return $aMonth[ $value ]; break;
		}
	}

	/**
	 * M�thode qui convertit une date au format ISO en tenant compte d'un d�calage horaire
	 *
	 * @param	date		date sous forme de chaine de caract�res
	 * @return	date		date au format ISO
	 **/
	public static function dateToIso($date,$delta) {

		return substr($date,0,4).'-'.
		substr($date,4,2).'-'.
		substr($date,6,2).'T'.
		substr($date,8,2).':'.
		substr($date,10,2).':00'.$delta;
	}

	/**
	 * M�thode qui retourne un timestamp au format ISO en tenant compte d'un d�calage horaire
	 *
	 * @param	timestamp	timestamp
	 * @return	date		date au format ISO
	 **/
	public static function timestampToIso($timestamp,$delta) {

		return @date('Y-m-d\TH:i:s',$timestamp).$delta;
	}

	/**
	 * M�thode qui convertit une date ISO au format humain en tenant compte du formatage pass� en param�tre
	 *
	 * @param	date		date au format AAAAMMJJ
	 * @param	format		format de la date de sortie (variable: #minute,#hour,#day,#month,#num_day,#num_month,#num_year(2),#num_year(4))
	 * @return	date		date format�e au format humain 
	 **/
	public static function dateIsoToHum($date, $format='#day #num_day #month #num_year(4)') {

		# On decoupe notre date
		$year4 = substr($date, 0, 4);
		$year2 = substr($date, 2, 2);
		$month = substr($date, 5, 2);
		$day = substr($date, 8, 2);
		$day_num = @date('w',@mktime(0,0,0,$month,$day,$year4));
		$hour = substr($date,11,2);
		$minute = substr($date,14,2);

		# On retourne notre date au format humain
		$format = str_replace('#minute', $minute, $format);
		$format = str_replace('#hour', $hour, $format);
		$format = str_replace('#day', plxDate::getCalendar('day', $day_num), $format);
		$format = str_replace('#month', plxDate::getCalendar('month', $month), $format);
		$format = str_replace('#num_day', $day, $format);
		$format = str_replace('#num_month', $month, $format);
		$format = str_replace('#num_year(2)', $year2 , $format);
		$format = str_replace('#num_year(4)', $year4 , $format);
		return $format;
	}

	/**
	 * M�thode qui retourne l'heure au format humain contenue dans une date au format ISO
	 *
	 * @param	date		date au format ISO
	 * @return	heure		heure format�e HH:MM
	 **/
	public static function heureIsoToHum($date) {

		# On retourne l'heure au format 12:55
		return substr($date,11,2).':'.substr($date,14,2);
	}
	
	/**
	 * M�thode qui d�coupe une date ISO dans un tableau (element du tableau: year, month, day, time, delta)
	 *
	 * @param	date		date au format ISO
	 * @return	array		tableau contenant les diff�rentes parties de la date
	 **/
	public static function dateIso2Admin($date) {

		preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9:]{8})((\+|-)[0-9:]{5})/',$date,$capture);
		return array ('year' => $capture[1],'month' => $capture[2],'day' => $capture[3],'time' => substr($capture[4],0,5),'delta' => $capture[5]);
	}	
	
	/**
	 * M�thode qui v�rifie la validit� de la date et de l'heure
	 *
	 * @param	int		mois
	 * @param	int		jour
	 * @param	int		ann�e
	 * @param	int		heure:minute
	 * @return	boolean	vrai si la date est valide
	 **/
	public static function checkDate($day, $month, $year, $time) {
		
		return (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])[0-9]{4}([0-1][0-9]|2[0-3])\:[0-5][0-9]$/",$day.$month.$year.$time)
			AND checkdate($month, $day, $year));

	}
}
?>