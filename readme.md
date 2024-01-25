## {eac}Doojigger Readme Extension for WordPress  
[![eacDoojigger](https://img.shields.io/badge/Requires-{eac}Doojigger-da821d)](https://eacDoojigger.earthasylum.com/)  
Plugin URI:         https://eacdoojigger.earthasylum.com/eacreadme/  
Author:             [EarthAsylum Consulting](https://www.earthasylum.com)  
Stable tag:         1.2.6  
Last Updated:       24-Jan-2024  
Requires at least:  5.5.0  
Tested up to:       6.4  
Requires PHP:       7.2  
Requires EAC:       2.0  
Contributors:       [kevinburkholder](https://profiles.wordpress.org/kevinburkholder)  
License:            GPLv3 or later  
License URI:        https://www.gnu.org/licenses/gpl.html  
Tags:               readme, parser, markdown, parsedown, {eac}Doojigger, post from readme, code highlighting, readme.txt, shortcode  
WordPress URI:		https://wordpress.org/plugins/eacreadme  

**_{eac}Readme loads and translates a WordPress markdown 'readme.txt' file providing shortcodes to access header lines and section blocks._**

### Description

_{eac}Readme_ is an [{eac}Doojigger](https://eacDoojigger.earthasylum.com/) extension which loads and translates a WordPress markdown 'readme.txt' file providing shortcodes to access header lines and section blocks.

#### Shortcode Usage

The first used shortcode must indicate the file to load...

    [eacReadme file='/docfolder/readme.txt']        # file is relative to the WordPress document root folder
    [eacReadme content='/contentfolder/readme.txt'] # content file is relative to the WordPress content folder (wp-content/)
    [eacReadme plugin='/pluginfolder/readme.txt']   # plugin file is relative to the WordPress plugins folder (wp-content/plugins/)
    [eacReadme theme='/themefolder/readme.txt']     # theme file is relative to the WordPress themes folder (wp-content/themes/)

After which, headers and sections may be pulled from that file...

    [eacReadme]All Headers[/eacReadme]              # parses all header lines
    [eacReadme]headerName[/eacReadme]               # gets the value of the named header line

    [eacReadme]All Sections[/eacReadme]             # parses all section blocks
    [eacReadme]sectionName[/eacReadme]              # parses the content of the named section block
    [eacReadme]sectionName/sub-section[/eacReadme]  # parses the content of the named sub-section within section block

One shortcode can do it all...

    [eacReadme plugin='/pluginfolder/readme.txt']Document[/eacReadme]    # loads the file and parses the entire document

Or load the entire file as a single code block...

	[eacReadme theme='/themefolder/functions.php']Code File[/eacReadme]

#### Shortcode Examples

Get header values...

    [eacReadme]Contributors[/eacReadme]
    [eacReadme]Donate link[/eacReadme]
    [eacReadme]Requires at least[/eacReadme]
    [eacReadme]Stable tag[/eacReadme]

Get unnamed segments...

    [eacReadme]Title[/eacReadme]                    # gets the '=== plugin name ===' line (before headers)
    [eacReadme]Short Description[/eacReadme]        # gets the short description (between headers and first section block)

Get section blocks...

    [eacReadme]Description[/eacReadme]
    [eacReadme]Installation[/eacReadme]
    [eacReadme]Screenshots[/eacReadme]
    [eacReadme]Changelog[/eacReadme]

Get multiple blocks and/or sub-sections...

    [eacReadme plugin='/eacReadme/readme.txt']Short Description,Description[/eacReadme]
    [eacReadme plugin='/eacReadme/readme.txt']Short Description,Description/Shortcode Examples[/eacReadme]

Get a file as a code block...

	[eacReadme theme='/my-child-theme/functions.js' lang='js']Code File[/eacReadme]
	[eacReadme theme='/my-child-theme/style.css' lang='css']Code File[/eacReadme]

#### Other Options

Override option to parse markdown when retrieving a segment

    [eacReadme parse='true|false' ...]

Set class='language-*' on code blocks

    [eacReadme lang='php|js|css|html' ...]

#### Translating Header/Section Names

Translate header/section names when retrieving _All Headers_, _All Sections_, or _Document_

    [eacReadme translate='name=newname,...']
    [eacReadme translate='Requires at least=Requires WordPress Version,Screenshots=Screen Shots']

Erase default translation table

    [eacReadme translate='no|none|false']

Default translation table

    [
        'Headers'               => 'Document Header',
        'Plugin URI'            => 'Homepage',
        'Stable tag'            => 'Current Version',
        'Requires at least'     => 'Requires WordPress Version',
        'Tested up to'          => 'Compatible up to',
        'Requires PHP'          => 'Requires PHP Version',
        'WC requires at least'  => 'Requires WooCommerce',
        'Requires EAC'          => 'Requires {eac}Doojigger',
        'Changelog'             => 'Change Log',
        'Screenshots'           => 'Screen Shots',
    ];

### Installation

**{eac}Doojigger Readme Extension** is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).

#### Automatic Plugin Installation

This plugin is available from the [WordPress Plugin Repository](https://wordpress.org/plugins/search/earthasylum/) and can be installed from the WordPress Dashboard » *Plugins* » *Add New* page. Search for 'EarthAsylum', click the plugin's [Install] button and, once installed, click [Activate].

See [Managing Plugins -> Automatic Plugin Installation](https://wordpress.org/support/article/managing-plugins/#automatic-plugin-installation-1)

#### Upload via WordPress Dashboard

Installation of this plugin can be managed from the WordPress Dashboard » *Plugins* » *Add New* page. Click the [Upload Plugin] button, then select the eacreadme.zip file from your computer.

See [Managing Plugins -> Upload via WordPress Admin](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin)

#### Manual Plugin Installation

You can install the plugin manually by extracting the eacreadme.zip file and uploading the 'eacreadme' folder to the 'wp-content/plugins' folder on your WordPress server.

See [Managing Plugins -> Manual Plugin Installation](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1)

#### Settings

Once installed and activated options for this extension will show in the 'General' tab of {eac}Doojigger settings.


### Screenshots

1. Readme Extension
![{eac}Readme Extension](https://ps.w.org/eacreadme/assets/screenshot-1.png)


### Other Notes

#### Additional Information

+   {eac}Readme is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).
+   {eac}Readme uses [Parsedown 1.7.4](http://parsedown.org/), Copyright (c) 2013-2018 [Emanuil Rusev](erusev.com)
+   {eac}Readme uses [Prism syntax highlighter](https://prismjs.com/), Copyright (c) 2012 Lea Verou


### Copyright

#### Copyright © 2019-2024, EarthAsylum Consulting, distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.  

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should receive a copy of the GNU General Public License along with this program. If not, see [https://www.gnu.org/licenses/](https://www.gnu.org/licenses/).


### Changelog

#### Version 1.2.6 – January 24, 2024

+	Fixed "preg_match(): Passing null" notice

#### Version 1.2.5 – December 8, 2023

+	Fixed caching (yet again) by adding current file name to cache key.
	+	subsequent segments could load the wrong cache key when no file given.

#### Version 1.2.4 – December 4, 2023

+	Added button to flush group cache when using drop-in object cache.
+	Added use of 'EAC_README_CACHE_LIFETIME' constant to set cache life-time (default = 1 day).
	+	 In wp-config.php: `define('EAC_README_CACHE_LIFETIME',3600);`
+	md5() cache key name.

#### Version 1.2.3 – August 30, 2023

+	Improved caching.

#### Version 1.2.2 – June 6, 2023

+	Removed unnecessary plugin_update_notice trait.
+	Cosmetic changes to option & help titles.

#### Version 1.2.1 – April 22, 2023

+	Correctly load inline style using wp_add_inline_style()
+	Tested with WordPress 6.2 and {eac}Doojigger 2.2.

#### Version 1.2.0 – November 16, 2022

+	Updated to / Requires {eac}Doojigger 2.0.
+	Uses 'options_settings_page' action to register options.
+	Added contextual help using 'options_settings_help' action.
+	Renamed extension file(s) and vendor directory.
+	Changed tab name in registerExtension (must be -re-enabled in admin).
+	Moved plugin_action_links hook to eacDoojigger_load_extensions filter.
+	Added use of WP_Object_Cache.
+	Fixed problem with admin page reload when changing style.

#### Version 1.1.1 – September 25, 2022

+	Fixed potential PHP notice on load (plugin_action_links_).
+   Added upgrade notice trait for plugins page.
+	Added tagify option: getTags(true) when processing tags to convert to array [slug=>tag]

#### Version 1.1.0 – September 7, 2022

+	Added ability to get the full file contents as a code block (Code File).
+	Renamed include file eacParseReadme.php to class.readme_parser.php.

#### Version 1.0.7 – August 28, 2022

+	Updated to / Requires {eac}Doojigger 1.2
+	Added 'Settings', 'Docs' and 'Support' links on plugins page.
+   Fixed admin display error (section not found).

#### Version 1.0.6 – July 12, 2022

+   Get contributor profile when getting all headers.
+	Move short description before headers in getDocument().

#### Version 1.0.5 – June 22, 2022

+   Added "{plugin}_eacReadme" filter.
+   Added title attribute to nav links.

#### Version 1.0.4 – June 9, 2022

+   Updated for {eac}Doojigger 1.1.0

#### Version 1.0.3 – May 12, 2022

+   Added transient caching of contributor profiles.

#### Version 1.0.2 – May 10, 2022

+   Added section name translation.

#### Version 1.0.1 – April 28, 2022

+   Minor changes / enhancements.

#### Version 1.0.0 – February 26, 2022

+   Initial release.


### Upgrade Notice

#### 1.2.0

Requires {eac}Doojigger version 2.0+
