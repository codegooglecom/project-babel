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
<title>V2EX Labs</title>
<link href="http://www.v2ex.com/favicon.ico" rel="shortcut icon" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body><a name="top"></a>
<img src="/img/logo.png" alt="V2EX Labs" align="absmiddle" />
<span class="welcome">V2EX 实验室，走进我们的产品、技术和团队</span>
<div class="main">
<div class="left">
<div class="header">
<img src="/img/favicon.png" align="absmiddle" /> <a name="babel"> </a> Project Babel </div>
<div class="content">
<a href="http://www.v2ex.com/" target="_blank"><img src="/img/v2ex_babel.png" align="left" style="margin-right: 20px;" border="0" /></a>
Project Babel 是一套在众多方面进行创新的开放源代码网络社区软件，发布于 GPL 协议下。<br /><br />

Project Babel 的界面设计十分清爽，几乎不需要任何额外思考的可用性是我们的最高宗旨，而尽可能地符合 W3C 标准当然也是我们的目标之一。作为对社区中讨论话题的补充，Project Babel 具有非常强大的内容聚合功能，每个讨论区支持从 RSS，外部网址及 Flickr 图片社区中聚合相关内容。<br /><br />

“人”是一个社区中最关键的因素，Project Babel 支持让社区中任何一个个体都可以充分的表现和表达自我。你可以在 Project Babel 中添加你的各种网上据点、你的朋友，同时程序还专门为你准备了一份“成分分析”。下面这里是一个 profile 页的例子：<br /><br />

<small><span class="tip">Livid's V2EX Profile: </span><a href="http://www.v2ex.com/u/Livid" target="_blank">http://www.v2ex.com/u/Livid</a></small><br /><br />

<a href="http://www.v2ex.com/" target="_blank">V2EX</a> 即基于 Project Babel 的最新版本搭建，这是一个面向那些充满好奇心，有着不寻常生活态度的年轻人的全新社区，你可以到那里感受一下实际运行中的最新版本的 Project Babel。<br /><br />

Project Babel 构建于 PHP 5.2 技术上，结合 MySQL 4.1，系统中所有文字的编码是 UTF-8，可以很好地支持各种语言。我们使用了 Apache 的 mod_rewrite 来为所有使用 Project Babel 搭建的社区的 SEO 效果加分。<br /><br />

每一个页面上都尽可能地使用了 cache，因此即使内容完全动态，Project Babel 仍然可以提供足以支撑每天 1,000,000 访问量的性能。并且，cache 触发机制使得在访问量越高时，cache 命中率就越高。<br /><br />

Project Babel 在 GPL 协议下发布，这意味着你可以自由地下载，修改甚至再发布 Project Babel 的整个系统，你甚至可以销售它！<br /><br />

而如果你在 Project Babel 的基础上做出了一些有益的修改和增强，那么整个社区将非常欢迎你将这些修改和增强同样以 GPL 协议发布。<br /><br />

系统需求：

<blockquote style="font-size: 90%;">
&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Scripting Runtime: PHP 5.0 or later<br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Web Server: Apache Web Server with mod_rewrite<br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Database: MySQL 4.1 or later<br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Server OS: Any<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tip">Linux is recommended</span><br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Server RAM: 2G at a minimum<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tip">Usually every httpd process costs 20M, it's up to your concurrency</span><br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Server Storage: 400M free disk space at a minimum<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tip">Depends on your traffic, 4G or more is recommended</span><br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Required Dependencies: Zend Framework 0.2.0 or later<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="tip">0.7.0 is included in newer releases</span><br />

&nbsp;&nbsp;<img src="/img/bullet.png" align="absmiddle" />&nbsp;Optional Dependencies: ImageMagick, <a href="http://www.dict.org/" target="_blank">dictd</a><br />
</blockquote>

我们在这个项目的诸多细节上投入了非常多的精力，如果你也是一个非常注重细节的完美理想主义者的话，你或许会喜欢上这些细节的。比如，Project Babel 的所有功能和效果都能够在所有浏览器上做到一致，包括 Safari，Opera，Firefox 和 IE 等浏览器从 2004 年以后开始的所有版本。
</div>
<div class="header"><?php _v_ico_silk('basket_put'); ?> <a name="downloads"> </a> Downloads</div>
<div class="content">

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

Project Babel 的项目控制站点及 Subversion repository 位于 <a href="http://code.google.com/p/project-babel" target="_blank">Google Code</a>，你可以使用很多 <a href="http://subversion.tigris.org/" target="_blank">Subversion</a> 客户端匿名 check out 最新的开发进展。这个版本中缺乏一些外部的依赖文件，如 <a href="http://pear.php.net/" target="_blank">PEAR</a> 和 <a href="http://framework.zend.com/" target="_blank">Zend Framework</a>，及一些必要的外部图片，因此，如果你使用这个版本安装，可能会有一定难度。<br /><br />

<?php _v_ico_silk('bullet_go'); ?> <strong>Subversion Trunk</strong>
<blockquote><span style="font-family: 'Courier New', mono, fixed;">svn co https://project-babel.googlecode.com/svn/trunk project-babel</span><br />
安装说明及讨论:&nbsp;&nbsp;<a href="http://www.v2ex.com/topic/view/7856.html" target="_blank">http://www.v2ex.com/topic/view/7856.html</a>
</blockquote>
</div>

<div class="header">
<img src="/img/icons/application_side_list.png" align="absmiddle" /> <span class="new">NEW!</span> V2EX Sidebar
</div>
<div class="content">
<script type="text/javascript">
function add_mozilla_sidebar() {
	if ((typeof window.sidebar == "object") && (typeof window.sidebar.addPanel == "function")) {
		window.sidebar.addPanel ("V2EX Sidebar", "http://www.v2ex.com/sidebar.html", "");
	} else {
		window.alert("抱歉，这个功能只存在于 Mozilla Firefox 及其他 Gecko 核心的浏览器中。");
	}
	return false;
}
</script>
<img src="/img/v2ex_sidebar.png" align="right" style="margin-left: 20px;" border="0" />
如果你在使用 Mozilla Firefox，那么这项功能就是专门为你特别设计的，可以帮助你节约时间！<br /><br />
你可以把下面这个页面加入你的 Mozilla Firefox 的收藏夹：<br /><br />

<a href="http://www.v2ex.com/sidebar.html" target="_blank">http://www.v2ex.com/sidebar.html</a> <img src="/img/icons/add.png" align="absmiddle" /> <a href="#;" onclick="add_mozilla_sidebar();">点击加入收藏</a>

<br /><br />

然后在其属性中选定“在侧栏载入此书签”，之后每次当你打开这个收藏的时候，Mozilla Firefox 便会在其侧栏中打开这个“微内容”页面，你可以在这个页面上看到 V2EX 的最新讨论主题，同时上面还有很多特别优秀的网络服务的图标链接，比如 Flickr，del.icio.us 和 Google Reader，帮助你节约时间！
</div>
<div class="header">
<img src="/img/icons/phone.png" align="absmiddle" /> V2EX Mobile
</div>
<div class="content">
<img src="/img/v2ex_psp.png" align="left" style="margin-right: 20px;" />
V2EX 的制作者是一个狂热的移动设备收藏迷，因而能够兼容各种具有上网功能的移动设备，也成为了 V2EX 的实现目标之一。你可以在你的 PSP，Palm，PocketPC 及 Smartphone 的浏览器中输入 V2EX.com 试试！<br /><br />

在未来，我们还将继续针对各种移动设备开发更多的功能，我们相信，在未来的某一天，V2EX 的超过 50% 的访问量将是来自移动设备的。
</div>
</div> <!-- end of div class="left" -->
<div class="right">
<div class="header">
<img src="/img/icons/wrench.png" align="absmiddle" /> V2EX Gadgets
</div>
<div class="content">
<img src="/img/icons/book_open.png" align="absmiddle" /> <a href="http://www.v2ex.com/man.html" target="_blank">参考文档搜索</a><br />
<span class="tip"><small>High performance search system built for manual pages of PHP, PEAR, Smarty, Zend Framework, Suversion and PostgreSQL.</small></span>
</div>
<div class="header">
<img src="/img/icons/feed.png" align="absmiddle" /> V2EX RSS Channels
</div>
<div class="content"><span class="tip"><small>
<img src="/img/bullet_feed.png" align="absmiddle" /> Overall &nbsp;<a href="http://v2ex.com/feed/v2ex.rss" target="_blank">http://v2ex.com/feed/v2ex.rss</a><br />
<img src="/img/bullet_feed.png" align="absmiddle" /> Board &nbsp;<a href="http://v2ex.com/feed/board/mac.rss" target="_blank">http://v2ex.com/feed/board/<strong>mac</strong>.rss</a><br />
<img src="/img/bullet_feed.png" align="absmiddle" /> Geo &nbsp;<a href="http://v2ex.com/feed/geo/shanghai" target="_blank">http://v2ex.com/feed/geo/<strong>shanghai</strong></a><br />
<img src="/img/bullet_feed.png" align="absmiddle" /> Topic &nbsp;<a href="http://v2ex.com/feed/topic/7746.rss" target="_blank">http://v2ex.com/feed/topic/<strong>7746</strong>.rss</a><br />
<img src="/img/bullet_feed.png" align="absmiddle" /> Member &nbsp;<a href="http://v2ex.com/feed/user/Livid" target="_blank">http://v2ex.com/feed/user/<strong>Livid</strong></a><br />
</small></span><p style="padding: 0px; margin: 5px 0px 0px 0px;">替换上面的粗体字中的内容即可选择性订阅。<br />

<span class="tip"><small><img src="http://www.v2ex.com/img/favicons/google/reader.png" align="absmiddle" /> <a href="http://reader.google.com/" target="_blank">Google Reader</a> is recommended!</small></span><br />

<span class="tip"><small><img src="http://static.cn.v2ex.com/v2ex/0.5/img/favicons/pageflakes.png" align="absmiddle" /> <a href="http://www.pageflakes.com/?source=d736779a-49d4-46a7-a918-a70ad0b8cbd8" target="_blank">Pageflakes</a> is recommended!</small></span><br />

</p></div>

<div class="header">
<?php _v_ico_silk('eye'); ?> 媒体报道
</div>

<div class="content"><?php _v_ico_silk('bullet_go'); ?> <a href="http://www.v2ex.com/topic/view/6488.html" target="_blank">三联生活周刊</a></div>

<div class="header">
<img src="/img/icons/group.png" align="absmiddle" /> V2EX Team
</div>
<div class="content">
<a href="http://www.v2ex.com/u/Livid" target="_blank"><img src="http://www.v2ex.com/img/p/1_s.jpg" style="margin-right: 5px;" align="left" class="p" border="0" /></a> <a href="http://www.v2ex.com/u/Livid" target="_blank">Livid</a><span class="tip"><small> - Location: Shanghai, China</small></span><br /><span class="tip"><small>The creator of the project.</small></span>
<?php _v_hr(); ?>
<span class="tip"><small><?php _v_ico_silk('email_edit'); ?> Want to join? write to <a target="_blank">v2ex.livid at mac.com</a></small></span>
</div>


</div>
</div>
</div>
<div class="sep"></div>
<?php _v_hr(); ?>
<div class="svn">svn: $Id$</div>
</body>
</html>

