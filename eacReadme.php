<?php
namespace EarthAsylumConsulting;

/**
 * Add {eac}Readme extension to {eac}Doojigger
 *
 * @category	WordPress Plugin
 * @package		{eac}Readme\{eac}Doojigger Extensions
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 * @link		https://eacdoojigger.earthasylum.com/eacreadme/
 * @link 		https://wordpress.org/plugins/eacreadme
 * @link 		https://github.com/EarthAsylum/eacReadme
 * @uses		Parsedown 1.8.0, Copyright (c) Emanuil Rusev, erusev.com
 * @see			http://parsedown.org/
 * @uses		Prism 1.30, Copyright (c) Lea Verou
 * @see			https://prismjs.com/
 *
 * @wordpress-plugin
 * Plugin Name:			{eac}Readme
 * Description:			{eac}Readme loads and translates a WordPress readme.txt file providing shortcodes to access header lines, section blocks, or the entire document.
 * Version:				1.5.4
 * Requires at least:	5.8
 * Tested up to:		7.1
 * Requires PHP:		8.1
 * Plugin URI:			https://eacdoojigger.earthasylum.com/eacreadme/
 * Author:				EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * License:				GPLv3 or later
 * License URI:			https://www.gnu.org/licenses/gpl.html
 */

if (!defined('EACDOOJIGGER_VERSION'))
{
	\add_action( 'admin_notices', function()
		{
			echo '<div class="notice notice-error is-dismissible">'.
				 '<em>{eac}Readme</em> requires installation & activation of '.
				 '<a href="https://eacdoojigger.earthasylum.com/eacdoojigger" target="_blank">'.
				 '{eac}Doojigger</a>.</div>';
		}
	);
	return;
}

class eacReadme
{
	/**
	 * constructor method
	 *
	 * @return	void
	 */
	public function __construct()
	{
		/**
		 * {pluginname}_load_extensions - get the extensions directory to load
		 *
		 * @param	array	$extensionDirectories - array of [plugin_slug => plugin_directory]
		 * @return	array	updated $extensionDirectories
		 */
		add_filter( 'eacDoojigger_load_extensions', function($extensionDirectories)
			{
				if (is_admin())
				{
					/*
					 * Enable update notice (self hosted or wp hosted)
					 */
					eacDoojigger::loadPluginUpdater(__FILE__,'wp');

					/*
					 * Add links on plugins page
					 */
					add_filter( (is_network_admin() ? 'network_admin_' : '').'plugin_action_links_' . plugin_basename( __FILE__ ),
						function($pluginLinks, $pluginFile, $pluginData) {
							return array_merge(
								[
									'settings'		=> eacDoojigger::getSettingsLink($pluginData,'general'),
									'documentation'	=> eacDoojigger::getDocumentationLink($pluginData),
									'support'		=> eacDoojigger::getSupportLink($pluginData),
								],
								$pluginLinks
							);
						},20,3
					);
				}

				/*
    			 * Add our extension to load
    			 */
				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ )];
				return $extensionDirectories;
			}
		);

		/**
		 * eacReadme_load_parser - loads the eacParseReadme static class for 3rd-party use
		 *
		 * @return	void
		 */
		add_filter( 'eacReadme_load_parser', function()
			{
				if (! class_exists('\eacParseReadme',false))
				{
					require_once 'Extensions/class.eacReadme.parser.php';
				}
			}
		);
	}
}
new \EarthAsylumConsulting\eacReadme();
?>
