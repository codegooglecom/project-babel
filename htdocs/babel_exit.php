<?php
define(V2EX_BABEL, 1);

require('core/Settings.php');

setcookie('babel_usr_email', '', time() - 86400, '/');
setcookie('babel_usr_password', '', time() - 86400, '/');
$_SESSION['babel_usr_email'] = '';
$_SESSION['babel_usr_password'] = '';

if (isset($_SERVER['HTTP_REFERER'])) {
	if ($_SERVER['HTTP_REFERER'] != '') {
		$_prev = $_SERVER['HTTP_REFERER'];
	} else {
		if (BABEL_DEBUG) {
			$_prev = '/';
		} else {
			$_prev = 'http://' . BABEL_DNS_NAME . '/';
		}
	}
} else {
	if (BABEL_DEBUG) {
		$_prev = '/';
	} else {
		$_prev = 'http://' . BABEL_DNS_NAME . '/';
	}
}

header('Content-type: text/html;charset=UTF-8');

$to = $_prev;

header('Location: ' . $to);
?>