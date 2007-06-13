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
<?php _v_logo(); ?>
<span class="welcome">V2EX 实验室，走进我们的产品、技术和团队</span>
<div class="nav"><?php _v_nav('installation'); ?></div>
<div class="main">
<div class="left">
<div class="header"><?php _v_ico_silk('control_play'); ?> <a name="installation"> </a> Installation</div>
<div class="content">
本安装说明文档适用于最复杂的 Trunk 版本（关于 Trunk 版本和 Distribution 版本的区别，请看 <a href="/">V2EX Labs</a> 的 <a href="/downloads.php">Downloads</a> 页）。<br /><br />

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 1:</strong>

<blockquote>下载压缩包或者从 Subversion repository 中获得安装文件之后，将整个目录（名字叫 babel 的顶级目录）上传到服务器，并且记下这个文件夹在服务器上所位于的绝对路径。比如在 Linux 上这个路径可能是 /www/babel 而在 Windows 上这个路径可能是 C:/www/babel 。</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 2:</strong>

<blockquote>将 htdocs/core/ 目录下的 Settings.example.php 更名为 Settings.php，然后把之前记下的那个目录位置写到 BABEL_PREFIX 这个配置选项中。假设这个位置是 /www/babel，那么完成之后的代码应该是这样的：

<div class="code">define('BABEL_PREFIX', '/www/babel');</div>
</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 3:</strong>

<blockquote>使用 <a href="http://phpmyadmin.sf.net/" target="_blank">phpMyAdmin</a> 或者 MySQL 命令行客户端将 sql/babel.mysql.sql 文件导入数据库中，然后将 MySQL 数据库的登陆信息写到 Settings.php 中：

<div class="code">define('BABEL_DB_HOSTNAME', '127.0.0.1');
define('BABEL_DB_PORT', 3306);
define('BABEL_DB_USERNAME', 'user');
define('BABEL_DB_PASSWORD', 'password');
define('BABEL_DB_SCHEMATA', 'database');</div>

将域名的相关信息写到 Settings.php 中的三个配置选项中：

<div class="code">define('BABEL_DNS_NAME', 'www.v2ex.com'); // 你希望出现在浏览器地址栏的标准化域名
define('BABEL_DNS_DOMAIN', 'v2ex.com'); // 域名的顶级部分，不包括 www 之类的 hostname
define('BABEL_DNS_FEED', 'feed.v2ex.com'); // RSS 输出专用服务器的地址
define('BABEL_FEED_URL', 'http://www.v2ex.com/feed/v2ex.rss'); // Primary RSS 地址
</div>

这个步骤非常重要，如果配置不正确，将会导致无法登录。<br /><br />

你可以将 BABEL_FEED_URL 配置为一个 <a href="http://www.feedburner.com/" target="_blank">FeedBurner</a> 的地址。
</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 4:</strong>

<blockquote>确认 Apache Web Server 已经加载了 mod_rewrite 模块，Project Babel 的 DocumentRoot 位于 htdocs 目录中，如果你是在 <a href="http://www.dreamhost.com/r.cgi?267137" target="_blank">DreamHost</a> 上安装，请在添加 Domain 时指定 DocumentRoot 到 Project Babel 文件夹中的 htdocs 目录，如果你是在 <a href="http://www.mediatemple.net/" target="_blank">Media Temple</a> 上安装，那么请将 htdocs 目录符号链接到 httpdocs 及 httpsdocs（如果你计划使用 https 方式的话），然后将 apache/htaccess/.htaccess 文件复制到 htdocs 中。</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 5:</strong>

<blockquote>编辑 htdocs/core/InstallCore.php 配置初始的分区（Section）及讨论区（Discussion Board）设置。然后从浏览器中访问此文件一次。<br /><br />
InstallCore.php 文件的概念类似于一个批处理文件，不过重复运行不会对系统造成破坏。建议在运行完毕之后，在本地备份这个文件，然后从服务器上删除此文件，否则就是一个可能的性能漏洞。</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 6:</strong>

<blockquote>如果之前各步骤进行正确的话，那么这个时候你可以从浏览器中打开域名尝试第一次访问。这是可能会继续提示一些问题的存在，比如数据库未正确配置或者目录权限问题之类，根据屏幕上的提示逐一修正这些问题。</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 7:</strong>

<blockquote>如果不再提示任何错误，那么至此安装基本完成。你可以在这个新网站上注册第一个用户，而这个用户就将成为这个社区里拥有最高权限的管理员。<br /><br />
Settings.php 中还有很多好玩的配置选项，欢迎你打开这个文件仔细研究各种功能。
</blockquote>

</div>

<div class="header"><?php _v_ico_silk('control_fastforward'); ?> <a name="upgrading"> </a> Upgrading</div>

<div class="content">在不同版本之间升级主要需要完成的事情是两件：<br /><br />

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 1:</strong>

<blockquote>备份旧版本的 Settings.php 及所有用户的头像文件。如果你是从 v0.5-pre 版本升级的话，那么上传所有的文件之后，打开新的 Settings.example.php，将旧的 Settings.php 中的定制过的配置写入，然后将这个新的 Settings.php 上传。如果你对 Vocabularies.php 进行过定制，那么也请对比新旧版本之后，在新的 Vocabularies.php 中恢复那些你定制过的部分。</blockquote>

<?php _v_ico_silk('bullet_black'); ?> <strong>STEP 2:</strong>

<blockquote>使用 <a href="http://www.sqlmanager.net/" target="_blank">EMS</a> <a href="http://www.sqlmanager.net/en/products/studio/mysql" target="_blank">SQL Studio for MySQL</a> 生成新旧两个版本的数据库的升级脚本。然后在旧数据库上执行之后升级到最新的数据库结构。请注意备份你的数据。</blockquote>

Project Babel 从 2006 年第一次开放源代码发布至今，每一个版本都有使用者，而各个版本之间的区别明显，因此在升级时所需要付出的努力也不同。欢迎你到 <a href="http://www.v2ex.com/" target="_blank">V2EX</a> 的 <a href="http://www.v2ex.com/go/babel" target="_blank">Project Babel</a> 讨论区和大家一起研究你所遇到的问题。<br /><br />

如果你对你已经安装的那个版本完全满意，你或许也就不用升级到最新版本。因为，我觉得，社区中的那些文字的精彩程度和背后的技术含量，与所用的系统恐怕是关系不大的。

</div>

<div class="header"><?php _v_ico_silk('emoticon_smile'); ?> <a name="v2u"> </a> Various Ways to Use</div>

<div class="content"></div>

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