package com.v2ex.midgard;

import org.jivesoftware.smack.XMPPConnection;
import org.jivesoftware.smack.XMPPException;
import org.jivesoftware.smack.MessageListener;
import org.jivesoftware.smack.packet.Packet;
import org.jivesoftware.smack.packet.Message;
import org.jivesoftware.smack.Chat;
import org.apache.commons.httpclient.HttpClient;
import org.apache.commons.httpclient.HttpException;
import org.apache.commons.httpclient.NameValuePair;
import org.apache.commons.httpclient.methods.PostMethod;
import org.apache.commons.httpclient.util.URIUtil;
import org.apache.commons.logging.LogFactory;
import org.apache.commons.codec.DecoderException;
import java.sql.Connection;
import java.sql.SQLException;
import java.sql.ResultSet;
import java.sql.Statement;
import java.util.Date;

/**
 * 
 * @author Livid
 */

class AvalonProcessor extends GenericProcessor {
	public XMPPConnection xmpp = null;

	AvalonProcessor(XMPPConnection xmppInput) {
		xmpp = xmppInput;
	}
	
	public void processMessage(Packet packet) {
		try {
			if (packet instanceof Message) {
				Message msg = (Message) packet;
				// Process incoming message:
				if (msg.getBody() != null) {
					byte[] utf8 = msg.getBody().getBytes("UTF-8");
					String msgBody = new String(utf8, "UTF-8");
					System.out.println("(From: " + msg.getFrom() + ") - " + msgBody);
					Chat chat = this.xmpp.getChatManager().createChat(msg.getFrom(), new MessageListener() {
						public void processMessage(Chat chat, Message message) {
						}
					});
					String ls = new String(msg.getBody().toLowerCase());
					String sender = this.getSender(msg.getFrom());
					if (sender.equals("admin.ae@gmail.com") || sender.equals("v2ex.livid@gmail.com")) {
						if (ls.startsWith("/version")) {
							chat.sendMessage(this.getVersion());
						} else if (ls.equals("?")) {
							chat.sendMessage(this.getHelp());
						} else if (ls.startsWith("/help")) {
							chat.sendMessage(this.getHelp());
						} else {
							chat.sendMessage(this.writeEntry(msgBody));
						}
					} else {
						chat.sendMessage("It's none of your business here.");
					}
				}
			}
		} catch (XMPPException xe) {
			xe.printStackTrace();
		} catch (Exception e) {
			System.err.println("Exception: " + e.getMessage());
		}
	}
	
	public String getHelp() {
		String help = new String("Avalon commands:\n\nWrite anything and enjoy it!");
		return help;
	}
	
	public String getVersion() {
		String version = "Avalon/0.0.1 (c) Aether & Livid\nV2EX Labs | software for internet";
		return version;
	}
	
	public String writeEntry(String body) {
		try {
			HttpClient client = new HttpClient();
		
			String api = "http://www.woooh.com/api";
		
			PostMethod method = new PostMethod(api);
			
			method.setParameter("m", "new");
			method.setParameter("clip", URIUtil.encodeAll(body, "UTF-8"));
			method.setParameter("key", ""); // API Key of Avalon installation
			
			client.executeMethod(method);
			String response = method.getResponseBodyAsString();
			method.releaseConnection();
			method = null;
			client = null;
			return response;
		} catch (Exception e) {
			System.err.println("Exception: " + e.getMessage());
		}
		
		return "Unknown exception";
	}
}