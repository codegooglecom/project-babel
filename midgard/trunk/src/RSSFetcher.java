package com.v2ex.midgard;

import java.net.URL;
import java.io.InputStreamReader;
import com.sun.syndication.feed.synd.SyndFeed;
import com.sun.syndication.io.SyndFeedInput;
import com.sun.syndication.io.XmlReader;

class RSSFetcher {
	public static String getFeed(String feedURL) {
		String o = "";
		try {
			URL dest = new URL(feedURL);
			
			SyndFeedInput input = new SyndFeedInput();
			SyndFeed feed = input.build(new XmlReader(dest));
			
			o = feed.toString();
		} catch (Exception e) {
			e.printStackTrace();
			return "Exception: " + e.getMessage();
		}
		return o;
	}
}