<?php

/**
 * Classe plxDate rassemblant les fonctions utiles à PluXml
 * concernant la manipulation des dates
 *
 * @package PLX
 * @author	Stephane F
 **/

class plxDate {

	/**
	 * Méthode qui retourne le libellé du mois ou du jour passé en paramètre
	 *
	 * @param	key		constante: 'day' ou 'month'
	 * @param	value	numero du mois ou du jour
	 * @return	string	libellé du mois ou du jour
	 **/
	public static function getCalendar($key, $value) {

		$aMonth = array(
			'01' => L_JANUARY,
			'02' => L_FEBRUARY,
			'03' => L_MARCH,
			'04' => L_APRIL,
			'05' => L_MAY,
			'06' => L_JUNE,
			'07' => L_JULY,
			'08' => L_AUGUST,
			'09' => L_SEPTEMBER,
			'10' => L_OCTOBER,
			'11' => L_NOVEMBER,
			'12' => L_DECEMBER);
		$aDay = array(
			'1' => L_MONDAY,
			'2' => L_TUESDAY,
			'3' => L_WEDNESDAY,
			'4' => L_THURSDAY,
			'5' => L_FRIDAY,
			'6' => L_SATURDAY,
			'0' => L_SUNDAY);

		switch ($key) {
			case 'day':
				return $aDay[ $value ]; break;
			case 'month':
				return $aMonth[ $value ]; break;
		}
	}

	/**
	 * Méthode qui convertit une date au format ISO en tenant compte d'un décalage horaire
	 *
	 * @param	date		date sous forme de chaine de caractères
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
	 * Méthode qui retourne un timestamp au format ISO en tenant compte d'un décalage horaire
	 *
	 * @param	timestamp	timestamp
	 * @return	date		date au format ISO
	 **/
	public static function timestampToIso($timestamp,$delta) {

		return @date('Y-m-d\TH:i:s',$timestamp).$delta;
	}

	/**
	 * Méthode qui convertit une date ISO au format humain en tenant compte du formatage passé en paramètre
	 *
	 * @param	date		date au format AAAAMMJJ
	 * @param	format		format de la date de sortie (variable: #minute,#hour,#day,#month,#num_day,#num_month,#num_year(2),#num_year(4))
	 * @return	date		date formatée au format humain
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
	 * Méthode qui retourne l'heure au format humain contenue dans une date au format ISO
	 *
	 * @param	date		date au format ISO
	 * @return	heure		heure formatée HH:MM
	 **/
	public static function heureIsoToHum($date) {

		# On retourne l'heure au format 12:55
		return substr($date,11,2).':'.substr($date,14,2);
	}

	/**
	 * Méthode qui découpe une date ISO dans un tableau (element du tableau: year, month, day, time, delta)
	 *
	 * @param	date		date au format ISO
	 * @return	array		tableau contenant les différentes parties de la date
	 **/
	public static function dateIso2Admin($date) {

		preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9:]{8})((\+|-)[0-9:]{5})/',$date,$capture);
		return array ('year' => $capture[1],'month' => $capture[2],'day' => $capture[3],'time' => substr($capture[4],0,5),'delta' => $capture[5]);
	}

	/**
	 * Méthode qui vérifie la validité de la date et de l'heure
	 *
	 * @param	int		mois
	 * @param	int		jour
	 * @param	int		année
	 * @param	int		heure:minute
	 * @return	boolean	vrai si la date est valide
	 **/
	public static function checkDate($day, $month, $year, $time) {

		return (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])(0[1-9]|1[0-2])[1-2][0-9]{3}([0-1][0-9]|2[0-3])\:[0-5][0-9]$/",$day.$month.$year.$time)
			AND checkdate($month, $day, $year));

	}

	/**
	 * Fonction de conversion de date ISO en format RFC822
	 *
	 * @param	date	date à convertir
	 * @return	string	date au format iso.
	 * @author	Amaury GRAILLAT
	 **/
	public static function dateIso2rfc822($date) {

		$tmpDate = plxDate::dateIso2Admin($date);
		return @date(DATE_RSS, mktime(substr($tmpDate['time'],0,2), substr($tmpDate['time'],3,2), $tmpDate['second'], $tmpDate['month'], $tmpDate['day'], $tmpDate['year']));
	}

}
?>