=== WP Maltor ===
Contributors: davidmerinas, miguel.arroyo
Donate link: http://957.be/donatedavidmerinas
Tags: tor, malicious, block traffic
Requires at least: 3.3
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin blocks traffic from malicious IP and Tor Network

== Description ==

This plugin "hides" the WordPress site if traffic is coming from malicious IP or Tor Network (is selected). IP list is updated every 30 minutes from http://iprep.wpmaltor.com/ and http://torlist.wpmaltor.com/ which are using 2 free services: https://www.dan.me.uk/torlist/ for Tor Exit Nodes and http://myip.ms/files/blacklist/csf/latest_blacklist.txt for malicious IP list.

## **When a malicious IP is detected the plugin will act in one of these ways:**
* Blank page
* Redirection
* 404 Error Page
* Default (MalTor Logo)

== Installation ==

1. Uncompress the file and upload the folder wp-maltor to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to options page and select how the plugin will act when a malicious IP is detected and if Tor IPs are blocked too.

== Frequently asked questions ==
Not yet


== Screenshots ==
1. screenshot-1.png


== Changelog ==

= 0.1.5 =
*Now you can select if Tor is blocked or not. Malicious IPs are blocked by default.

= 0.1.4 =
*New URL to download IP list

= 0.1.3 =
*Fixed some problems determining real IP

= 0.1.2 =
*Fixed some problems identifying real IP

= 0.1.1 =
*Now you can select the plugin behavior.

= 0.1 =
*First stable version

== Upgrade notice ==
You may need to reactivate plugin through options page
