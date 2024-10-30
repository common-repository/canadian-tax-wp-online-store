=== Canadian Tax for WP Online Store ===
Contributors: martinhurford,dandelionweb
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=UBP3HJ2EDDP3L&lc=CA&item_name=Martin%20Hurford%20%28WordPress%20Plugin%29&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: WP online Store, Canadian Tax, Ecommerce, HST, PST, GST, osCommerce
Requires at least: 3.0
Tested up to: 3.8
Stable tag: trunk
Version: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sets up Canadian Tax Zones and Rates, sets the default currency, store country and shipping country for the WP Online Store eCommerce Plugin.

== Description ==

This plugin works with the FREE WPonlineStore WordPress eCommerce plugin. If you’re a Canadian shop one of the most challenging parts of the setup process will likely be to get the taxes setup correctly. You need to charge GST to some Provinces, HST to others, with NS & PEI having a different HST%. If you are in a Province that charges PST you’ll also have to charge PST within your Province.

The Canadian Tax plugin will automatically set up Canadian Federal and Provincial Sales Tax Zones and Rates, set the default currency, store country and shipping country!  All you do is follow steps 4 & 5 in the installation instructions to select your Province.

== Installation ==

1. Download and install the WP online Store plugin - http://wordpress.org/plugins/wp-online-store/
2. Upload canadian-tax-for-wp-online-store folder to the /wp-content/plugins directory
3. Activate the plugin through the Plugins menu in WordPress - The plugin sets up Canadian Federal and Provincial Sales Tax Zones and Rates, sets the default currency to CAD, sets the store country and shipping country to Canada 
4. You need to set your store zone for your Province. Click WP Online Store from admin panel -> My Store -> Set Zone to the Province your store is located in.
5. From within the WP Online Store admin screen click on Locations & Taxes -> Tax Zones  - delete the four PST Tax Rates that apply to other Provinces (don't delete a Province if your store is located there). See screenshot

== Frequently Asked Questions ==

= Don't I just set HST for all Provinces? = 

You need to charge GST to some Provinces, HST to others, with BC and NS having a different HST%. If you are in a Province that charges PST you’ll also have to charge PST within your Province.  Please visit: http://dandelionwebdesign.com/canadian-taxes/ for full instructions.

Rates are based on this table - http://www.cra-arc.gc.ca/tx/bsnss/tpcs/gst-tps/rts-eng.html 
and this infographic - http://www.iglobalexports.com/internationalblog/2012/02/03/infographic-canadian-provincial-taxes-canada-province-tax-rates/

= What Tax Zones will be created? =

* Canada GST
* Canada HST
* NS HST
* PEI HST
* PST BC
* PST MB
* PST QC
* PST SK

= What Tax Rates will be created? =

* Canada GST Zone 5%
* Canada HST Zone 13%
* NS HST 15%
* PEI HST 14%

= Which Provices charge GST? =
* Alberta
* British Columbia
* Saskatchewan
* Yukon Territory
* Manitoba
* Northwest Territories
* Nunavut
* Quebec

= Which Provinces charge HST? =
* New Brunswick 13%
* Newfoundland & Labrador 13%
* Ontario 13%
* NS HST 15%
* PEI HST 14%

= Which Provinces charge PST? =

If your shop is located in one of these Provinces you need to charge Provincial PST. If not please delete all classes that don't apply to your shop.

* British Columbia 7%
* Manitoba 7%
* Quebec 9.975%
* Saskatchewan 5%

== Screenshots ==

1. infographic - source: http://www.iglobalexports.com/internationalblog/2012/02/03/infographic-canadian-provincial-taxes-canada-province-tax-rates/
2. in WP Online Store set store zone under My Store
3. delete any of the 4 PST zones that don't apply to your store location. Only keep your local Province.

== Upgrade Notice ==
= 2 =
 * 01-29-2014 - v2.0 - fix by Dandelion Web Design currency to CAD (was CDN in error),
 * update tax rates that changed April 2013
 * created readme.txt file
 * release to WordPress repository
= 1 =
 * 11-05-2012 - v1.0 - Initial version Mindtripz.com


== Changelog ==
= 2 =
 * 01-29-2014 - v2.0 - fix by Dandelion Web Design currency to CAD (was CDN in error),
 * update tax rates that changed April 2013
 * created readme.txt file
 * release to WordPress repository
= 1 =
 * 11-05-2012 - v1.0 - Initial version Mindtripz.com
