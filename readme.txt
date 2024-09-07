=== {eac}Doojigger Readme Extension for WordPress ===
Plugin URI:         https://eacdoojigger.earthasylum.com/eacreadme/
Author:             [EarthAsylum Consulting](https://www.earthasylum.com)
Stable tag:         1.4.2
Last Updated:       04-Apr-2024
Requires at least:  5.8
Tested up to:       6.6
Requires PHP:       7.4
Requires EAC:       2.0
Contributors:       kevinburkholder
License:            GPLv3 or later
License URI:        https://www.gnu.org/licenses/gpl.html
Tags:               readme, markdown, parsedown, {eac}Doojigger, code-highlighting, github, svn
WordPress URI:      https://wordpress.org/plugins/eacreadme
GitHub URI:         https://github.com/EarthAsylum/eacReadme

{eac}Readme loads and translates a WordPress markdown 'readme.txt' file providing shortcodes to access header lines and section blocks.

== Description ==

_{eac}Readme_ is an [{eac}Doojigger](https://eacDoojigger.earthasylum.com/) extension which loads and translates a WordPress markdown 'readme.txt' file providing shortcodes to access header lines and section blocks.

= Shortcode Usage =

The first used shortcode must indicate the file to load...

    [eacReadme file='/docfolder/readme.txt']        # file is relative to the WordPress document root folder
    [eacReadme content='/contentfolder/readme.txt'] # content file is relative to the WordPress content folder (wp-content/)
    [eacReadme plugin='/pluginfolder/readme.txt']   # plugin file is relative to the WordPress plugins folder (wp-content/plugins/)
    [eacReadme theme='/themefolder/readme.txt']     # theme file is relative to the WordPress themes folder (wp-content/themes/)
    [eacReadme wpsvn='/slugname/trunk/readme.txt']  # load file from WordPress SVN repository
    [eacReadme github='/owner/repository/main/readme.txt']        # load file from github repository

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

= Shortcode Examples =

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

= Other Options =

Change the default cache time-to-live by adding to wp-config.php:

    define('EAC_README_CACHE_LIFETIME',$seconds);   # default: 1-day (DAY_IN_SECONDS).

Override the default cache time-to-live

    [eacReadme ttl=$seconds ...]                    # minimum: 1-minute (MINUTE_IN_SECONDS).

Set the default GitHub access token (for private repositories):

    define('GITHUB_ACCESS_TOKEN',$token);

Set/override the GitHub access token

    [eacReadme token=$token ...]

Override option to parse markdown when retrieving a segment

    [eacReadme parse='true|false' ...]

Set class='language-*' on code blocks

    [eacReadme lang='php|js|css|html' ...]

= Translating Header/Section Names =

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

= Readme Format =

{eac}Readme expects a well-formed readme.txt file that follows the [WordPress readme file standard](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works)...

    === title ===
    header: value
    header: value
    short Description
    == section ==
    = sub-section =
    
...but supports some extensions to that standard:

+   Author & Author URI
    +   `Author` header may be a simple name or a markdown link:
        +   `[Author](Author URI)`.
    +   The `Author` & `Author URI` headers, if present, are combined as a markdown `[Author](Author URI)`.

+   Homepage
    +   Looks for `Homepage` or `Plugin URI`.

+   Version
    +   Looks for `Version` or `Stable tag`.

+   Contributors
    +   `profileId` - wordpress profile (standard)
    +   `profileId@youremaildomain.com` - gravatar profile
    +   `profileId@wordpress` - wordpress profile
    +   `profileId@gravatar` - gravatar profile
    +   `profileId@github` - github profile
    +   `[display name](mailto:email@address.com)` or `[display name](http://www.gravatar.com/profileId/)`
    +   `[display name](http://profiles.wordpress.org/profileId/)`
    +   `[your name]((http://your/profile/url)`

+   A "banner" section may be included between the top title line and the first header line.

```
    === title ===
   [![banner](//image_url)](//link_url)
    header: value
    header: value
    short Description
    == section ==
    = sub-section =
```

+   The header block may be enclosed in an html `<header>` or `<details>` tag, opening and closing each on a single line. These tags are ignored by the eacParseReadme parser but may be beneficial if posting your readme file elseware. See [{eac}Readme on Github](https://github.com/EarthAsylum/eacReadme).

>   Note: these extensions are not supported by the WordPress Plugin Repository.

{eac}Readme supports standard markdown (readme.md) formatting for section identification.
+   `=== title ===` and `## title` are equivalent
+   `== section ==` and `### section` are equivalent
+   `= sub-section =` and `#### sub-section` are equivalent


= Output HTML =

When retrieving the header block with ...

`[eacReadme]All Headers[/eacReadme]` or `\eacParseReadme::getAllHeaders()`

Or when retrieving all sections with ...

`[eacReadme]All Sections[/eacReadme]` or `\eacParseReadme::getAllSections()`
    
Or when retrieving the entire document with ...

`[eacReadme]Document[/eacReadme]` or `\eacParseReadme::getDocument()`

Additional html tags and classes are added, including wrapping blocks within a `<details>` tags, adding `readme-*` class names, and adding `<a>` anchor links.


= WordPress Actions =

3rd-party actors may load and use the parser class included in {eac}Readme...

        do_action('eacReadme_load_parser');     // loads \eacParseReadme static class
        if (class_exists('\eacParseReadme'))
        {
            \eacParseReadme::loadFile($readme,$context);
            $html_document  = \eacParseReadme::getDocument();
            $title          = \eacParseReadme::getTitle();
            $version        = \eacParseReadme::getVersion();
            $donations      = \eacParseReadme::getHeader('donate_link');
            $description    = \eacParseReadme::getSection('description');
        }


== Installation ==

**{eac}Doojigger Readme Extension** is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).

= Automatic Plugin Installation =

This plugin is available from the [WordPress Plugin Repository](https://wordpress.org/plugins/search/earthasylum/) and can be installed from the WordPress Dashboard » *Plugins* » *Add New* page. Search for 'EarthAsylum', click the plugin's [Install] button and, once installed, click [Activate].

See [Managing Plugins -> Automatic Plugin Installation](https://wordpress.org/support/article/managing-plugins/#automatic-plugin-installation-1)

= Upload via WordPress Dashboard =

Installation of this plugin can be managed from the WordPress Dashboard » *Plugins* » *Add New* page. Click the [Upload Plugin] button, then select the eacreadme.zip file from your computer.

See [Managing Plugins -> Upload via WordPress Admin](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin)

= Manual Plugin Installation =

You can install the plugin manually by extracting the eacreadme.zip file and uploading the 'eacreadme' folder to the 'wp-content/plugins' folder on your WordPress server.

See [Managing Plugins -> Manual Plugin Installation](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1)

= Settings =

Once installed and activated options for this extension will show in the 'General' tab of {eac}Doojigger settings.


== Screenshots ==

1. Readme Extension
![{eac}Readme Extension](https://ps.w.org/eacreadme/assets/screenshot-1.png)

1. Readme Help
![{eac}Readme Help](https://ps.w.org/eacreadme/assets/screenshot-2.png)


== Other Notes ==

= Additional Information =

+   {eac}Readme is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).
+   {eac}Readme uses [Parsedown 1.7.4](http://parsedown.org/), Copyright (c) 2013-2018 [Emanuil Rusev](erusev.com)
+   {eac}Readme uses [Prism syntax highlighter](https://prismjs.com/), Copyright (c) 2012 Lea Verou

+   The [{eac}SoftwareRegistry Software Product Taxonomy](https://swregistry.earthasylum.com/software-taxonomy/) plugin uses {eac}Readme to parse readme markdown files hosted on Github to provide plugin information and automated updates to WordPress for self-hosted plugins.

== Copyright ==

= Copyright © 2019-2024, EarthAsylum Consulting, distributed under the terms of the GNU GPL. =

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should receive a copy of the GNU General Public License along with this program. If not, see [https://www.gnu.org/licenses/](https://www.gnu.org/licenses/).


== Changelog ==

= Version 1.4.2 – April 4, 2024 =

+   Fix deprecated notice on substr/strpos with null value in parser.
+   Compatible with WordPress 6.5.

= Version 1.4.1 – March 1, 2024 =

+   Additional documentation.
+   Improved isolation of headers and short description.
+   Support readme.md (standard markdown) files.
    +   Expects WordPress layout (h2 title, headers, Short Description, h3 sections).
+   Non-standard 'banner' content between === title === and headers.
+   Updated most regular expressions.

= Version 1.4.0 – February 9, 2024 =

+   Added github profile support along with optional profile formats for contributors.
+   Trim tags when tagifying from header.
+   Added new action, 'eacReadme_load_parser', to allow 3rd-party apps to use the parser class.
    +   `do_action('eacReadme_load_parser'); // loads \eacParseReadme static class`
+   Added context to file access for github authentication & WP headers.
+   Changed WordPress svn uri (https://ps.w.org/)

= Version 1.3.0 – January 31, 2024 =

+   Added 'ttl' option to shortcode to set cache time-to-live (min 1 minute).
+   Added WP SVN support:
    +   [eacReadme wpsvn='/slugname/trunk/readme.txt']document[/eacReadme]
+   Added GitHub support:
    +   [eacReadme github='/owner/repository/main/readme.txt']document[/eacReadme]
    +   [eacReadme github='...',token='...']

= Version 1.2.6 – January 24, 2024 =

+   Fixed "preg_match(): Passing null" notice.

= Version 1.2.5 – December 8, 2023 =

+   Fixed caching (yet again) by adding current file name to cache key.
    +   subsequent segments could load the wrong cache key when no file given.

= Version 1.2.4 – December 4, 2023 =

+   Added button to flush group cache when using drop-in object cache.
+   Added use of 'EAC_README_CACHE_LIFETIME' constant to set cache life-time (default = 1 day).
    +    In wp-config.php: `define('EAC_README_CACHE_LIFETIME',3600);`
+   md5() cache key name.

= Version 1.2.3 – August 30, 2023 =

+   Improved caching.

= Version 1.2.2 – June 6, 2023 =

+   Removed unnecessary plugin_update_notice trait.
+   Cosmetic changes to option & help titles.

= Version 1.2.1 – April 22, 2023 =

+   Correctly load inline style using wp_add_inline_style()
+   Tested with WordPress 6.2 and {eac}Doojigger 2.2.

= Version 1.2.0 – November 16, 2022 =

+   Updated to / Requires {eac}Doojigger 2.0.
+   Uses 'options_settings_page' action to register options.
+   Added contextual help using 'options_settings_help' action.
+   Renamed extension file(s) and vendor directory.
+   Changed tab name in registerExtension (must be -re-enabled in admin).
+   Moved plugin_action_links hook to eacDoojigger_load_extensions filter.
+   Added use of WP_Object_Cache.
+   Fixed problem with admin page reload when changing style.

= Version 1.1.1 – September 25, 2022 =

+   Fixed potential PHP notice on load (plugin_action_links_).
+   Added upgrade notice trait for plugins page.
+   Added tagify option: getTags(true) when processing tags to convert to array [slug=>tag]

= Version 1.1.0 – September 7, 2022 =

+   Added ability to get the full file contents as a code block (Code File).
+   Renamed include file eacParseReadme.php to class.readme_parser.php.

= Version 1.0.7 – August 28, 2022 =

+   Updated to / Requires {eac}Doojigger 1.2
+   Added 'Settings', 'Docs' and 'Support' links on plugins page.
+   Fixed admin display error (section not found).

= Version 1.0.6 – July 12, 2022 =

+   Get contributor profile when getting all headers.
+   Move short description before headers in getDocument().

= Version 1.0.5 – June 22, 2022 =

+   Added "{plugin}_eacReadme" filter.
+   Added title attribute to nav links.

= Version 1.0.4 – June 9, 2022 =

+   Updated for {eac}Doojigger 1.1.0

= Version 1.0.3 – May 12, 2022 =

+   Added transient caching of contributor profiles.

= Version 1.0.2 – May 10, 2022 =

+   Added section name translation.

= Version 1.0.1 – April 28, 2022 =

+   Minor changes / enhancements.

= Version 1.0.0 – February 26, 2022 =

+   Initial release.


== Upgrade Notice ==

= 1.2.0 =

Requires {eac}Doojigger version 2.0+
