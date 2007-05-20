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
<title>V2EX Labs - Installation</title>
<link href="http://www.v2ex.com/favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body><a name="top"></a>
<img src="/img/logo.png" alt="V2EX Labs" align="left" />
<span class="welcome">V2EX 实验室，走进我们的产品、技术和团队</span>
<div class="nav"><?php _v_nav('installation'); ?></div>
<div class="main">
<div class="left">
<div class="header"><?php _v_ico_silk('basket_put'); ?> <a name="installation"> </a> Installation</div>
<div class="content">

你可以有两种选择来获得 Project Babel 的安装包。Distribution 发行版本（在一些文档中，也被叫做严肃版本）中包括了经过测试的安装手段、相对稳定的功能、完整的依赖关系及升级脚本。比较适合初学者，但是这个版本中包括的功能集相对比较古老一些。<br /><br />

另外一种选择是 Trunk 最新版本（你可以把这个版本理解为是非严肃版本），你将需要使用 Subversion 来获得最新的源代码。因为 Project Babel 的代码管理的原则之一就是我们尽量不把那些“可以被下载”和“可以被重复生成”的部分放进我们的代码管理库中，所以这个版本中缺乏两个必要的依赖程序包 <a href="http://pear.php.net/" target="_blank">PEAR</a> 和 <a href="http://framework.zend.com/" target="_blank">Zend Framework</a>，及来自 <a href="http://www.famfamfam.com/" target="_blank">FAMFAMFAM</a> 的 <a href="http://www.famfamfam.com/lab/icons/silk/" target="_parent">Silk</a> 图标库，你将需要自己下载并安装这些缺少的部分。具体可以参考 V2EX Labs 中关于 Trunk 版本的 <a href="/installation.php">installation</a> 的文档。

<br /><br />

<?php _v_ico_silk('disk'); ?> <strong>发行版本 - Distribution Version</strong>

<?php _v_hr(); ?>

发行版本中包括了完整的程序源代码及运行所依赖的组件，适合初学者安装。但是其中的技术可能不是最新最好的。Project Babel 的发行版本的压缩包推荐使用 gnu tar 解压缩。<br /><br />

<?php _v_ico_silk('bullet_go'); ?> 2006-7-14: <strong>R500</strong> <span class="tip"><small>Stable Release</small></span>
<blockquote>
下载地址:&nbsp;&nbsp;<a href="http://project-babel.googlecode.com/files/r500.tgz" target="_blank">http://project-babel.googlecode.com/files/r500.tgz</a> <span class="tip">3.3M</span><br />
安装说明:&nbsp;&nbsp;<a href="http://www.v2ex.com/topic/view/1736.html" target="_blank">http://www.v2ex.com/topic/view/1736.html</a>
</blockquote>
<?php _v_ico_silk('bullet_go'); ?> 2006-4-10: <strong>R215</strong> <span class="tip"><small>Legacy Release</small></span>
<blockquote>
下载地址:&nbsp;&nbsp;<a href="http://project-babel.googlecode.com/files/r215.tgz" target="_blank">http://project-babel.googlecode.com/files/r215.tgz</a> <span class="tip">2.1M</span><br />
安装说明:&nbsp;&nbsp;<a href="http://www.v2ex.com/topic/view/127.html" target="_blank">http://www.v2ex.com/topic/view/127.html</a>
</blockquote><br />

<?php _v_ico_silk('brick'); ?> <strong>最新版本 - Cutting Edge Version</strong>

<?php _v_hr(); ?>

Project Babel 的项目控制站点及 Subversion repository 位于 <a href="http://code.google.com/p/project-babel" target="_blank">Google Code</a>，你可以使用很多 <a href="http://subversion.tigris.org/" target="_blank">Subversion</a> 客户端匿名 check out 最新的开发进展。<br /><br />

<?php _v_ico_silk('bullet_go'); ?> <strong>Subversion Trunk</strong>
<blockquote><span style="font-family: 'Courier New', mono, fixed;">svn co http://project-babel.googlecode.com/svn/trunk project-babel</span><br />
安装说明及讨论:&nbsp;&nbsp;<a href="http://www.v2ex.com/topic/view/7856.html" target="_blank">http://www.v2ex.com/topic/view/7856.html</a>
</blockquote>
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
<div class="svn">svn: $Id$</div>
</body>
</html>

