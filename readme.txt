=== TwitFeed ===
Contributors: ahsile
Tags: twitter, stream
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: 0.0.3

Connect to the twitter stream and feed live twets to your blog.

== Description ==

Demo: http://zenwerx.com/projects/twitfeed/

This plugin can be used to connect to the twitter stream and feed your blog 
with content in real time. There are three options for how you can receive
data from twitter: Follow Users, Follow Track, and Sample.

Following users works by subscribing to user's twitter feeds and taking any
tweets they may have made. Using the "track" method allows you to enter
certain keywords visitors to your blog may be interested in. Any tweets
containing the tracked words you enter will be displayed. Sample mode is mostly
for making sure your setup is working correctly. This takes a sample of all
tweets (10-15 a second) and displays them. To keep the traffic down the plugin
is set to take only 1/50th of sample tweets (about 1 every 5 seconds).

= Author =
Michael Carpenter
http://zenwerx.com/


== Installation ==

Requirements:

* Memcache + php5-memcache
* php5-curl
* Phirehose (included, with some minor changes )
* jQuery must be included in your blog

Installation:

Just uncompress to your plugins folder and make sure the requirements are met.
In the wp admin menu enable TwitFeed, and set the options for your account.

You WILL need to change some options in the daemon and consumer scripts.
There are four settings with need to change in BOTH scripts:

* appRunAsGID (default 1000)
  * This is the group id the daemon will run under. Set to a valid group id.
* appRunAsUID (default 1000)
  * Similar to above, user id instead of group. Set to a valid user id.
* logLocation (default /var/log/{OPTIONS.appName}.log)
  * Where the log will be written.
  * The user above must be able to create a file here, and write to the file.
* appPidLocation (default /var/run/[daemon]/[daemon].pid)
  * Process id tracking file
 * User must be able to create and write to files here.

Running:

From a shell run: 
	./daemon
	./consumer

The daemon script connects to twitter and stores tweets in memcache.
The consumer script reads the data from memcache and inserts into the db.

This is a two step process in order to make sure than the daemon does not
fall behind if the feed and database are busy.

If you want to write an init script you can do:
	./daemon --write-initd
	./consumer --write-initd

This will create scripts in /etc/init.d which you can use to start and stop 
the daemons. You can also create startup scripts so the daemons start if
you reboot, but I won't get in to that. Look up the details on the PEAR
System_Daemon class if you wish to do this. Also, this only works for
Debian and Ubuntu based systems as far as I can tell.

== Changelog ==

= 0.0.2 =
* Removed inline css
* Fix some mysql issues
* Add some default options in the daemons (see below)
* Add System/Daemon to package (no more need for PEAR)

= 0.0.1 =
* Initial release
