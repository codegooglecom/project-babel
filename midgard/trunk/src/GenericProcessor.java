package com.v2ex.midgard;

import org.jivesoftware.smack.XMPPConnection;
import org.jivesoftware.smack.packet.Message;
import org.jivesoftware.smack.packet.Packet;
import java.sql.Connection;

abstract class GenericProcessor implements Processor {
	public void processMessage(Packet packet) {
	}
	
	public String getVersion() {
		String version = new String("$Id: GenericProcessor.java 4 2007-04-05 11:33:13Z livid $");
		return version;
	}
	
	public String getHelp() {
		String help = new String("Available commands:\n\n/help - Print usage information\n/version - Print version information");
		return help;
	}
	
	public String getSender(String from) {
		String sender = null;
		sender = from.substring(0, from.indexOf(47));
		return sender;
	}
}