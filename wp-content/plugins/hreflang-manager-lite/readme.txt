=== Hreflang Manager - Hreflang Implementation for International SEO ===
Contributors: DAEXT
Tags: hreflang, seo, language, internationalization, multilingual
Donate link: https://daext.com
Requires at least: 4.0
Tested up to: 6.8
Requires PHP: 5.2
Stable tag: 1.12
License: GPLv3

The Hreflang Manager plugin provides you an easy and reliable method to implement hreflang in WordPress.

== Description ==
The Hreflang Manager plugin provides you an easy and reliable method to implement hreflang in WordPress.

For more information on the technical use of hreflang, please consider reading the [official documentation provided by Google](https://developers.google.com/search/docs/advanced/crawling/localized-versions).

### Pro Version
A [Pro Version](https://daext.com/hreflang-manager/) of this plugin is available on our website with many additional features, like the ability to move the hreflang implementation in all the websites of the network, a maximum of 100 alternative versions of the page per connection, the ability to mass import hreflang data from a spreadsheet, and much more.

### Features
* Supports the hreflang implementation of different websites or the sub-sites of a WordPress network
* Supports all the languages defined with [ISO_639-1](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes)
* Supports all the scripts defined with [ISO 15924](https://en.wikipedia.org/wiki/ISO_15924)
* Supports all the countries defined with [ISO 3166-1 alpha-2](https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)
* A maximum of 10 alternative versions of the page per connection
* Includes a log system to verify the correct implementation in the front-end
* Ability to select the default languages, scripts, and countries
* Automatically deletes the hreflang data of the deleted posts

### Credits
This plugin makes use of the following resources:

* [Select2](https://select2.org/) licensed under the MIT License

For each library you can find the actual copy of the license inside the folder used to store the library files.

== Installation ==
= Installation (Single Site) =

With this procedure you will be able to install the Hreflang Manager plugin on your WordPress website:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Activate Plugin**

= Installation (Multisite) =

This plugin supports both a **Network Activation** (the plugin will be activated on all the sites of your WordPress Network) and a **Single Site Activation** in a **WordPress Network** environment (your plugin will be activate on single site of the network).

With this procedure you will be able to perform a **Network Activation**:

1. Visit the **Plugins -> Add New** menu
2. Click on the **Upload Plugin** button and select the zip file you just downloaded
3. Click on **Install Now**
4. Click on **Network Activate**

With this procedure you will be able to perform a **Single Site Activation** in a **WordPress Network** environment:

1. Visit the specific site of the **WordPress Network** where you want to install the plugin
2. Visit the **Plugins** menu
3. Click on the **Activate** button (just below the name of the plugin)

== Changelog ==

= 1.12 =

*April 16, 2025*

* Fixed PHP notice caused by early use of translation functions.

= 1.11 =

*November 29, 2024*

* Resolved CSS style issue.
* The load_plugin_textdomain() function now runs with the correct hook.

= 1.10 =

*June 14, 2024*

* In the "Connections" menu the URLs max length is now properly set to 2083 characters.
* The PHP trim() function has been added to the "Connections" menu to remove any leading or trailing white spaces from the entered URLs.

= 1.09 =

*May 23, 2024*

* Major back-end UI update.
* Refactoring.

= 1.08 =

*April 7, 2024*

* Fixed a bug (started with WordPress version 6.5) that prevented the creation of the plugin database tables and the initialization of the plugin database options during the plugin activation.

= 1.07 =

*October 25, 2023*

* Nonce fields have been added to the "Connections" menus.
* General refactoring. The phpcs "WordPress-Core" ruleset has been partially applied to the plugin code.

= 1.06 =

*February 8, 2023*

* The "Auto Alternate Pages" option has been added.
* Footer links have been added to all the plugin menus.
* Minor backend improvements.

= 1.05 =

*July 31, 2022*

* The text domain has been changed to match the plugin slug.
* Changelog added.
* All the dismissible notices are now generated in the "Connections" menu.
* Updated the description of the features in the "Pro Version" menu.
* The "Export to Pro" menu has been added.
* Minor backend improvements.

= 1.04 =

*February 11, 2022*

* The correct ISO 3166-1 alpha-2 code is now used for Lebanon.

= 1.03 =

*December 30, 2021*

* Minor backend improvements.

= 1.01 =

*March 17, 2021*

* Minor backend improvements.
* Bug fix.

= 1.00 =

*March 17, 2021*

* Initial release.

== Screenshots ==
1. Connections menu
2. Hreflang implementation of a single URL
3. Options menu in the "General" tab
4. Options menu in the "Defaults" tab