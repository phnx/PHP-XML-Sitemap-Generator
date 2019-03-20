# PHP XML Sitemap Generator - Boosted


*March 2019*  
*Ping Charoenwet - @phnx*

## Credits

The original PHP XML Sitemap Generator was created by [Hemn Chawroka](http://iprodev.com) from [iProDev](http://iprodev.com). Released under the MIT license.

Included scripts:

 - [PHP Simple HTML DOM Parser](http://simplehtmldom.sourceforge.net/) - A HTML DOM parser written in PHP5+

## Introduction
An improved version of IProDev's PHP XML Sitemap Generator that 
Sitemap format: [http://www.sitemaps.org/protocol.html](http://www.sitemaps.org/protocol.html)

## Additional Features
 - Robust URL Duplication Check
 - Only store URLs that has HTTP response code = 200
 - Skipping Extensions - what's not supposed to be scanned
 - Page Priority Calculation - reduced by # of path levels

## Usage
Similar to original version, the script can be executed after following steps:

- Configure the crawler by modifying the `sitemap-generator.php` file
 - Change initial URL to crawl
 - Change the sitemap.xml output location
 - Change accepted extensions ("/" is manditory for proper functionality)
 - Change skipping extensions
 - Change skipping URL list
 - Change frequency (always, daily, weekly, monthly, never, etc...)
 - Change default last modification date
 - Change Maximum Priority (default = 1, lessened by level of URL path)

- The script can be started as CLI script or as Website. CLI is the preferred way to start this script.

 - CLI scripts are started from the command line, can be used with CRON. You start it with the php program.

 - CLI command to create the XML file: `php sitemap-generator.php`

- To start the program with your Webserver as Website change in the script the line 33 from
```php
   define ('CLI', true);
```
to 
```php
   define ('CLI', false);
```
