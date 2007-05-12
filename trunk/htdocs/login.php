<?php
define('V2EX_BABEL', 1);

// Old school Project Babel stuff.
require_once ('core/Settings.php');
require_once ('core/Vocabularies.php');
require_once ('core/Utilities.php');
require_once ('core/Shortcuts.php');

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
			header('Location: ' . $rt['return']);
			die();
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 TRANSITIONAL//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title><?php echo Vocabulary::site_name ?></title>
<link href="/css/login.css" media="screen" rel="stylesheet" type="text/css" />
</head>
<?php
switch ($rt['mode']) {
	case 'welcome':
	default:
?>
<body onload="document.forms[0].elements[0].focus();">
<div id="main" align="center">

	<div id="v2ex" align="left">
		<div class="title"><?php echo Vocabulary::site_name ?> 登录</div>
		<?php _v_hr() ?>
		<table width="100%" cellpadding="5" cellspacing="0" class="login_form_t">
			<form action="/login.php" method="post">
			<tr>
				<td width="80" align="right">用户名:</td>
				<td align="left"><input name="usr" type="text" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="100" /></td>
			</tr>
			<tr>
				<td width="80" align="right">密码:</td>
				<td align="left"><input name="usr_password" type="password" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="32" /></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td align="left"><span class="tip"><a href="/passwd.vx">我忘记了密码</a> &nbsp;|&nbsp; <a href="/signup.html">注册</a> &nbsp;|&nbsp; <a href="/">游客</a></span></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td valign="middle"><input type="image" src="/img/graphite/login.gif" alt="登录" /></td>
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
	case 'ok':
?>
<body>
<div id="main" align="center">
<div id="v2ex" align="left">
<div class="title"><?php echo Vocabulary::site_name ?> 登录成功</div>
<?php
_v_hr();
echo('<div id="info">正在自动跳转到 <a href="/">' . Vocabulary::site_name . '</a> 首页，或者你可以 <a href="/">点击这里</a> 进行手动跳转</div>');
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
		<div class="title"><?php echo Vocabulary::site_name ?> 登录</div>
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
				<td width="80" align="right">用户名:</td>
				<td align="left"><input name="usr" type="text" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="100" /></td>
			</tr>
			<tr>
				<td width="80" align="right">密码:</td>
				<td align="left"><input name="usr_password" type="password" class="line" onfocus="this.style.borderColor = '#0C0'; this.style.backgroundColor = '#FFF';" onblur="this.style.borderColor = '#999'; this.style.backgroundColor = '#F5F5F5';" maxlength="32" /></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td align="left"><span class="tip"><a href="/passwd.vx">我忘记了密码</a> &nbsp;|&nbsp; <a href="/signup.html">注册</a> &nbsp;|&nbsp; <a href="/">游客</a></span></td>
			</tr>
			<tr>
				<td width="80"></td>
				<td valign="middle"><input type="image" src="/img/graphite/login.gif" alt="登录" /></td>
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
		<div class="title">你已经从 <?php echo Vocabulary::site_name ?> 登出</div>
		<?php _v_hr() ?>
		<div id="logout">
			<p>没有任何个人信息被留在你现在使用过的计算机上，请对你的隐私放心。</p>
			<p><?php echo Vocabulary::site_name ?> 欢迎你随时再度访问！</p>
			<p>
				<li><a href="/login">重新登录</a></li>
				<li><a href="/">返回 <?php echo Vocabulary::site_name ?> 首页</a></li>
				<li><a href="/new_features.html" target="_blank">新功能</a></li>
				<li><a href="http://io.v2ex.com/v2ex-doc/index.html" target="_blank">帮助</a> <img src="/img/ext.png" align="absmiddle" /></li>
				
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
