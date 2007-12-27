package com.v2ex.midgard;

import java.io.*;

import java.util.Properties;

class Configuration {
	public static final String get(String c)  {
		String v = "";
		try {
			Properties p = new Properties();
			p.loadFromXML(new FileInputStream("conf/config.xml"));
			v = p.getProperty(c);
		} catch (Exception e) {
			System.err.println("Exception: " + e.getMessage());
		}
		return v;
	}
}