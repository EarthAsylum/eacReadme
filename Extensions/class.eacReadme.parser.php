<?php
/**
 * Parse WordPress readme.txt file
 *
 * Parse individual elements/blocks or full document of a well-formed WordPress readme.txt file
 *
 * @category	WordPress Plugin
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2024 EarthAsylum Consulting <www.earthasylum.com>
 * @version		24.1012.1
 * @see 		https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/
 * @uses		Parsedown 1.7.4, Copyright (c) 2013-2018 Emanuil Rusev, erusev.com
 * @see 		http://parsedown.org/
 */


if (! class_exists('Parsedown',false))
{
	require 'vendor/parsedown-1.7.4/Parsedown.php';
}

/*
 * Usage:
 *
 *	Load a readme.txt file
 * 		\eacParseReadme::loadFile($_SERVER['DOCUMENT_ROOT'] .'/path/to/readme.txt);
 *
 * 	Get a header value
 * 		\eacParseReadme::getHeader('Header Name');
 * 		\eacParseReadme::getHeader(true); (parse markup)
 *
 * 	Get all header lines
 * 		\eacParseReadme::getAllHeaders(); (parse markup)
 * 		\eacParseReadme::getAllHeaders(false); (unparsed)
 *
 * 	Get a section block
 * 		\eacParseReadme::getSection('Section Name'); (parse markup)
 * 		\eacParseReadme::getSection('Section Name',false); (unparsed)
 *
 * 	Get all section blocks (parsed)
 * 		\eacParseReadme::getAllSections();
 * 		\eacParseReadme::getAllSections(true); (add tags)
 *
 * 	Get entire document (parsed)
 * 		\eacParseReadme::getDocument(); (add detail/summary/nav tags to sections)
 * 		\eacParseReadme::getDocument(false); (no tags)
 *
 * 	Get title (=== Plugin Name ===)
 * 		\eacParseReadme::getTitle();
 *
 * 	Get short description (after headers)
 * 		\eacParseReadme::getShortDescription();
 *
 * 	Get version ('Stable tag' or 'Version')
 * 		\eacParseReadme::getVersion();
 *
 * 	Get homepage ('Homepage' or 'Plugin URI')
 * 		\eacParseReadme::getHomepage();
 * 		\eacParseReadme::getHomepage(true); (parsed for url)
 *
 * 	Get author ('Author' or 'Author Name' and 'Author URI')
 * 		\eacParseReadme::getAuthor(); (parsed for [name](uri))
 *
 * 	Get contributors (wp.org_user_name OR gravatar_email_addgress OR [Display Name](user_name | email_address | url))
 * 		\eacParseReadme::getContributors(); (parsed for [name](uri))
 * 		\eacParseReadme::getContributors(true); (array for use with WordPress plugins api)
 *
 * 	Get any other named header or section
 * 		\eacParseReadme::getCamelCaseName();
 *		e.g.	getDonateLink()			= 'Donate link' header (unparsed)
 *				getDonateLink(true)		= 'Donate link' header (parse markup)
 *				getOtherNotes()			= 'Other Notes' section (parse markup)
 *				getOtherNotes(false)	= 'Other Notes' section (unparsed)
 *
 * Translations
 *
 * When using getAllHeaders() or getAllSections() (including getDocument()),
 * header/section names may be translated by loading a translation table with:
 *		"section_name" => "translated_name"
 *		"Requires at least"	=> "Requires WordPress Version"
 * and calling:
 * 		loadTranslations($table);
 */

/*
 *	a well-formed readme.txt file looks something like...
 * (https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/)

=== Plugin Name ===
Contributors: (this should be a list of wordpress.org userid's)
Donate link: https://example.com/
Tags: tag1, tag2
Requires at least: 4.7
Tested up to: 5.4
Stable tag: 4.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Here is a short description of the plugin. This should be no more than 150 characters. No markup here.

== Description ==

description block (using markdown)

== Installation ==

installation block (using markdown)

== FAQ ==

FAQ block (using markdown)

== Screenshots ==

1. screenshot 1 title
![Title](https://example.com/path/assets/screenshot-1.png)

== Changelog ==

change log block (using markdown)

*/

/* Non-WP-standard...

{banner content between title and headers}
Homepage: http://url/to/plugin/page
Plugin URI: http://url/to/plugin/page
WordPress URI: https://wordpress.org/plugins/page
Author: author name OR [author name](http://url/to/author/page)
Author URI: http://url/to/author/page
Contributors: wp.org_user_name OR gravatar_email_addgress OR [Display Name](user_name | email_address | url)
Description: short description
Version: M.m.p
Last Updated: date time

* other header/sections/blocks may be included as well (Upgrade Notice, Reviews, Copyright, Other Notes, etc)...
*/

if (! class_exists('eacParseReadme',false))
{
	class eacParseReadme
	{
		/**
		 * @var string default language to tag <code class='language-*'>
		 */
		private static $defaultLang;
		/**
		 * @var array translate segment names
		 */
		private static $translate = [
			'Headers'				=> 'Document Header',
			'Plugin URI'			=> 'Homepage',
			'Stable tag'			=> 'Current Version',
			'Requires at least'		=> 'Requires WordPress Version',
			'Tested up to'			=> 'Compatible up to',
			'Requires PHP'			=> 'Requires PHP Version',
			'WC requires at least' 	=> 'Requires WooCommerce',
			'Requires EAC'			=> 'Requires {eac}Doojigger',
			'Changelog'				=> 'Change Log',
			'Screenshots'			=> 'Screen Shots',
		];
		/**
		 * @var string readme.txt file contents
		 */
		private static $content;
		/**
		 * @var string readme.txt header block
		 */
		private static $headers;
		/**
		 * @var object Parsedown instance
		 */
		private static $parser;


		/**
		 * Set default language for <code class='language-*'> when using code highlighters
		 *
		 * @param string 	$language - php, css, html, js, etc.
		 * @return bool
		 */
		public static function setCodeLanguage(string $language): void
		{
			self::$defaultLang = strtolower($language);
		}


		/*
		 * Load translation table
		 *
		 * @param string|array 	$table - [title=>new_title,...] | "title=new_title,..."
		 * @return void
		 */
		public static function loadTranslations($table)
		{
			if (is_string($table))
			{
				$output = array();
				foreach (explode(',', str_replace([';','|',"\n"],',',$table)) as $key => $value)
				{
					if (in_array(strtolower($value),['no','none','false','']))
					{
						self::$translate = [];
					}
					else if (strpos($value,'=') > 0)
					{
						list($key,$value) = explode('=',$value,2);
					}
					$output[trim($key)] = trim($value);
				}
				self::$translate = array_merge(self::$translate,$output);
			}
			else
			{
				self::$translate = array_merge(self::$translate,$table);
			}
		}


		/**
		 * Load file and initialize parser
		 *
		 * @param string 	$filePath - path to readme.txt
		 * @param resource 	$file_context stream_context_create() if needed
		 * @return bool
		 */
		public static function loadFile(string $filePath, $file_context = null): bool
		{
			if (! self::$content = file_get_contents($filePath,false,$file_context)) {
				return false;
			}
			// if normal markdown, replace header markers with WordPress markers
			self::$content = preg_replace(
			//		H4						H3					H2
				[ "|^#### (.+)$|m", 	"|^### (.+)$|m", 	"|^## (.+)$|m" ],
				[ "= $1 =", 			"== $1 ==",			"=== $1 ===" ],
				self::$content
			);
			self::$content .= "\n== [EOF]\n";
			self::$headers 	= null;
			self::$parser 	= new \Parsedown();
			return true;
		}


		/**
		 * Parse markdown text block
		 *
		 * @param string 	$text - segment text to parse
		 * @return string	parsed text
		 */
		public static function parseMarkdownText(string $text): string
		{
			// Replace WordPress header markers with Markdown markers
			$text = preg_replace(
			//		H2							H3						H4
				[ "|^=== (.+) ===$|m", 	"|^== (.+) ==$|m",		"|^= (.+) =$|m" ],
				[ "## $1", 					"### $1", 				"#### $1" ],
				$text
			);
			return trim(self::$parser->text($text));
		}


		/**
		 * Parse markdown line
		 *
		 * @param string 	$taxt - segment text to parse
		 * @return string	parsed text
		 */
		public static function parseMarkdownLine(string $text): string
		{
			return trim(self::$parser->line($text));
		}


		/*
		 * Get headers block
		 *
		 * @param bool 		$parse - parse the segment through Parsedown
		 * @param bool 		$inline - parse as inline text (no enclosing <p>, no wp header markup)
		 * @param string	$content - get from content
		 * @return string	requested segment or empty string
		 */
		public static function getHeaderBlock(bool $parse = false, bool $inline = false, $content = null): string
		{
			if (self::$headers)	// get all headers once
			{
				$text = self::$headers;
			}
			else
			{
				$pattern = '|'."^([a-zA-Z0-9 ]+):(\s+)(.+)\n".'|m';

				$content = $content ?: self::$content;
				// search up to the first section header
				if (!$content || !preg_match_all($pattern, substr( $content, 0, strpos($content,"\n== ") ), $matches))
				{
					return '';
				}
				//echo "<p>Pattern:{$pattern}</p><pre>";print_r([$matches,$text]);echo "</pre><hr>";

				$text = trim(implode("",$matches[0]))."\n";
				self::$headers = $text;
			}

			if ($parse)
			{
				$text = ($inline) ? self::parseMarkdownLine($text) : self::parseMarkdownText($text);
			}

			return $text;
		}


		/*
		 * Get a segment of the readme file
		 *
		 * @param string 	$start_marker - the starting marker of the segment
		 * @param string 	$end_marker - the ending marker of the segment
		 * @param bool 		$parse - parse the segment through Parsedown
		 * @param bool 		$inline - parse as inline text (no enclosing <p>, no wp header markup)
		 * @param string	$content - get from content
		 * @return string	requested segment or empty string
		 */
		public static function getSegment(string $start_marker, string $end_marker, bool $parse = false, bool $inline = false, $content = null): string
		{
			$pattern = '|'.preg_quote($start_marker,'|').'(.*)'.preg_quote($end_marker,'|').'|isU';

			if (!preg_match($pattern, (string)($content ?: self::$content), $matches))
			{
				return '';
			}

			$text = trim($matches[1]);

			if ($parse)
			{
				$text = ($inline) ? self::parseMarkdownLine($text) : self::parseMarkdownText($text);
				$text = self::codeTagLanguage($text);
			}

			return $text;
		}


		/**
		 * check code tag language
		 *
		 * @param string $text
		 * @return string
		 */
		public static function codeTagLanguage($text): string
		{
			// default language when using code highlighters
			if (self::$defaultLang)
			{
				$text = str_replace("<code>", "<code class='language-".self::$defaultLang."'>", $text);
			}
			return $text;
		}


		/**
		 * Get file as a code block
		 *
		 * @return string file contents (unprocessed)
		 */
		public static function getCodeFile(): string
		{
			$text = self::getSegment("","\n== [EOF]",false);
			$text = self::parseMarkdownText("```\n".$text."\n```");
			return self::codeTagLanguage($text);
		}


		/*
		 * Get pre-defined segment - header value
		 *
		 * A header value is one line with "name: value\n"
		 * Typically a header is not parsed but may be (i.e. author url) and if parsed, parse as inline
		 *
		 * @param string 	$header - the header name
		 * @param bool 		$parse - parse the header through Parsedown
		 * @return string	requested header value or empty string
		 */
		public static function getHeader(string $header, bool $parse = false): string
		{
			$headers = self::getHeaderBlock(false);
			return self::getSegment("{$header}:", "\n", $parse, $parse, $headers);
		}


		/*
		 * Get all headers
		 *
		 * A header value is one line with "name: value\n"
		 * The headers are sequential at the beginning of the file following the '=== plugin name ===' title
		 *
		 * @param bool 		$parse - parse the headers through Parsedown
		 * @param bool 		$addTags - wrap name & value in <span>
		 * @return string	requested headers or empty string
		 */
		public static function getAllHeaders(bool $parse = true, bool $addTags = false): string
		{
			$lines = self::getHeaderBlock($parse, true);

			if ($addTags)
			{
				$result = "<details id='readme-head' class='readme'><summary>".self::_translate('Headers')."</summary><p>";
				$pattern = "|^(.*):\s(.*)$|m";
				if (preg_match_all($pattern, $lines, $matches))
				{
					foreach ($matches[1] as $x=>$line)
					{
						if (strtolower($line) == 'contributors') {
							$matches[2][$x] = self::getContributors();
						}
						$result .= 	"<span class='readme-head-name'>".self::_translate($line).":</span>".
									"<span class='readme-head-value'>".trim($matches[2][$x])."</span>\n";
					}
				}
				$result .= "</p></details>";
				$lines = $result;
			}
			return $lines;
		}


		/*
		 * Get pre-defined segment - section block
		 *
		 * A section block begins with "== name ==" and ends with the next section
		 * Sections are parsed for markdown
		 *
		 * @param string 	$section - the section name
		 * @param bool 		$parse - parse the headers through Parsedown
		 * @return string	requested segment or empty string
		 */
		public static function getSection(string $section, bool $parse = true): string
		{
			$pattern = '|^(.*)/(.*)$|';	// look for section/sub-section (H4 within H3)

			if (preg_match($pattern, $section, $matches))
			{
				$content = "\n".self::getSegment("\n== {$matches[1]} ==\n", "\n==", false)."\n=";
				return self::getSegment("\n= {$matches[2]} =\n", "\n=", $parse, false, $content);
			}

			return self::getSegment("\n== {$section} ==\n", "\n==", $parse) ;
		}


		/*
		 * Get all sections (anything after headers & short description)
		 *
		 * A section block begins with "== name ==" and ends with the next section
		 * Sections are parsed for markdown
		 *
		 * @param bool 		$addTags - wrap sections in <detail><summary> and. add nav anchor tags
		 * @return string	requested segment or empty string
		 */
		public static function getAllSections($addTags = false): string
		{
			$pattern = '|^== (.*) ==$|m';

			$result = '';
			$tags 	= '';
			if (preg_match_all($pattern, self::$content, $matches))
			{
				foreach ($matches[1] as $section)
				{
					if ($addTags) {
						$sectionId = str_replace(' ','-',strtolower($section));
						$tags 	.= "<a href='#readme-{$sectionId}' class='readme' title='".
									self::_translate($section)."'>".
									self::_translate($section)."</a>\n";
						$result .= "<details id='readme-{$sectionId}' class='readme' open><summary>".
									self::_translate($section)."</summary>".
									self::getSection($section)."</details>\n<a href='#readme-top'>Top</a>\n";
					} else {
						$result .= "<h3 class='readme'>{$section}</h3>".self::getSection($section);
					}
				}
			}
			return ($tags)
				? "<nav id='readme-nav' class='readme'>".$tags."</nav>".
					"<div id='readme-content'>$result</div>"
				: $result;
		}


		/*
		 * Get the document (headers, short description, sections)
		 *
		 * @param bool 		$addTags - wrap sections in <detail><summary> and. add nav anchor tags
		 * @return string	requested all segments
		 */
		public static function getDocument($addTags = true): string
		{
			return "<a id='readme-top'/></a>" .
					"<div id='readme-banner' class='readme'>".self::getBanner().'</div>' .
					"<p id='readme-short' class='readme'>".self::getShortDescription()."</p>" .
					self::getAllHeaders(true,true) .
					self::getAllSections($addTags);
		}


		/*
		 * Get pre-defined segment - title
		 *
		 * Title is the first/only line enclosed in === ===
		 * Title is not parsed for markdown
		 *
		 * @return string	requested segment or empty string
		 */
		public static function getTitle(): string
		{
			return self::getSegment("=== ", " ===\n") ?: self::getHeader('Plugin Name');
		}


		/*
		 * Get non-standard segment - banner
		 *
		 * banner is anything between the "=== title ===" line and the first header (normally nothing)
		 * banner is parsed for markdown
		 *
		 * @return string	requested segment or empty string
		 */
		public static function getBanner(): string
		{
			$header 	= explode("\n", trim(self::getHeaderBlock()));
			$header 	= $header[0];
			$content	= self::getSegment(" ===\n", "\n{$header}",true);
			// special, non-standard case: look for opening html tag before headers,
			// where headers are wrapped in an html tag (<header>...</header>, <details>...</details>)
			if (preg_match("/(.*)\n<(header|details)>/s", $content, $matches)) {
				$content	= $matches[1];
			}
			return trim($content);
		}


		/*
		 * Get pre-defined segment - short description
		 *
		 * Description is a single line proceeded with header lines and a blank line followed by a blank line and the (long) Description section
		 * Will fallback to a 'Description' header (though not recommended, conflicts with 'Description' section)
		 * Description is not parsed for markdown
		 *
		 * @return string	requested segment or empty string
		 */
		public static function getShortDescription(): string
		{
			$header 	= explode("\n", trim(self::getHeaderBlock()));
			$header 	= end($header);
			$content	= self::getSegment("{$header}", "\n== ");
			// special, non-standard case: look for closing html tag before short description,
			// where headers are wrapped in an html tag (<header>...</header>, <details>...</details>)
			if (preg_match("/<\/(header|details)>\n(.*)/s", $content, $matches)) {
				$content	= $matches[2];
			}
			return ltrim(trim($content),"> ");
		}


		/*
		 * Get pre-defined segment - version ('Stable tag' or 'Version' header)
		 *
		 * Version is either 'Stable Tag:' (standard) or 'Version:' (non-standard)
		 * Version is not parsed for markdown
		 *
		 * @return string	requested segment or empty string
		 */
		public static function getVersion(): string
		{
			return  self::getHeader('Stable tag') ?: self::getHeader('Version');
		}


		/*
		 * Get pre-defined segment - homepage ('Homepage' or 'Plugin URI' header)
		 *
		 * Homepage is either 'Homepage:' (non-standard) or 'Plugin URI:' (non-standard)
		 * Homepage is not parsed for markdown
		 *
		 * @param bool 		$parse - parse the headers through Parsedown
		 * @return string	requested segment or empty string
		 */
		public static function getHomepage(bool $parse = false): string
		{
			return self::getHeader('Homepage',$parse) ?: self::getHeader('Plugin URI',$parse);
		}


		/*
		 * Get pre-defined segment - author header
		 *
		 * Author is either 'Author:' or 'Author Name:' and 'Author URI:'
		 * Author may use markdown (non-standard) 'Author: [name](url)'
		 *
		 * @return string	requested segment or empty string
		 */
		public static function getAuthor(): string
		{
			if ( ($authorUri = self::getHeader('Author URI')) && ($author = self::getHeader('Author Name')) )
			{
				return self::parseMarkdownLine("[{$author}]({$authorUri})");
			}
			return self::getHeader('Author',true);
		}


		/*
		 * Get pre-defined segment - tags header
		 *
		 * @param bool 		$tagify return tags array [tag-slug=>tag]
		 * @return array|string	requested segment or empty
		 */
		public static function getTags(bool $tagify = false)
		{
			if (! $tags = self::getHeader("tags") )
			{
				return ($tagify) ? [] : '';
			}

			if (! $tagify) return $tags;

			$tags = explode(',',$tags);
			$tagArray = [];

			foreach ($tags as $tag)
			{
				$slug = str_replace([' ','_','.'],'-',strtolower(trim($tag)));
				$slug = preg_replace('|[^a-z0-9_\-]|','',$slug);
				$tagArray[$slug] = trim($tag);
			}
			return $tagArray;
		}


		/*
		 * Get pre-defined segment - contributors header
		 *
		 * Option to get WP plugin_info array of contributors
		 * contributors is a comma-delimited list of WP.org user ids
		 * accept Gravatar email addresses and parse for [display name](profile link) format
		 *
		 * @param bool 		$forPluginAPI return profile array for WordPress plugin api
		 * @return array|string	requested segment or empty string
		 */
		public static function getContributors(bool $forPluginAPI = false)
		{
			if (! $contributors = self::getHeader("contributors") )
			{
				return ($forPluginAPI) ? [] : '';
			}

			$contributors = explode(',',$contributors);
			$profiles = [];

			foreach ($contributors as $contributor)
			{
				$contributor = strtolower(trim($contributor));
/* 				if (! $forPluginAPI)
				{
					$profiles[] = self::parseMarkdownLine($contributor);
					continue;
				}
*/

				if (preg_match('|^\[(.*?)\]\((.*)\)$|', $contributor, $matches))
				// [display name](profile link)
				{
					if (preg_match('|^mailto:(.*)|i', $matches[2], $link))
					// [display name](mailto:email@address.com)
					{
						$profiles = array_merge($profiles,self::_getGravatarProfile(md5($link[1]),$link[1],$matches[1]));
						continue;
					}

					if (preg_match('|(.*)\.gravatar\.com/(.*)|i', $matches[2], $link))
					// [display name](http://www.gravatar.com/profile/)
					{
						$profiles = array_merge($profiles,self::_getGravatarProfile($link[2],$link[2],$matches[1]));
						continue;
					}

					if (preg_match('|(.*)github\.com/(.*)|i', $matches[2], $link))
					// [display name](http://github.com/profile/)
					{
						$profiles = array_merge($profiles,self::_getGithubProfile($link[2],$matches[1]));
						continue;
					}

					if (preg_match('|(.*)\.wordpress\.org/(.*)|i', $matches[2], $link))
					// [display name](http://profiles.wordpress.org/profile/)
					{
						$profiles = array_merge($profiles,self::_getWordpressProfile($link[2],$matches[1]));
						continue;
					}

					// [display name](http://something.com/someprofile/url)
					$profiles[str_replace([' ','@','.'],'',strtolower($matches[1]))] = [
								'display_name'	=> $matches[1],
								'profile'		=> $matches[2],
								'avatar'		=> "https://secure.gravatar.com/avatar/?d=mp"
						];
				}
				else
				//  profile link only - email address (gravatar) or profile name (WordPress)
				// 		or <profile>@wordpress|gravatar|github
				{
					if (strpos($contributor, '@')) 	// email address
					{
						if (preg_match('/^(.*)@(wordpress|gravatar|github)$/', $contributor, $matches))
						{
							$profile_id = $matches[1];
							switch ($matches[2])
							{
								case 'wordpress': 	// <profileid>@wordpress
									$profiles = array_merge($profiles,self::_getWordpressProfile($profile_id,$profile_id));
									break;
								case 'gravatar': 	// <profileid>@gravatar
									$profiles = array_merge($profiles,self::_getGravatarProfile($profile_id,$profile_id));
									break;
								case 'github': 		// <profileid>@github
									$profiles = array_merge($profiles,self::_getGithubProfile($profile_id));
									break;
							}
						}
						else
						{
							$profiles = array_merge($profiles,self::_getGravatarProfile(md5($contributor),$contributor));
						}
					}
					else							// profile name
					{
						$profiles = array_merge($profiles,self::_getWordpressProfile($contributor));
					}

				}
			}

			if (! $forPluginAPI)
			{
				$contributors = array();
				foreach ($profiles as $profile) {
					$contributors[] = ($profile['profile'])
						? self::parseMarkdownLine( "[{$profile['display_name']}]({$profile['profile']})"  )
						: $profile['display_name'];
				}
				return implode(', ',$contributors);
			}

			return $profiles;
		}


		/*
		 * Get Gravatar profile
		 *
		 * @param string 	$profile - the profile name (may be md5()'d)
		 * @param string 	$textProfile - the profile name (text)
		 * @param string 	$displayName - display name (if provided)
		 * @return array	profile content
		 */
		private static function _getGravatarProfile(string $profile, string $textProfile, string $displayName=null): array
		{
			$profile = rtrim($profile,'/');
			// if we're in WordPress, use transient
			$transient = "eacReadme_gravatar_profile_{$profile}";
			if (function_exists('\get_transient') && ($content = \get_transient($transient))) {
				return $content;
			}
			// use gravatar profile api in PHP serialized format
			if ( ($content = @file_get_contents('https://secure.gravatar.com/'.$profile.'.php'))
			&&   ($content = unserialize($content)) )
			{
				$content = [$content['entry'][0]['preferredUsername'] => [
						'display_name'	=> $displayName ?: $content['entry'][0]['displayName'],
						'profile'		=> $content['entry'][0]['profileUrl'],
						'avatar'		=> $content['entry'][0]['thumbnailUrl']
				]];
				if (function_exists('\set_transient')) {
					\set_transient($transient,$content,DAY_IN_SECONDS);
				}
				return $content;
			}
			// no matching profile
			if (!$displayName) list($displayName) = explode('@',$textProfile);
			return [$textProfile => [
					'display_name'	=> $displayName,
					'profile'		=> '',
					'avatar'		=> "https://secure.gravatar.com/avatar/?d=mp"
			]];
		}


		/*
		 * Get Github profile
		 *
		 * @param string 	$profile - the profile name (may be md5()'d)
		 * @param string 	$displayName - display name (if provided)
		 * @return array	profile content
		 */
		private static function _getGithubProfile(string $profile, string $displayName=null): array
		{
			$profile = rtrim($profile,'/');
			// if we're in WordPress, use transient
			$transient = "eacReadme_github_profile_{$profile}";
			if (function_exists('\get_transient') && ($content = \get_transient($transient))) {
				return $content;
			}
			$context = stream_context_create(array(
				'http'	=> array(
					'method'	=> 	"GET",
					'header'	=> 	"Accept: application/vnd.github+json, application/json\r\n".
									"X-GitHub-Api-Version: 2022-11-28\r\n".
									"User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n",
				)
			));
			if ( ($content = @file_get_contents('https://api.github.com/users/'.$profile,false,$context))
			&&   ($content = json_decode($content)) )
			{
				$content = [$content->login => [
						'display_name'	=> $content->name,
						'profile'		=> $content->html_url,
						'avatar'		=> $content->avatar_url
				]];
				if (function_exists('\set_transient')) {
					\set_transient($transient,$content,DAY_IN_SECONDS);
				}
				return $content;
			}
			// no matching profile
			return [$profile => [
					'display_name'	=> $displayName ?: $profile,
					'profile'		=> '',
					'avatar'		=> "https://secure.gravatar.com/avatar/?d=mp",
			]];
		}


		/*
		 * Get WordPress profile
		 *
		 * @param string 	$profile - the profile name
		 * @param string 	$displayName - display name (if provided)
		 * @return array	profile content
		 */
		private static function _getWordpressProfile(string $profile, string $displayName=null): array
		{
			$profile = rtrim($profile,'/');
			// if we're in WordPress, use transient
			$transient = "eacReadme_wporg_profile_{$profile}";
			if (function_exists('\get_transient') && ($content = \get_transient($transient))) {
				return $content;
			}
			if ( ($content = @file_get_contents('https://profiles.wordpress.org/wp-json/wporg/v1/users?slug='.$profile))
			&&   ($content = json_decode($content,true)) )
			{
				$content = $content[0];
				$content = [$profile => [
						'display_name'	=> $content['name'],
						'profile'		=> str_replace('/author/','/',$content['link']),
						'avatar'		=> $content['avatar_urls']['96'],
				]];
				if (function_exists('\set_transient')) {
					\set_transient($transient,$content,DAY_IN_SECONDS);
				}
				return $content;
			}
			// get wordpress.org profile page (first 64k), parse for 'og' tags
			if ( ($content = @file_get_contents('https://profiles.wordpress.org/'.$profile.'/',false,null,0,64*1024)) )
			{
				$title = $displayName ?: ( (preg_match('/<meta property="og:title" content="(.*) \((.*)\)/i', $content, $matches)) ? $matches[1] : $profile );
				$url   = (preg_match('/<meta property="og:url" content="(.*)"/i', $content, $matches)) ? $matches[1] : "https://profiles.wordpress.org/{$profile}/";
				$image = (preg_match('/<meta property="og:image" content="(.*)\?s=/i', $content, $matches)) ? $matches[1] : 'https://secure.gravatar.com/avatar/?d=mp';

				$content = [$profile => [
						'display_name'	=> $title,
						'profile'		=> $url,
						'avatar'		=> $image
				]];
				if (function_exists('\set_transient')) {
					\set_transient($transient,$content,DAY_IN_SECONDS);
				}
				return $content;
			}
			// no matching profile
			return [$profile => [
					'display_name'	=> $displayName ?: $profile,
					'profile'		=> '',
					'avatar'		=> "https://secure.gravatar.com/avatar/?d=mp"
			]];
		}


		/*
		 * Translate a segment title
		 *
		 * @param string 	$title - the title to be translated
		 * @return string	translated (or not) title
		 */
		private static function _translate(string $title, $usePreg=false): string
		{
			if (empty(self::$translate)) {
				return $title;
			}
			if (array_key_exists($title,self::$translate)) {
				return self::$translate[$title];
			}
			if ($usePreg)
			{
				foreach (self::$translate as $from=>$to) {
					$title = preg_replace("|^{$from}\n$|i", $to, $title, 1);
				}
			}
			return $title;
		}


		/**
		 * All other headers/segments - getNameString()
		 *
		 * @param string 	$name - the segment name
		 * @param bool 		$args - parse the header segment through Parsedown
		 * @return string	requested segment or empty string
		 */
		public static function __callStatic(string $name, $args /* bool $parse */)
		{
			/* drop 'get' and parse function name to header/section name...
				getDonateLink()			'Donate link';
				getRequiresAtLeast()	'Requires at least';
				getTestedUpTo()			'Tested up to';
				getStableTag()			'Stable tag';
				getRequiresPHP()		'Requires PHP';
				getLicenseURI()			'License URI';
				getOtherNotes()			'Other Notes';
			*/
			$name = preg_replace(['/^get(.*?)/','/([A-Z])(?<=[a-z]\1|[A-Za-z]\1(?=[a-z]))/'], ['$1',' $1'], $name);
			return self::getHeader($name,...$args) ?: self::getSection($name,...$args);
		}
	}
}
?>
