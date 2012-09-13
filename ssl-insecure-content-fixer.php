<?php
/*
Plugin Name: SSL Insecure Content Fixer
Plugin URI: http://snippets.webaware.com.au/wordpress-plugins/ssl-insecure-content-fixer/
Description: Fix some common problems with insecure content on pages using SSL
Version: 1.4.0
Author: WebAware
Author URI: http://www.webaware.com.au/
*/

if (!defined('SSLFIX_PLUGIN_ROOT')) {
	define('SSLFIX_PLUGIN_ROOT', dirname(__FILE__) . '/');
	define('SSLFIX_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
}

class SSLInsecureContentFixer {

	/**
	* hook WordPress to handle script and style fixes
	*/
	public static function run() {
		add_action('wp_print_scripts', array(__CLASS__, 'scriptsFix'), 100);
		add_action('wp_print_styles', array(__CLASS__, 'stylesFix'), 100);
		add_filter('plugin_row_meta', array(__CLASS__, 'addPluginDetailsLinks'), 10, 2);
		add_filter('image_widget_image_url', array(__CLASS__, 'filterImageWidgetURL'));
	}

	/**
	* action hook for adding plugin details links
	*/
	public static function addPluginDetailsLinks($links, $file) {
		// add settings link
		if ($file == SSLFIX_PLUGIN_NAME) {
			$links[] = '<a href="http://wordpress.org/support/plugin/ssl-insecure-content-fixer">' . __('Support') . '</a>';
			$links[] = '<a href="http://wordpress.org/extend/plugins/ssl-insecure-content-fixer/">' . __('Rating') . '</a>';
			$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=FNFKTWZPRJDQE">' . __('Donate') . '</a>';
		}

		return $links;
	}

	/**
	* force plugins to load scripts with SSL if page is SSL
	*/
	public static function scriptsFix() {
		global $wp_scripts;

		if (is_ssl()) {
			if (!is_admin()) {
				// search the registered scripts for any that will load as insecure content
				foreach ((array) $wp_scripts->registered as $script) {
					// only fix if source URL starts with http://
					if (stripos($script->src, 'http://') !== FALSE)
						$script->src = self::fixURL($script->src);
				}
			}
		}
	}

	/**
	* force plugins to load styles with SSL if page is SSL
	*/
	public static function stylesFix() {
		global $wp_styles;

		if (is_ssl()) {
			if (!is_admin()) {
				// search the registered stylesheets for any that will load as insecure content
				foreach ((array) $wp_styles->registered as $style) {
					// only fix if source URL starts with http://
					if (stripos($style->src, 'http://') !== FALSE)
						$style->src = self::fixURL($style->src);
				}

				// force list-category-posts-with-pagination plugin to load its CSS with SSL (it doesn't use wp_enqueue_style)
				if (function_exists('admin_register_head') && is_dir(WP_PLUGIN_DIR . '/list-category-posts-with-pagination')) {
					remove_action('wp_head', 'admin_register_head');
					$url = plugins_url('pagination.css', 'list-category-posts-with-pagination/x');
					wp_enqueue_style('lcpwp', $url);
				}
			}
		}
	}

	/**
	* filter Image Widget image links to load over SSL if page is SSL
	* @return string
	*/
	public static function filterImageWidgetURL($imageurl) {
		if (is_ssl()) {
			// only fix if source URL starts with http://
			if (stripos($imageurl, 'http://') !== FALSE)
				$imageurl = self::fixURL($imageurl);
		}

		return $imageurl;
	}

	/**
	* replace URL with one that uses SSL
	* @param string $url
	* @return string
	*/
	private static function fixURL($url) {
		return str_replace('http://', 'https://', $url);
	}

	/**
	* remove filters that are methods of an object of some class
	* @param string $filterName name of action or filter hook
	* @param string $className name of class for object method
	*/
	private static function removeObjectFilters($filterName, $className) {
		global $wp_filter;

		// must take a variable to iterate over array of filters,
		// else a subtle reference bug messes up the original array!
		$filters = $wp_filter[$filterName];

		foreach ($filters as $priority => $hooks) {
			foreach ($hooks as $idx => $filter) {
				// check for function being a method on a $className object
				if (is_array($filter['function']) && is_a($filter['function'][0], $className)) {
					remove_filter($filterName, $idx, $priority);
					break;
				}
			}
		}
	}
}

SSLInsecureContentFixer::run();
