package com.v2ex.midgard;

import org.jivesoftware.smack.XMPPConnection;
import org.jivesoftware.smack.XMPPException;
import org.jivesoftware.smack.MessageListener;
import org.jivesoftware.smack.packet.Packet;
import org.jivesoftware.smack.packet.Message;
import org.jivesoftware.smack.Chat;

import java.sql.Connection;
import java.sql.SQLException;
import java.sql.ResultSet;
import java.sql.Statement;
import java.sql.PreparedStatement;

import java.util.Date;
import java.util.Properties;

import java.lang.System;

import java.io.*;

import net.zuckerfrei.jcfd.DatabaseList;
import net.zuckerfrei.jcfd.Definition;
import net.zuckerfrei.jcfd.DefinitionList;
import net.zuckerfrei.jcfd.Dict;
import net.zuckerfrei.jcfd.DictFactory;

import org.apache.commons.lang.exception.NestableException;
import org.apache.oro.text.regex.*;

import org.apache.lucene.analysis.Analyzer;
import org.apache.lucene.analysis.SimpleAnalyzer;
import org.apache.lucene.document.Document;
import org.apache.lucene.queryParser.QueryParser;
import org.apache.lucene.search.Hits;
import org.apache.lucene.search.IndexSearcher;
import org.apache.lucene.search.Query;
import org.apache.lucene.search.Searcher;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import com.softabar.sha4j.ShaUtil;

class V2EXProcessor extends GenericProcessor {
	public Connection db = null;
	public XMPPConnection xmpp = null;
	public Connection cache = null;
	public Searcher searcher = null;
	private Log log = LogFactory.getLog("com.v2ex.midgard.V2EXProcessor");

	V2EXProcessor(Connection dbInput, XMPPConnection xmppInput, Connection dbCache) {
		db = dbInput;
		xmpp = xmppInput;
		cache = dbCache;
		try {
			Properties p = new Properties();
			p.loadFromXML(new FileInputStream("conf/lucene.xml"));
			String luceneIndex = p.getProperty("luceneIndex");
			searcher = new IndexSearcher(luceneIndex);
		} catch (Exception e) {
			log.error("Exception: " + e.getMessage());
		}
	}
	
	public void processMessage(Packet packet) {
		try {
			if (packet instanceof Message) {
				Message msg = (Message) packet;
				// Process incoming message:
				if (msg.getBody() != null) {
					byte[] utf8 = msg.getBody().getBytes("UTF-8");
					String msgBody = new String(utf8, "UTF-8");
					log.info("Message from " + msg.getFrom() + " - " + msgBody);
					Chat chat = this.xmpp.getChatManager().createChat(msg.getFrom(), new MessageListener() {
						public void processMessage(Chat chat, Message message) {
						}
					});
					String ls = new String(msg.getBody().toLowerCase().trim());
					
					if (ls.equals("v") || ls.equals("/v") || ls.equals("/ver") || ls.equals("ver") || ls.equals("version") || ls.equals("/version")) {
						chat.sendMessage(this.getVersion());
					} else if (ls.equals("?") || ls.equals("？")) {
						chat.sendMessage(this.getHelpCN());
					} else if (ls.equals("h") || ls.equals("help") || ls.equals("/h") || ls.equals("/help")) {
						chat.sendMessage(this.getHelpCN());
					} else if (ls.equals("l") || ls.equals("/l")) {
						chat.sendMessage(this.getLatestIng(this.db));
					} else if (ls.equals("who") || ls.equals("/who")) {
						chat.sendMessage(this.getWhoAmI(this.db, msg.getFrom()));
					} else if (ls.equals("f") || ls.equals("/f")) {
						chat.sendMessage(this.getUpdates(this.db, msg.getFrom(), this.cache));
					} else if (ls.startsWith("/d")) {
						chat.sendMessage(this.getDefs(ls));
					} else if (ls.equals("all") || ls.equals("ls") || ls.equals("/ls") || ls.equals("/all")) {
						chat.sendMessage(this.getPublic(this.db));
					} else if (ls.equals("me") || ls.equals("/me") || ls.equals("/i") || ls.equals("i")) {
						chat.sendMessage(this.getMine(this.db, msg.getFrom()));
					} else if (ls.equals("/revert") || ls.equals("revert")) {
						chat.sendMessage(this.revertIng(this.db, msg.getFrom()));
					} else if (ls.startsWith("/link")) {
						chat.sendMessage(this.makeLink(msg.getBody(), this.db, msg.getFrom()));
					} else if (ls.startsWith("/php")) {
						chat.sendMessage(this.searchPHP(msg.getBody()));
					} else {
						chat.sendMessage(this.writeIng(this.db, msg.getFrom(), msgBody));
					}
				}
			}
		} catch (XMPPException xe) {
			xe.printStackTrace();
		} catch (Exception e) {
			System.err.println("Exception: " + e.getMessage());
		} finally {
			System.gc();
		}
	}
	
	public String getHelpEN() {
		String help = new String("Available commands:\n\n? or h - Print usage information\nv - Print version information\nl - Print the latest ING activity\nwho - Print current user\nf - Get the latest updates from your friends\nls - Latest updates in public timeline\nme - Get my own updates\n/revert - Erase your last update\n/d [word] - Look up dictionary for a word\n/link [uid] [password] - Link your Google account with V2EX ID\n/php [keyword] - Search for [keyword] in PHP manual pages\n\nanything else - Will update to your ING");
		return help;
	}
	
	public String getHelpCN() {
		String help = new String("欢迎使用 V2EX XMPP 即时通讯机器人\n\n可用指令:\n\n? or h - 帮助信息\nv - 系统版本信息\nl - 最新的一条 ING 更新\nwho - 显示当前用户\nf - 得到朋友们的最新 ING\nls - 得到所有人的最新 ING\nme - 得到我自己的最新 ING\n/revert - 删除上一条更新\n/d [word] - 查询词典\n/link [uid] [password] - 关联 Google 账户\n/php [keyword] - 搜索 PHP 参考手册\n\n任何的其他输入 - 将被更新到你的 ING");
		return help;
	}
	
	public String getVersion() {
		String version = "ING/V2EX - 20070708 - (c) Livid";
		return version;
	}

	private String getLatestIng(Connection db) {
		String doing = "(void)";
		
		Statement sql = null;
		ResultSet rs = null;
		
		try {
			sql = db.createStatement();
			rs = sql.executeQuery("SELECT ing_doing, usr_nick FROM babel_ing_update, babel_user WHERE ing_uid = usr_id ORDER BY ing_created DESC LIMIT 1");
			rs.next();
			doing = rs.getString("usr_nick") + ": " + rs.getString("ing_doing");
			rs.close(); rs = null;
			sql.close(); sql = null;
			return doing;
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		}
		
		return doing;
	}
	
	private String getDefs(String command) {
		if (command.length() > 3) {
			String word = command.substring(3);
			try {
				DictFactory factory = DictFactory.getInstance();
				Dict dict = factory.getDictClient();
				DefinitionList defList = dict.define(word, DatabaseList.findDatabase("wn"));
				Definition def;
				String result = "";
				int i = 0;
				while (defList.hasNext()) {
					i++;
					def = defList.next();
					result = result + "Term: " + def.getWord() + "\n";
					result = result + "Database: " + def.getDatabase().getName() + "\n";
					result = result + "\n" + def.getContent();
				}
				dict.close();
				if (i > 0) {
					return result.trim();
				} else {
					return "No definitions for this word: " + word;
				}
			} catch (Exception e) {
				return "No definitions for this word.";
			}
		} else {
			return "Please specify a word to lookup.";
		}
	}
	
	private String getWhoAmI(Connection db, String from) {
		String sender = mysqlEscape(this.getSender(from));
		String userNick = null;
		Statement sql = null;
		ResultSet rs = null;
		
		try {
			sql = db.createStatement();
			rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_google_account = " + sender + " LIMIT 1");
			if (rs.next()) {
				int userId = rs.getInt("usr_id");
				userNick = rs.getString("usr_nick");
				return "#" + userId + " member - " + userNick;
			} else {
				return "Your Gooogle account is not linked.";
			}
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		}
		
		return userNick;
	}
	
	private String getUpdates(Connection db, String from, Connection cache) {
		String sender = mysqlEscape(this.getSender(from));
		String userNick = null;
		Statement sql = null;
		Statement sql_c = null;
		ResultSet rs = null;	
		ResultSet rs_friends = null;
		String up[] = new String[10];
		String doing = null;
		String friend = null;
		String rt = "";
		String cacheTag;
		int cacheId;

		try {
			int now = (int) (System.currentTimeMillis() / 1000);
			int deadline = now - 60;
			cacheTag = sender + "_up";
			cacheId = cacheTag.hashCode();
			sql_c = cache.createStatement();
			rs = sql_c.executeQuery("SELECT DATA FROM CACHE WHERE ID = " + cacheId + " AND CREATED > " + deadline);
			if (rs.next()) {
				String r = rs.getString("data");
				rs.close(); rs = null;
				sql_c.close(); sql_c = null;
				return r;
			} else {
				sql = db.createStatement();
				rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_google_account = " + sender + " LIMIT 1");
				if (rs.next()) {
					int userId = rs.getInt("usr_id");
					userNick = rs.getString("usr_nick");
					rs_friends = sql.executeQuery("SELECT usr_nick, ing_doing FROM babel_user, babel_ing_update WHERE ing_uid = usr_id AND (usr_id IN (SELECT frd_fid FROM babel_friend WHERE frd_uid = " + userId + ") OR usr_id = " + userId + ") ORDER BY ing_created DESC LIMIT 10");
					int i = 0;
					while (rs_friends.next()) {
						friend = rs_friends.getString("usr_nick");
						doing = rs_friends.getString("ing_doing");
						up[i] = friend + ": " + doing + "\n";
						i++;
					}
					while (i > 0) {
						i--;
						rt = rt + up[i];
					}
					if (rs != null) {
						rs.close(); rs = null;
					}
					if (rs_friends != null) {
						rs_friends.close(); rs_friends = null;
					}
					if (sql != null) {
						sql.close(); sql = null;
					}
					rt = rt.trim();
					sql_c.executeUpdate("DELETE FROM CACHE WHERE ID = " + cacheId);
					sql_c.close(); sql_c = null;
					PreparedStatement p = this.cache.prepareStatement("INSERT INTO CACHE(ID, DATA, CREATED) VALUES(?, ?, ?)");
					p.setInt(1, cacheId);
					p.setString(2, rt);
					p.setInt(3, now);
					p.execute();
					p.close(); p = null;
					return rt;
				} else {
					if (rs != null) {
						rs.close(); rs = null;
					}
					if (sql != null) {
						sql.close(); sql = null;
					}
					return "Your Google account is not linked.";
				}
			}
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		} catch (Exception e) {
			System.err.println("Exception: " + e.getMessage());
		}
		return userNick;
	}
	
	private String getPublic(Connection db) {
		Statement sql = null;
		ResultSet rs = null;
		String up[] = new String[10];
		String doing = null;
		String member = null;
		String rt = "";

		try {
			sql = db.createStatement();
			rs = sql.executeQuery("SELECT usr_nick, ing_doing FROM babel_user, babel_ing_update WHERE ing_uid = usr_id ORDER BY ing_created DESC LIMIT 10");
			int i = 0;
			while (rs.next()) {
				member = rs.getString("usr_nick");
				doing = rs.getString("ing_doing");
				up[i] = member + ": " + doing + "\n";
				i++;
			}
			while (i > 0) {
				i--;
				rt = rt + up[i];
			}
			if (rs != null) {
				rs.close(); rs = null;
			}
			if (sql != null) {
				sql.close(); sql = null;
			}
			return rt.trim();
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		}
		return rt;
	}
	
	private String getMine(Connection db, String from) {
		String sender = mysqlEscape(this.getSender(from));
		String userNick = null;
		Statement sql = null;
		ResultSet rs = null;	
		ResultSet rs_me = null;
		String up[] = new String[10];
		String doing = null;
		String friend = null;
		String rt = "";

		try {
			sql = db.createStatement();
			rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_google_account = " + sender + " LIMIT 1");
			if (rs.next()) {
				int userId = rs.getInt("usr_id");
				userNick = rs.getString("usr_nick");
				rs_me = sql.executeQuery("SELECT ing_doing FROM babel_ing_update WHERE ing_uid = " + userId + " ORDER BY ing_created DESC LIMIT 10");
				int i = 0;
				while (rs_me.next()) {
					doing = rs_me.getString("ing_doing");
					up[i] = userNick + ": " + doing + "\n";
					i++;
				}
				while (i > 0) {
					i--;
					rt = rt + up[i];
				}
				if (rs != null) {
					rs.close(); rs = null;
				}
				if (rs_me != null) {
					rs_me.close(); rs_me = null;
				}
				if (sql != null) {
					sql.close(); sql = null;
				}
				return rt.trim();
			} else {
				if (rs != null) {
					rs.close(); rs = null;
				}
				if (sql != null) {
					sql.close(); sql = null;
				}
				return "Your Google account is not linked.";
			}
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		}
		return userNick;
	}
	
	private String revertIng(Connection db, String from) {
		String sender = mysqlEscape(this.getSender(from));
		String userNick = null;
		Statement sql = null;
		ResultSet rs = null;
		
		try {
			sql = db.createStatement();
			rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_google_account = " + sender + " LIMIT 1");
			if (rs.next()) {
				int userId = rs.getInt("usr_id");
				userNick = rs.getString("usr_nick");
				sql.executeUpdate("DELETE FROM babel_ing_update WHERE ing_uid = " + userId + " ORDER BY ing_created DESC LIMIT 1");
				if (rs != null) {
					rs.close(); rs = null;
				}
				if (sql != null) {
					sql.close(); sql = null;
				}
				return "Your last update has been erased.";
			} else {
				if (rs != null) {
					rs.close(); rs = null;
				}
				if (sql != null) {
					sql.close(); sql = null;
				}
				return "Your Google account is not linked.";
			}
		} catch (SQLException se) {
			System.err.println("SQLException: " + se.getMessage());
		}
		
		return userNick;
	}
	
	private String writeIng(Connection db, String from, String ing) {
		String sender = mysqlEscape(this.getSender(from));
		String ing_sql = mysqlEscape(ing);
		String userNick = null;
		Statement sql = null;
		ResultSet rs = null;
		
		if (ing_sql.length() > 131) {
			return "Your input is too long. :(";
		} else {
			try {
				sql = db.createStatement();
				rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_google_account = " + sender + " LIMIT 1");
				if (rs.next()) {
					int userId = rs.getInt("usr_id");
					userNick = rs.getString("usr_nick");
					int source = 3;
					if (from.endsWith("/iChat")) {
						source = 4;
					} else if (from.indexOf("/Adium") > 0) {
						source = 5;
					} else if (from.indexOf("/gmail") > 0) {
						source = 6;
					}
					sql.executeUpdate("INSERT INTO babel_ing_update (ing_uid, ing_doing, ing_source, ing_created) VALUES(" + userId + ", " + ing_sql + ", " + source + ", UNIX_TIMESTAMP())");
					if (rs != null) {
						rs.close(); rs = null;
					}
					if (sql != null) {
						sql.close(); sql = null;
					}
					return "Got it.";
				} else {
					if (rs != null) {
						rs.close(); rs = null;
					}
					if (sql != null) {
						sql.close(); sql = null;
					}
					return "Your Google account is not linked.";
				}
			} catch (SQLException se) {
				System.err.println("SQLException: " + se.getMessage());
			}
		}
		
		return userNick;
	}
	
	private String makeLink(String command, Connection db, String from) {
		String sender = mysqlEscape(this.getSender(from));
		Statement sql = null;
		ResultSet rs = null;
		if (command.length() > 6) {
			String token = command.substring(6);
			try {
				PatternCompiler compiler = new Perl5Compiler();
				PatternMatcher matcher  = new Perl5Matcher();
				Pattern pattern = compiler.compile("^([0-9]+) (.+)$");
				if (matcher.contains(token, pattern)) {
					MatchResult result = matcher.getMatch();
					String uid = result.group(1);
					String password = ShaUtil.toSha1String(result.group(2));
					sql = db.createStatement();
					rs = sql.executeQuery("SELECT usr_id, usr_nick FROM babel_user WHERE usr_id = " + uid + " AND usr_password = '" + password + "'");
					if (rs.next()) {
						String nick = rs.getString("usr_nick");
						rs.close(); rs = null;
						sql.executeUpdate("UPDATE babel_user SET usr_google_account = '' WHERE usr_google_account = " + sender);
						sql.executeUpdate("UPDATE babel_user SET usr_google_account = " + sender + " WHERE usr_id = " + uid);
						sql.close(); sql = null;
						return "Your Google account is linked with: " + nick + " (#" + uid + " member)";
					} else {
						rs.close(); rs = null;
						return "Your submitted password or user ID is incorrect.";
					}
				} else {
					return token + "\n\nYour input format is incorrect.";
				}
			} catch (Exception e) {
				System.err.println("Exception: " + e.getMessage());
				return "Your input format is incorrect.";
			}
		} else {
			return "Your input format is incorrect.";
		}
	}
	
	private String searchPHP(String command) {
		if (command.length() > 4) {
			String token = command.substring(4).toLowerCase().trim();
			try {
				Analyzer analyzer = new SimpleAnalyzer();
				QueryParser q = new QueryParser("anchor", analyzer);
				Query query = q.parse(token);
				Hits hits = searcher.search(query);
				String rt = "";
				int i;
				rt = hits.length() + " documents matched for *" + token + "*:\n\n";
				int max = 10;
				if (hits.length() < 10) {
					max = hits.length();
				}
				for (i = 0; i < max; i++) {
					Document doc = hits.doc(i);
					rt = rt + doc.get("title") + "\n";
					rt = rt + doc.get("url") + "\n\n";
				}
				rt = rt.trim();
				return rt;
			} catch (Exception e) {
				System.err.println("Exception: " + e.getMessage());
				return "0 documents matched.";
			}
		} else {
			return "Please specify a term for searching.";
		}
	}
	
	public final static String mysqlEscape(String s) {
		if (s == null)
			return "''";
		StringBuffer buffer = new StringBuffer();
		buffer.append('\'');
		int length = s.length();
		for (int i = 0; i < length; i++) {
			char ch=s.charAt(i);
			if (ch=='\'' || ch=='\\')
			buffer.append('\\');
			buffer.append(ch);
		}
		buffer.append('\'');
		return buffer.toString();
	}
	
	public final static String hsqldbEscape(String s) {
		if (s == null)
			return "''";
		StringBuffer buffer = new StringBuffer();
		buffer.append('\'');
		int length = s.length();
		for (int i = 0; i < length; i++) {
			char ch=s.charAt(i);
			if (ch=='\'')
			buffer.append('\'');
			buffer.append(ch);
		}
		buffer.append('\'');
		return buffer.toString();
	}
}