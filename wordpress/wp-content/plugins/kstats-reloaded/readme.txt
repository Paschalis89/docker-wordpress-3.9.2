=== Plugin Name ===
Contributors: mwaterous
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9847234
Tags: admin, traffic, stats, statistics, visits, visitors, pageviews, referrer, agents, analysis, spiders, hits
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 0.7.4

Use kStats to collect real time statistics and aggregated data for your Wordpress site. Provides a detailed overview of visitors, pageviews, referring web sites and more.


== Description ==

kStats Reloaded began as a project to redesign StatPress Reloaded into a much faster and more up to date statistics plugin
for Wordpress. The original StatPress Reloaded is an amazing plugin, and can be found
<a href="http://wordpress.org/extend/plugins/statpress-reloaded/">here</a>.

Using kStats you can instantly see who's coming to your site, where they are coming from, be it another web site or one
of the many popular search engines, and much more. See up to the minute information on how many unique visitors you've
gotten, how many pageviews they've resulted in, including information on which crawlers are visiting and indexing your
Wordpress site.

kStats is much faster than many of its predecessors due to the fact that it takes a completely new approach to how
it stores and maintains information. Using a slightly more complex database schema and running a daily aggregate of
information allows it to reduce its SQL interaction by literally hundreds of queries.

<strong>This plugin is currently in its initial beta release</strong> &#8212; <em>it is meant for beta testing only, and not intended for use
on a live web site.</em>


== Installation ==

Installation of kStats Reloaded is fairly easy;

1. After unpacking the archive, upload the entire `/kstats-reloaded/` folder to your `/wp-content/plugins/` directory
1. All of the .ini files in the packages `/definitions/` folder need to be made writable (chmod 0777)
1. Navigate to the Plugins page of your Wordpress Dashboard and activate kStats-Reloaded
1. Watch the statistics start building immediately!

** Please note that installation of kStats Reloaded requires PHP 5. MySQL 5.0.3+ is recommended but not required.


== Screenshots ==

1. The kStats main overview interface, showing your totals, an accompanying chart, and the most recent hits
2. Top 20 Charts, displaying aggregated information about search terms, spiders, referrers and user agents.
3. kStats Options page, configuration settings, convert StatPress raw data, and more...
4. The kStats dashboard widget. This can be turned on or off via the kStats Options page.


== Changelog ==

= 0.7.4 =
* Fixed bug in range validation
* Fixed bug with the charts on new installs only displaying single days.

= 0.7.1 =
* Added asynchronous http request to run nightly aggregate as a background process
* Added opt-in program for submitting unknown robots to user-agent-string.info
* Added scheduler configuration, you can now set the aggregate to run daily, twice daily or hourly
* Fixed a vulnerability with referrer/search terms data

= 0.6.9 =
* Major bugfix release, includes improvements to the aggregate utilities
* Updated query.ini to include new google queries
* Updated search terms trap method
* The bar graph has been snazzified

= 0.6.2 =
* Fixed a bug in the StatPress conversion utility

= 0.6.1 =
* Added ignore\_user\_abort to nightly cleanup

= 0.6.0 =
* Updated database structure to optimize tables for performance
* Implemented new file system structure
* Access levels and Utilities sub-system added to Options
* Added new uninstall feature, altered deactivation hook in favor of uninstall hook
* Began adding i18n support

= 0.5.0 =
* Addition of the sidebar widget
* Fixed a few minor datetime bugs
* Completed integration of user-agent-string.info API into kStats core

= 0.4.6 =
* Interface updates, tabs are out, pages are in.
* Improvements to the upgrade engine.
* Tonnes of little things I did during my oink oink flu stupor that I forgot to document.

= 0.4.2 =
* Minor SQL bugfix

= 0.4.1 =
* Rebuilt the StatPress converter, and integrated it
* Added an integrated upgrade process
* Updates and bugfixes for both SQL queries and aggregate library

= 0.2.5 =
* Updates and bugfixes mainly focusing on the aggregate library

= 0.1.0 =
* Initial beta release


== kStats Widget ==

Since version 0.5.0, kStats includes the option to display a portion of your statistics publicly on your blog via a sidebar widget.

The widget operates just like a regular text widget; you can place as many on your blog as you want, in any sidebar you have activated on your theme. The formatting is
completely under your control, so feel free to make it look however you want to suit your blog. kStats will simply interpret any of the inserted macro codes that you choose
to use and replace them with the appropriate information.

You can display your all-time totals, this months, todays, or yesterdays for any of the fields currently displayed on your statistics page (visitors, pageviews, spiders and
feeds). In addition you are also able to include information such as visitors currently online, or data about the visitor currently viewing your page, including their IP,
OS, or Browser.


== Converting From StatPress Reloaded ==

Included in the archive is a conversion utility for StatPress Reloaded so that you don't have to lose all the stats
you've already recorded. This has been tested on StatPress v1.5.21, and is available from your plugins Options page.

Don't worry about losing any of your data either if you choose not to use kStats. The conversion utility works from
a temporary table, and does not affect your original StatPress data in any way.
