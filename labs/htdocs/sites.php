<?php
require_once('config.php');
require_once('inc/functions.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<meta http-equiv="cache-control" content="no-cache" />
<meta name="keywords" content="V2EX, Babel, Livid, PHP" />
<title>V2EX Labs - Sites</title>
<link href="http://www.v2ex.com/favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body><a name="top"></a>
<?php _v_logo(); ?>
<span class="welcome">V2EX 实验室，走进我们的产品、技术和团队</span>
<div class="nav"><?php _v_nav('sites'); ?></div>
<div class="main">
<div class="left">
<div class="header"><?php _v_ico_silk('server'); ?> <a name="sites"> </a> Sites</div>
<div class="content">

这里是一个使用 Project Babel 搭建起来的网站的列表，如果你希望加入到这个列表中，那么请将你的站点（使用 Project Babel 搭建的站点）的一些介绍发信到 <a href="/team.php">Livid</a> 的邮箱 v2ex.livid at mac.com 或者发布到 Project Babel 在 V2EX 上的讨论区 <a href="http://www.v2ex.com/go/babel" target="_blank">http://www.v2ex.com/go/babel</a> ，如果你的站点已经出现在下面的列表中，而这是你不希望的，那么也请写信到 v2ex.livid at mac.com ，我将在收到你的信之后将站点从这个列表中移除。

<?php _v_hr(); ?>

<?php _v_ico_silk('bullet_black'); ?> <strong>China On Rails</strong> - <a href="http://www.chinaonrails.com" target="_blank">www.chinaonrails.com</a>

<blockquote><span class="tip">Ruby on Rails 开发专题社区</span></blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>Intown</strong> - <a href="http://www.intown.tv/" target="_blank">www.intown.tv</a>

<blockquote><span class="tip">Community for Korean Chinese</span></blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>18mo</strong> - <a href="http://www.18mo.com/" target="_blank">www.18mo.com</a>

<blockquote><span class="tip">猛小蛇的个人网站</span></blockquote>


</div>

</div>

</div> <!-- end of div class="left" -->
<div class="right">

<div class="header">
<?php _v_ico_silk('plugin'); ?> 相关网站
</div>

<div class="content">

<small><?php _v_ico_silk('bullet_go'); ?> <a href="http://framework.zend.com/" target="_blank">Zend Framework</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://pear.php.net/" target="_blank">PEAR</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.famfamfam.com/" target="_blank">FAMFAMFAM</a><br />
</small>
</div>

<div class="header">
<?php _v_ico_silk('images'); ?> Subversion 客户端
</div>

<div class="content">
<small>
<?php _v_ico_silk('bullet_go'); ?> <a href="http://subversion.tigris.org/" target="_blank">Subversion Official</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://subclipse.tigris.org/" target="_blank">Subclipse</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://tortoisesvn.tigris.org/" target="_blank">TortoiseSVN</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://rapidsvn.tigris.org/" target="_blank">RapidSVN</a>
</small>
</div>

<div class="header">
<?php _v_ico_silk('comments'); ?> 相关 V2EX 讨论区
</div>

<div class="content">
<small>
<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/babel" target="_blank">Project Babel</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/php" target="_blank">PHP</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/linux" target="_blank">Linux</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/httpd" target="_blank">Apache Web Server</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/mysql" target="_blank">MySQL</a><br />

<?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/go/svn" target="_blank">Subversion</a><br />
</small>
</div>

</div>
</div>
</div>
<div class="sep"></div>
<?php _v_hr(); ?>
<div class="svn">svn: $Id: index.php 63 2007-03-16 10:04:39Z v2ex.livid $</div>
</body>
</html>

