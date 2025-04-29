<?php
namespace EarthAsylumConsulting\Extensions;

if (! class_exists(__NAMESPACE__.'\readme_extension', false) )
{
	/**
	 * Extension: readme - parse readme.txt to shortcodes - {eac}Doojigger for WordPress
	 *
	 * @category	WordPress Plugin
	 * @package 	{eac}Readme\{eac}Doojigger Extensions
	 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
	 * @copyright	Copyright (c) 2025 EarthAsylum Consulting <www.EarthAsylum.com>
	 * @link		https://eacDoojigger.earthasylum.com/
	 * @see 		https://eacDoojigger.earthasylum.com/phpdoc/
	 * @uses		Parsedown 1.7.4, Copyright (c) 2013-2018 Emanuil Rusev, erusev.com
	 * @see 		http://parsedown.org/
	 * @uses 		Prism 1.27, Copyright (c) 2012 Lea Verou
	 * @see			https://prismjs.com/
	 */

	class readme_extension extends \EarthAsylumConsulting\abstract_extension
	{
		/**
		 * @var string extension version
		 */
		const VERSION	= '25.0419.1';

		/**
		 * cache lifetime in seconds
		 *
		 * @var int
		 */
		private $cache_lifetime 	= DAY_IN_SECONDS;


		/**
		 * constructor method
		 *
		 * @param 	object	$plugin main plugin object
		 * @return 	void
		 */
		public function __construct($plugin)
		{
			parent::__construct($plugin, self::DEFAULT_DISABLED | self::ALLOW_ADMIN);

			if (defined( 'EAC_README_CACHE_LIFETIME' ) && is_int( EAC_README_CACHE_LIFETIME )) {
				$this->cache_lifetime = EAC_README_CACHE_LIFETIME;
			}

			add_action('admin_init', function()
			{
				$this->registerExtension( $this->className );
				// Register plugin options when needed
				$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
				// Add contextual help
				$this->add_action( 'options_settings_help', array($this, 'admin_options_help') );
			});
		}


		/**
		 * register options on options_settings_page
		 *
		 * @access public
		 * @return void
		 */
		public function admin_options_settings(): void
		{
			$cssFiles = [];
			foreach(@glob(__DIR__."/vendor/prism/prism_*.css") as $css)
			{
				$cssFiles[] = ucwords( str_replace(['prism_','_'],['',' '],basename($css,'.css')) );
			}

			$this->registerExtensionOptions( $this->className,
				[
					'readme_code_language'	=> array(
								'type'		=> 	'radio',
								'label'		=> 	'<span class="dashicons dashicons-editor-code"></span> Code Language',
								'options'	=>	[ 'disabled', 'PHP', 'JS', 'CSS', 'HTML', ['C-Like'=>'clike'], 'none'],
								'default'	=> 	'disabled',
								'info'		=> 	'Enable Prism code highlighting (for &lt;code&gt; blocks) by selecting the default coding language.'.
												"<br><small>Language can be overriden with the 'lang=x' tag in the shortcode.</small>"
							),
					'readme_code_style'		=> array(
								'type'		=> 	'select',
								'label'		=> 	'<span class="dashicons dashicons-editor-code"></span>Code Highlighting',
								'options'	=>	$cssFiles,
								'default'	=> 	'Default',
								'info'		=> 	'Select the style to use with Prism code highlighting.',
							),
				]
			);

			if (\wp_using_ext_object_cache() && \wp_cache_supports( 'flush_group' ))
			{
				$this->registerExtensionOptions( $this->className,
					[
						'_btnReadMeFlush' 	=> array(
								'type'		=> 	'button',
								'label'		=> 	'<span class="dashicons dashicons-remove"></span> Clear Cache',
								'default'	=> 	'Erase',
								'info'		=>	"Erase the 'Readme' object cache.",
								'validate'	=>	function() {\wp_cache_flush_group($this->className);},
						),
					]
				);
			}

			// when our submit buttons post
			$this->add_filter( 'options_form_post_readme_code_language',	array($this, 'reload_for_style'), 10, 4 );
			$this->add_filter( 'options_form_post_readme_code_style',		array($this, 'reload_for_style'), 10, 4 );
		}


		/**
		 * Add help tab on admin page
		 *
		 * @return	void
		 */
 		public function admin_options_help()
		{
			if (!$this->plugin->isSettingsPage('General')) return;

			if ($this->isEnabled())
			{
				$content = 	do_shortcode("[eacReadme plugin='eacreadme/readme.txt']Short Description[/eacReadme]") .
							"<details><summary>Shortcode Usage</summary><blockquote>".
								do_shortcode("[eacReadme]Description/Shortcode Usage[/eacReadme]")."</blockquote></details>" .
							"<details><summary>Shortcode Examples</summary><blockquote>".
								do_shortcode("[eacReadme]Description/Shortcode Examples[/eacReadme]")."</blockquote></details>" .
							"<details><summary>Other Options</summary><blockquote>".
								do_shortcode("[eacReadme]Description/Other Options[/eacReadme]")."</blockquote></details>";
			}
			else
			{
				$content = 	"{eac}Readme loads and translates a WordPress markdown 'readme.txt' file providing shortcodes to access header lines and section blocks. " .
							"Enable the extension for shortcode exampled and instructions. ";
			}

			$this->addPluginHelpTab('Readme',$content,['{eac}Readme Extension','open']);

			$this->addPluginSidebarLink(
				"<span class='dashicons dashicons-shortcode'></span>{eac}Readme",
				"https://eacdoojigger.earthasylum.com/eacreadme/",
				"{eac}Readme Extension Plugin"
			);
		}


		/**
		 * initialize method - called from main plugin
		 *
		 * @return 	void
		 */
		public function initialize()
		{
			if ( ! parent::initialize() ) return; // disabled
		}


		/**
		 * Add filters and actions - called from main plugin
		 *
		 * @return	void
		 */
		public function addActionsAndFilters()
		{
			parent::addActionsAndFilters();

			if ($this->is_option('readme_code_language'))
			{
				\add_action( \is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts', function()
				{
					$urlPrefix 	= plugins_url('/vendor',__FILE__);
					$cssFile 	= strtolower(str_replace(' ','_',$this->get_option('readme_code_style','default')));
			    	$scriptId 	= 'eacreadme-prism';
			    	// Register prism.css file
			        wp_register_style($scriptId, $urlPrefix . "/prism/prism_{$cssFile}.css");
			        // Register prism.js file
			        wp_register_script($scriptId, $urlPrefix . "/prism/prism.js");
			        // Enqueue the registered style and script files
			        wp_enqueue_style($scriptId);
			        wp_enqueue_script($scriptId);
					// remove indent of code blocks
					wp_add_inline_style($scriptId, "pre code {padding-left: 0;}");
				}, 99 );
			}
		}


		/**
		 * Add shortcodes - called from main plugin
		 *
		 * @return	void
		 */
		public function addShortcodes(): void
		{
			parent::addShortcodes();

			\add_shortcode( 'eacReadme', [ $this, 'readme_shortcode' ] );
		}


		/**
		 * readme  shortcode function
		 *
		 * @return	mixed
		 */
		public function readme_shortcode($atts = null, $sections = null, $tag = '' )
		{
			static $current_file = null;

			$a = shortcode_atts( [
				'file' 			=> '',		// relative to WP document root
				'content' 		=> '',		// relative to WP_CONTENT_DIR
				'plugin' 		=> '',		// relative to WP_PLUGIN_DIR
				'theme' 		=> '',		// relative to get_theme_root
				'wpsvn' 		=> '',		// retrieve from WP svn
				'github' 		=> '',		// retrieve from github
				'token' 		=> null,	// access token (github)
				'parse' 		=> null,	// boolean option
				'translate' 	=> null,	// translate section names
				'lang' 			=> $this->get_option('readme_code_language'),
				'ttl'			=> $this->cache_lifetime,
			], $atts, $tag );

			$parse_file = '';					// file name to be loaded/parsed
			$file_context = null;				// file_get_context context

			if ($a['file'])
			{
				$parse_file = $current_file = ABSPATH .'/'. ltrim($a['file'],'/');
			}
			else if ($a['content'])
			{
				$parse_file = $current_file = WP_CONTENT_DIR .'/'. ltrim($a['content'],'/');
			}
			else if ($a['plugin'])
			{
				$parse_file = $current_file = WP_PLUGIN_DIR .'/'. ltrim($a['plugin'],'/');
			}
			else if ($a['theme'])
			{
				$parse_file = $current_file = get_theme_root() .'/'. ltrim($a['theme'],'/');
			}
			else if ($a['wpsvn'])
			{
				// /eacreadme/trunk/readme.txt
				$wpsvn = 'https://ps.w.org/';
				$parse_file = $current_file = $wpsvn . ltrim(str_replace(
					[ 'https://', 'http://', 'plugins.svn.wordpress.org', 'ps.w.org' ],
					'',
					$a['wpsvn']
				),'/');
				// https://plugins.svn.wordpress.org/eacreadme/trunk/readme.txt
				$file_context = stream_context_create(array(
					'http'	=> array(
						'method'	=> 	"GET",
						'header'	=> 	"Accept: text/plain\r\n".
										"user-agent: ".filter_input(INPUT_SERVER,'HTTP_USER_AGENT',FILTER_SANITIZE_STRING),
					)
				));
			}
			else if ($a['github'])
			{
				// /KBurkholder/eacReadme/main/readme.txt
				$github = 'https://raw.githubusercontent.com/';
				$parse_file = $current_file = $github . ltrim(str_replace(
					[ 'https://', 'http://', 'raw.githubusercontent.com','github.com', '/blob' ],
					'',
					$a['github']
				),'/');
				// https://raw.githubusercontent.com/KBurkholder/eacReadme/main/readme.txt
				$token = $a['token'] ?: (defined( 'GITHUB_ACCESS_TOKEN' ) ? GITHUB_ACCESS_TOKEN : null);
				if ($token) {
			    //    $parse_file = $current_file = add_query_arg( 'access_token',  , $parse_file );
			        $file_context = stream_context_create(array(
						'http'	=> array(
							'method'	=> "GET",
							'header'	=> "Authorization: Token {$token}\r\n",
						)
					));
				}
			}
			else
			{
				$a['file'] = $current_file; 	// currently loaded file
			}

			if ($sections)
			{
				$cacheKey = md5(sanitize_key($sections) .':'.
								sanitize_key($tag) .':'.
								serialize($a)
							);
				if ( $cache = wp_cache_get($cacheKey,$this->className) )
				{
					return $cache;
				}

				$sections 	= array_map('trim', explode(',', $sections));
			}

			require_once 'class.eacReadme.parser.php';

			if ($parse_file)
			{
				\eacParseReadme::loadFile($parse_file,$file_context);
			}

			if (!is_null($a['parse']))
			{
				$a['parse'] = [\filter_var($a['parse'],FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)];
			}

			if (!is_null($a['translate']))
			{
				\eacParseReadme::loadTranslations($a['translate']);
			}

			if ($a['lang'] && $a['lang'] != 'none') {
				\eacParseReadme::setCodeLanguage($a['lang']);
			}

			if ($sections)
			{
				$result 	= '';
				foreach ($sections as $section)
				{
					$section = str_replace(' ','',ucwords($section));
					if (method_exists('\eacParseReadme', "get{$section}")) {
						$section = "get{$section}";
					}
					if (is_bool($a['parse'])) {
						$result .=  \eacParseReadme::{$section}($a['parse']) ?: "<em>{$section} not found</em>";
					} else {
						$result .=  \eacParseReadme::{$section}() ?: "<em>{$section} not found</em>";
					}
				}

				/**
				 * filter {classname}_eacReadme
				 * @param	string	$result parsed readme content
				 * @return	string	updated readme content
				 */
				$result = $this->apply_filters('eacReadme',$result);

				$ttl = max(intval($a['ttl']),MINUTE_IN_SECONDS);
				wp_cache_set($cacheKey,$result,$this->className,$ttl);
				return $result;
			}
		}


		/**
		 * filter for options_form_post_ reload page to force style setting effect
		 *
		 * @param string 	$value - the value POSTed
		 * @param string	$fieldName - the name of the field/option
		 * @param array		$metaData - the option metadata
		 * @param string	$priorValue - the prior option value
		 * @return string	$function
		 */
		public function reload_for_style($value, $fieldName=null, $metaData=null, $priorValue=null)
		{
			if ($value != $priorValue)
			{
				$this->plugin->page_reload();
			}
			return $value;
		}
	}
}
/**
 * return a new instance of this class
 */
if (isset($this)) return new readme_extension($this);
?>
