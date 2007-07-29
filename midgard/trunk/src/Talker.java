package com.v2ex.midgard;

import org.jivesoftware.smack.XMPPConnection;
import org.jivesoftware.smack.XMPPException;
import org.jivesoftware.smack.packet.XMPPError;
import org.jivesoftware.smack.MessageListener;
import org.jivesoftware.smack.packet.Presence.Type.*;
import org.jivesoftware.smack.packet.Message;
import org.jivesoftware.smack.packet.Packet;
import org.jivesoftware.smack.packet.Presence;
import org.jivesoftware.smack.PacketCollector;
import org.jivesoftware.smack.Chat;
import org.jivesoftware.smack.filter.*;

import org.apache.commons.cli.Options;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;

import java.sql.Connection;
import java.sql.Statement;
import java.sql.ResultSet;
import java.sql.DriverManager;
import java.sql.SQLException;

import java.io.*;

import java.util.Properties;

class Talker {
	public String mode;
	private Connection db;
	private XMPPConnection xmpp;
	private Connection cache;
	private Log log = LogFactory.getLog("com.v2ex.midgard.Talker");
	
	Talker() {
		log.info("Starting");
	}

	public static void main(String[] args) {
		try {
			Talker t = new Talker();
			t.init();
			t.run();
		} catch (Exception e) {
			e.printStackTrace();
		}
	}
	
	public void init() {
		try {
			log.info("Loading configuration");
			Properties p = new Properties();
			p.loadFromXML(new FileInputStream("conf/config.xml"));
			
			String mode = p.getProperty("mode");
			this.mode = mode;
			log.info("Message processor mode is set to <" + mode + ">");
			
			log.info("Connecting to database server");
			String dbType = p.getProperty("dbType");
			log.info("Database type is set to <" + dbType + ">");
			String dbName = p.getProperty("dbName");
			String dbServer = p.getProperty("dbServer");
			String dbPort = p.getProperty("dbPort");
			String dbUsername = p.getProperty("dbUsername");
			String dbPassword = p.getProperty("dbPassword");
			String dbSchemata = p.getProperty("dbSchemata");
			String dbEnable = p.getProperty("dbEnable");
			String cacheRepo = p.getProperty("cacheRepo");
			if (dbEnable.equals("yes")) {
				if (dbType.equals("mysql")) {
					Class.forName("com.mysql.jdbc.Driver").newInstance();
					this.db = DriverManager.getConnection("jdbc:mysql://" + dbServer + ":" + dbPort + "/" + dbSchemata + "?" + "user=" + dbUsername +  "&password=" + dbPassword + "&useUnicode=true&characterEncoding=UTF-8&characterSetResults=UTF-8");
				} else if (dbType.equals("derby")) {
					Class.forName("org.apache.derby.jdbc.EmbeddedDriver").newInstance();
					this.db = DriverManager.getConnection("jdbc:derby:" + dbName + ";create=true");
					this.db.setAutoCommit(false);
				} else {
					log.error("Database type <" + dbType + "> is not supported");
				}
				log.info("Database connection established");
			} else {
				log.info("Database not enabled");
			}
			log.info("Connecting to XMPP server");
			String xmppServer = p.getProperty("xmppServer");
			String xmppUsername = p.getProperty("xmppUsername");
			String xmppPassword = p.getProperty("xmppPassword");
			String xmppStatus = p.getProperty("xmppStatus");
			this.xmpp = new XMPPConnection(xmppServer);
			this.xmpp.connect();
			this.xmpp.login(xmppUsername, xmppPassword);
			log.info("XMPP connection established");
			log.info("Sending presence to XMPP server");
			Presence presence = new Presence(Presence.Type.available);
			presence.setStatus(xmppStatus);
			this.xmpp.sendPacket(presence);
			log.info("Presence sent to XMPP server");
			log.info("Ready for incoming packets");
			Class.forName("org.hsqldb.jdbcDriver").newInstance();
			this.cache = DriverManager.getConnection("jdbc:hsqldb:file:" + cacheRepo, "sa", "");
		} catch (XMPPException xe) {
			xe.printStackTrace();
		} catch (Exception e) {
			e.printStackTrace();
		} 
	}
	
	public void run() {
		try {
			if (this.mode.equals("v2ex")) {
				PacketFilter myFilter = new PacketFilter() {
					public boolean accept(Packet packet) {
						return true;
					}
				};
				
				PacketCollector collector = this.xmpp.createPacketCollector(myFilter);
			
				V2EXProcessor processor = new V2EXProcessor(this.db, this.xmpp, this.cache);
				while (true) {
					Packet packet = collector.nextResult();
					processor.processMessage(packet);
				}
			} else if (this.mode.equals("avalon")) {
				PacketFilter myFilter = new PacketFilter() {
					public boolean accept(Packet packet) {
						return true;
					}
				};
				
				PacketCollector collector = this.xmpp.createPacketCollector(myFilter);
			
				AvalonProcessor processor = new AvalonProcessor(this.xmpp);
				while (true) {
					Packet packet = collector.nextResult();
					processor.processMessage(packet);
				}
			} else {
				log.error("Message processor mode <" + this.mode + "> not supported");
			}		
		} catch (Exception e) {
			e.printStackTrace();
		} 
	}
	
	public static Options buildOptions() {
		Options options = new Options();
		return options;
	}
}