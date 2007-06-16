<?php
function vx_check_login() {
	$rt = array();
	
	$rt['mode'] = 'ok';
	
	$rt['return'] = '';
	
	$rt['errors'] = 0;
	
	$rt['usr_value'] = '';
	$rt['usr_email_value'] = '';
	/* usr_error:
	0 => no error
	1 => empty
	999 => unspecific */
	$rt['usr_error'] = 0;
	$rt['usr_error_msg'] = array(1 => '你忘记填写用户名了');
	
	$rt['usr_password_value'] = '';
	/* usr_password_error:
	0 => no error
	1 => empty
	2 => mismatch
	999 => unspecific */
	$rt['usr_password_error'] = 0;
	$rt['usr_password_error_msg'] = array(1 => '你忘记填写密码了', 2 => '名字或者密码有错误');

	if (isset($_POST['return'])) {
		if (function_exists('get_magic_quotes_gpc')) {
			if (get_magic_quotes_gpc()) {
				$rt['return'] = trim(stripslashes($_POST['return']));
			} else {
				$rt['return'] = trim($_POST['return']);
			}
		} else {
			$rt['return'] = trim($_POST['return']);
		}
	}
	
	if (isset($_POST['usr'])) {
		if (function_exists('get_magic_quotes_gpc')) {
			if (get_magic_quotes_gpc()) {
				$rt['usr_value'] = strtolower(make_single_safe(stripslashes($_POST['usr'])));
			} else {
				$rt['usr_value'] = strtolower(make_single_safe($_POST['usr']));
			}
		} else {
			$rt['usr_value'] = strtolower(make_single_safe($_POST['usr']));
		}
		if (mb_strlen($rt['usr_value'], 'UTF-8') == 0) {
			$rt['usr_error'] = 1;
			$rt['errors']++;
		}
	} else {
		$rt['usr_error'] = 1;
		$rt['errors']++;
	}
	
	if ($rt['errors'] > 0) {
		return $rt;
	}
	
	if (isset($_POST['usr_password'])) {
		if (function_exists('get_magic_quotes_gpc')) {
			if (get_magic_quotes_gpc()) {
				$rt['usr_password_value'] = make_single_safe(stripslashes($_POST['usr_password']));
			} else {
				$rt['usr_password_value'] = make_single_safe($_POST['usr_password']);
			}
		} else {
			$rt['usr_password_value'] = make_single_safe($_POST['usr_password']);
		}
		if (strlen($rt['usr_password_value']) == 0) {
			$rt['usr_password_error'] = 1;
			$rt['errors']++;
		}
	} else {
		$rt['usr_password_error'] = 1;
		$rt['errors']++;
	}
	
	if ($rt['errors'] > 0) {
		return $rt;
	}
	
	$sql = "SELECT usr_id FROM babel_user WHERE usr_email = '" . mysql_real_escape_string($rt['usr_value']) . "' AND usr_password = '" . mysql_real_escape_string(sha1($rt['usr_password_value'])) . "'";
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		mysql_free_result($rs);
		$rt['usr_email_value'] = $rt['usr_value'];
	} else {
		mysql_free_result($rs);
		$sql = "SELECT usr_id, usr_email FROM babel_user WHERE usr_nick = '" . mysql_real_escape_string($rt['usr_value']) . "' AND usr_password = '" . sha1($rt['usr_password_value']) . "'";
		$rs = mysql_query($sql);
		if ($user_array = mysql_fetch_array($rs)) {
			$rt['usr_email_value'] = $user_array['usr_email'];
		} else {
			$rt['usr_password_error'] = 2;
			$rt['errors']++;
		}
		mysql_free_result($rs);
	}
	
	return $rt;
}
?>
