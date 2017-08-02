=== Plugin Name ===
Contributors: drobersorg
Tags: government, legislation, open states, politics, find state legislators, legislator, legislature, state, vote
Requires at least: 4.3
Tested up to: 4.4.2
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A state legislative tracking system for activist organizations.

== Description ==

A state legislative tracking system for activist organizations. 

This Wordpress plugin uses data from the Open States API from the Sunlight Foundation as well as the Google Maps API to create a legislative lookup and bill tracking tool for Wordpress websites.

== Installation ==

1. Go to Plugins > Add New
1. Click the Upload Plugin button
1. Select wp-legisearch.zip
1. Activate the plugin
1. Go to Legisearch > Settings to set up the API keys
1. Use [legisearch_addresslookup] shortcode to create an address form for looking up legislators
1. Use [legisearch_chamberlink state="<state>" chamber="upper"] or [legisearch_chamberlink state="<state>" chamber="lower"] to display a link to the <state>'s upper and lower legislative chambers. Replace <state> with the two-letter postal code for the state.
1. See [Plugin homepage](http://droberts.us/legisearch/) for details on setting up API Keys and customizing

== Frequently Asked Questions ==

= The information on a legislator or bill is incorrect. How do I fix it? =

All legislative information is pulled from the [Open States API](http://openstates.org) and temporarily cached in your database. If you find that information is incorrect, you should first try clearing the cache in the Settings page. If the information is still incorrect, you may need to wait for the Sunlight Foundation to correct the data.

= I get an error on the geo result page that says my API key is invalid =

You not only need a Google Maps API key, you also need to enable the Google Maps Javascript API. In the Developers Console, go to your project. In the dashboard of your project, you will see a "Use Google APIs" box. Click on the "Enable and manage APIs" link. On the new page, click "Google Maps Javascript API" and enable. Do the same for the "Google Maps Geocoding API."

== Screenshots ==

1. Settings page. You must at least register a Sunlight Foundation API Key (free) in order for this plugin to work properly.
2. Search for existing state legislation from any state over several years
3. The `All Bills` page shows all legislation you are tracking
4. Select which votes to show to your visitors
5. Simple shortcode allows users to find their state legilsators based on address or you can present them links to specific legislators or an entire Senate or State Assembly.
6. This is what the user sees after they search by address
7. Legislator page showing contct information and voting record for votes you are tracking
8. It is simple to show an entire legislative chamber such as the State Senate or Assembly

== Changelog ==

= 1.3.1 =
* Fixed a bug related to Open States caching that made processing super slow

= 1.3 =
* Added preference tracking for votes

= 1.2 =
* API Keys will now save when updating or reactivating plugin
* Improved caching of Open States data
* Added sponsorship support

= 1.1 =
* Updated the title and content filters because some themes weren't handling them properly.
* Improved handling of apostrophes and quotes for bill and vote descriptions.

= 1.0 =
* Initial stable release.
