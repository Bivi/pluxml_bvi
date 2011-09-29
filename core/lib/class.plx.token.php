<?php
/**
 * Classe plxToken responsable du controle des formulaires
 *
 * @package PLX
 * @author	Stephane F
 **/

/*
How to use:
===========

<form action="process.php" method="post">
	<label>What is your name? <input name="name" /></label>
	<input type="submit" />
	<?php echo plxToken::getTokenPostMethod() ?>
</form

plxToken::validateFormToken($_POST);
# if valid token, next lines will be executed else script will die here

*/

class plxToken {

	const FIELDNAME = 'token';

	public static function getTokenPostMethod() {
		$token = self::_generateToken();
		return '<input name="'.self::FIELDNAME.'" value="'.$token.'" type="hidden" />';
	}

	public static function validateFormToken($request, $clear = true) {

		if($_SERVER['REQUEST_METHOD']=='POST') {
			$valid = false;
			$posted = isset($request[self::FIELDNAME]) ? $request[self::FIELDNAME] : '';
			if (!empty($posted)) {
				if (isset($_SESSION['formtoken'][$posted])) {
					if ($_SESSION['formtoken'][$posted] >= time() - 3600) { # 3600 seconds
						$valid = true;
					}
				if ($clear)
					unset($_SESSION['formtoken'][$posted]);
				}
			}
			if(!$valid) {
				die('Security error : invalid or expired token');
			}
		}
	}

	protected static function _generateToken() {
		$time = time();
		$token = sha1(mt_rand(0, 1000000));
		$_SESSION['formtoken'][$token] = $time;
		return $token;
	}

}
?>