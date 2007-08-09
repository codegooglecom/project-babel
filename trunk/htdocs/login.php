<?php
define('V2EX_BABEL', 1);

require_once ('core/Settings.php');

session_start();

include(BABEL_PREFIX . '/res/supported_languages.php');

if (isset($_SESSION['babel_lang'])) {
	if (in_array($_SESSION['babel_lang'], array_keys($_languages))) {
		define('BABEL_LANG', $_SESSION['babel_lang']);
	} else {
		define('BABEL_LANG', BABEL_LANG_DEFAULT);
	}
} else {
	define('BABEL_LANG', BABEL_LANG_DEFAULT);
}

require_once ('core/Vocabularies.php');
require_once ('core/Utilities.php');
require_once ('core/Shortcuts.php');
require_once('core/LanguageCore.php');

// New age lightweight functions.
require_once ('inc/check_login.php');

// PEAR.
ini_set('include_path', BABEL_PREFIX . '/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
require_once('Crypt/Blowfish.php');

if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
	$db = mysql_connect(BABEL_DB_HOSTNAME . ':' . BABEL_DB_PORT, BABEL_DB_USERNAME, BABEL_DB_PASSWORD);
	if ($db) {
		mysql_select_db(BABEL_DB_SCHEMATA);
		mysql_query("SET NAMES utf8");
		mysql_query("SET CHARACTER SET utf8");
		mysql_query("SET COLLATION_CONNECTION='utf8_general_ci'");
	}
	$rt = vx_check_login();
	if ($rt['errors'] == 0) {
		$bf = new Crypt_Blowfish(BABEL_BLOWFISH_KEY);
		setcookie('babel_usr_email', $rt['usr_email_value'], time() + 2678400, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_password', $bf->encrypt(sha1($rt['usr_password_value'])), time() + 2678400, '/', BABEL_DNS_DOMAIN);
		$_SESSION['babel_usr_email'] = $rt['usr_email_value'];
		$_SESSION['babel_usr_password'] = sha1($rt['usr_password_value']);
		$rt['mode'] = 'ok';
		if (trim($rt['return']) != '') {
			if (preg_match('/logout/i', $rt['return'])) {
				header('Location: /');
				die();
			} else {
				header('Location: ' . $rt['return']);
				die();
			}
		}
	} else {
		$rt['mode'] = 'error';
	}
	mysql_close($db);
} else {	
	$rt = array();
	$rt['mode'] = 'welcome';
	$rt['errors'] = 0;
	if (isset($_GET['do'])) {
		$do = strtolower($_GET['do']);
		if ($_GET['do'] == 'logout') {
			setcookie('babel_usr_email', '', 0, '/', BABEL_DNS_DOMAIN);
			setcookie('babel_usr_password', '', 0, '/', BABEL_DNS_DOMAIN);
			setcookie('babel_usr_email', '', 0, '/');
			setcookie('babel_usr_password', '', 0, '/');
			$rt['mode'] = 'logout';
		}
	} else {
		setcookie('babel_usr_email', '', 0, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_password', '', 0, '/', BABEL_DNS_DOMAIN);
		setcookie('babel_usr_email', '', 0, '/');
		setcookie('babel_usr_password', '', 0, '/');
	}
}

require_once(BABEL_PREFIX . '/lang/' . BABEL_LANG . '/lang.php');

$lang = new lang();		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 TRANSITIONAL//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title><?php echo Vocabulary::site_name ?></title>
<link href="/css/login.css" media="screen" rel="stylesheet" type="text/css" />
<?php
if (MINT_LOCATION != '') {
	echo('<script src="' . MINT_LOCATION . '" type="text/javascript"></script>');
}
?>
</head>
<?php
switch ($rt['mode']) {
	case 'welcome':
	default:
?>
<body onload="document.forms[0].elements[0].focus();">
<div id="main" align="center">

	<div id="v2ex" align="left">
		<div class="title"><?php echo Vocabulary::site_name ?> <?php echo $lang->login(); ?></div>
		<?php _v_hr() ?>
		<table width="100%" cellpadding="5" cellspacing="0" class="login_form_t">
			<form action="/login.php" method="post">
			<tr>
				<td width="80" align="right"><?php echo $lang->user_id(); ?></td>
				<td align="left"><input name="usr" type="text" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="100" /></td>
			</tr>
			<tr>
				<td width="80" align="right"><?php echo $lang->password(); ?></td>
				<td align="left"><input name="usr_password" type="password" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="32" /></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td align="left"><span class="tip"><a href="/passwd.vx"><?php echo $lang->password_recovery(); ?></a> &nbsp;|&nbsp; <a href="/signup.html"><?php echo $lang->register(); ?></a> &nbsp;|&nbsp; <a href="/"><?php echo $lang->take_a_tour(); ?></a></span></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td valign="middle"><input type="image" src="/img/graphite/login_<?php echo BABEL_LANG ?>.gif" alt="<?php echo $lang->login(); ?>" /></td>
			</tr>
<?php
if (isset($rt['return'])) {
	if (trim($rt['return']) != '') {
		echo ('<input type="hidden" value="' . make_single_return($rt['return'], 0) .  '" name="return" />');
	} else {
		if (!preg_match('/login$/i', $_SERVER['HTTP_REFERER'])) {
			echo('<input type="hidden" value="' . make_single_return($_SERVER['HTTP_REFERER']) . '" name="return" />');
		}
	}
} else {
	if (isset($_GET['r'])) {
		if (get_magic_quotes_gpc()) {
			$return = make_single_safe(stripslashes($_GET['r']));
		} else {
			$return = make_single_safe($_GET['r']);
		}
		if ($return != '') {
			echo ('<input type="hidden" value="' . make_single_return($return) . '" name="return" />');
		} else {
			if (!preg_match('/login$/i', $_SERVER['HTTP_REFERER'])) {
				echo('<input type="hidden" value="' . make_single_return($_SERVER['HTTP_REFERER']) . '" name="return" />');
			}
		}
	} else {
		if (isset($_SERVER['HTTP_REFERER'])) {
			if (!preg_match('/login$/i', $_SERVER['HTTP_REFERER'])) {
				echo('<input type="hidden" value="' . make_single_return($_SERVER['HTTP_REFERER']) . '" name="return" />');
			}
		}
	}
}
?>
			</form>
		</table>
	</div>
	
	<div id="bottom" align="center">
	&copy; 2006-2007 <a href="http://<?php echo BABEL_DNS_NAME ?>/" target="_self"><?php echo Vocabulary::site_name ?></a>
	</div>

</div>
</body>
<?php
	break;
	case 'ok':
?>
<body>
<div id="main" align="center">
<div id="v2ex" align="left">
<div class="title"><?php echo $lang->signed_in(Vocabulary::site_name); ?></div>
<?php
_v_hr();
echo('<div id="info">' . $lang->now_auto_redirecting(Vocabulary::site_name) . '</div>');
?>
</div>

	<div id="bottom" align="center">
		&copy; 2006-2007 <a href="http://<?php echo BABEL_DNS_NAME ?>/" target="_self"><?php echo Vocabulary::site_name ?></a>
	</div>
	
<script type="text/javascript">
setTimeout('location.href = "/"', 300);
</script>
</div>
</body>
<?php
	break;
	case 'error':
?>
<body onload="document.forms[0].elements[0].focus();">
<div id="main" align="center">

	<div id="v2ex" align="left">
		<div class="title"><?php echo Vocabulary::site_name ?> <?php echo $lang->login(); ?></div>
		<?php _v_hr() ?>
<?php
echo('<div id="info">');
if ($rt['usr_error'] > 0) {
	echo $rt['usr_error_msg'][$rt['usr_error']];
} else {
	if ($rt['usr_password_error'] > 0) {
		echo $rt['usr_password_error_msg'][$rt['usr_password_error']];
	}
}
echo('</div>');
?>
		<table width="100%" cellpadding="5" cellspacing="0" class="login_form_t">
			<form action="/login.php" method="post">
			<tr>
				<td width="80" align="right"><?php echo $lang->user_id(); ?></td>
				<td align="left"><input name="usr" type="text" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="100" /></td>
			</tr>
			<tr>
				<td width="80" align="right"><?php echo $lang->password(); ?></td>
				<td align="left"><input name="usr_password" type="password" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="32" /></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td align="left"><span class="tip"><a href="/passwd.vx"><?php echo $lang->password_recovery(); ?></a> &nbsp;|&nbsp; <a href="/signup.html"><?php echo $lang->register(); ?></a> &nbsp;|&nbsp; <a href="/"><?php echo $lang->take_a_tour(); ?></a></span></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td valign="middle"><input type="image" src="/img/graphite/login_<?php echo BABEL_LANG; ?>.gif" alt="<?php echo $lang->login(); ?>" /></td>
			</tr>
<?php
if (isset($rt['return'])) {
	if (trim($rt['return']) != '') {
		echo ('<input type="hidden" value="' . make_single_return($rt['return'], 0) .  '" name="return" />');
	}
} else {
	if (isset($_GET['r'])) {
		if (get_magic_quotes_gpc()) {
			$return = make_single_safe(stripslashes($_GET['r']));
		} else {
			$return = make_single_safe($_GET['r']);
		}
		if ($return != '') {
			echo ('<input type="hidden" value="' . make_single_return($return) . '" name="return" />');
		}
	}
}
?>
			</form>
		</table>
	</div>
	
	<div id="bottom" align="center">
	&copy; 2006-2007 <a href="http://<?php echo BABEL_DNS_NAME ?>/" target="_self"><?php echo Vocabulary::site_name ?></a>
	</div>

</div>
</body>
<?php
	break;
	case 'logout':
?>
<body>
<div id="main" align="center">

	<div id="v2ex" align="left">
		<div class="title"><?php echo $lang->you_have_signed_out(Vocabulary::site_name); ?></div>
		<?php _v_hr() ?>
		<div id="logout">
			<p><?php echo $lang->privacy_ok(); ?></p>
			<p><?php echo $lang->welcome_back_anytime(); ?></p>
			<p>
				<li><a href="/login"><?php echo $lang->sign_in_again(); ?></a></li>
				<li><a href="/"><?php echo $lang->return_home(Vocabulary::site_name); ?></a></li>
				<li><a href="/new_features.html" target="_blank"><?php echo $lang->new_features(); ?></a></li>
				<li><a href="http://io.v2ex.com/v2ex-doc/index.html" target="_blank"><?php echo $lang->help(); ?></a> <img src="/img/ext.png" align="absmiddle" /></li>
				
			</p>
		</div>
	</div>
	
	<div id="bottom" align="center">
	&copy; 2006-2007 <a href="http://<?php echo BABEL_DNS_NAME ?>/" target="_self"><?php echo Vocabulary::site_name ?></a>
	</div>

</div>
</body>
<?php
	break;
} // End: switch ($rt['mode'])
?>
</html>
