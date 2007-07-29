package com.v2ex.midgard;
import org.jivesoftware.smack.packet.Packet;

interface Processor {
	public void processMessage(Packet packet);

	public String getVersion();
	public String getHelp();
	public String getSender(String from);
}