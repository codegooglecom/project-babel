<?php
/* Project Babel
*  Author: Livid Torvalds
*  File: /htdocs/core/Vocabularies.php
*  Usage: Controlled vocabulary
*  Format: 1 tab indent(4 spaces), LF, UTF-8, no-BOM
*
*  Subversion Keywords:
*
*  $Id$
*  $LastChangedDate$
*  $LastChangedRevision$
*  $LastChangedBy$
*  $URL$
*/

if (@V2EX_BABEL != 1) {
	die('<strong>Project Babel</strong><br /><br />Made by <a href="http://www.v2ex.com/">V2EX</a> | software for internet');
}

class Vocabulary {
	const site_name = 'V2EX';
	const site_title = 'V2EX | Project Babel';
	const site_title_mobile = 'V2EX Mobile';
	const site_copyright = 'Project Babel | v0.5-pre Monster Inc | Copyright © 2007 Xin Liu (a.k.a. <a href="http://www.livid.cn/" target="_blank">Livid</a>)';
	
	const site_banner = "<a href=\"/\" target=\"_self\" class=\"var\"><img src=\"/img/top_logo_carbon.gif\" border=\"0\" alt=\"V2EX\" onclick=\"location.href='/';\" style=\"cursor: hand;\" align=\"absmiddle\" onmouseover=\"focusGo();\" /></a>";
	
	const meta_keywords = 'V2EX, Babel, Livid, PHP, ';
	const meta_description = 'V2EX | software for internet';
	const meta_category = 'Technology';
	
	const action_signup = '注册';
	const action_login = '登录';
	const action_logout = '登出';
	const action_passwd = '忘记密码';
	const action_passwd_reset = '重设密码';
	
	const action_mobile_search = '手机号码所在地查询';
	
	const action_man_search = '参考文档藏经阁';
	
	const action_newtopic = '创建新主题';
	const action_replytopic = '回复主题';
	const action_viewtopic = '查看主题';
	const action_modifytopic = '修改主题';
	const action_modifypost = '修改回复';
	const action_freshtopic = 'virgin 主题';
	
	const action_viewboard = '查看讨论版';
	const action_viewexpense = '查看消费记录';
	const action_viewonline = '查看谁在线';
	
	const action_modifyprofile = '修改信息与设置';
	const action_modifygeo = '修改我的所在地';
	
	const action_composemessage = '撰写短消息';
	
	const action_search = '搜索';
	
	const action_dry_new = '添加新的 DRY 项目';
	
	const msg_submitwrong = '对不起，你刚才提交的信息里有些问题';

	const term_toptopic = '最强主题排行';
	const term_favoritetopic = '我最喜欢的主题';
	const term_accessdenied = '访问阻止';
	
	const term_virgin_topic = ' virgin 主题';
	
	const term_privatemessage = '短消息';
	
	const term_user_random = '茫茫人海';
	
	const term_favorite = '我的收藏夹';
	
	const term_zen = 'ZEN';
	
	const term_dry = 'DRY';
	
	const term_region = '地域';
	
	const term_member = '会员';
	
	const term_userlogins = '会员登录历史';
	
	const term_pix = 'PIX';
	
	const term_ing = 'ING';
	
	const term_user_empty = '会员不存在';
	
	const term_shuffle_cloud = 'Shuffle 首页上的云';
	
	const term_right_friends = '右侧菜单上的好友列表';
	
	const term_sessionstats = '访问历史管理';
	
	const term_status = '系统状态';
	const term_jobs_kijiji = 'Kijiji Jobs';
	
	const term_hottopic = '热门话题';
	const term_latesttopic = '最新主题';
	const term_latest_answered_topic = '最新被回复主题';
	const term_latestfav = '最新收藏';
	
	const term_community_guidelines = 'V2EX 社区指导原则';
	const term_partners = '广告合作伙伴';
	const term_newfeatures = '新功能';
	
	const term_babel_downloads = '程序下载';
	
	const term_rules = '电子公告服务规则';
	const term_terms = '使用规则';
	const term_privacy = '隐私权保护规则';
	const term_policies = '禁止性内容规则';
	
	const term_out_of_money = '没有铜币没有银币没有金币';
}
?>
