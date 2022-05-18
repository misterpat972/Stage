<?php

/*
  Plugin Name: Bazooka Shopping
  Plugin URI:  https://bazooka.tech/downloads/bazooka-shopping
  Description: Automatic generation of affiliate links easily and effortlessly.
  Version: 30.1
  Author: Bazooka.Tech
  Author URI: https://bazooka.tech/
  License: GPLv3
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
  Text Domain: bazooka-shopping
  Domain Path: /languages/
 */
defined('ABSPATH') or die();

class Bazooka_Shopping
{

	const EDD_SL_ITEM_ID = 519;
	protected static $merchants;
	protected static $templates;
	protected static $used_products = array();
	protected static $content_cache = array();

	public static function init()
	{

		self::register_setting();

		add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'));

		add_shortcode('bzkshopping', array(__CLASS__, 'shortcode'));
		add_filter('the_content', array(__CLASS__, 'the_content'), 999);

		add_action('wp_enqueue_scripts', array(__CLASS__, 'wp_enqueue_scripts'));

		add_action('widgets_init', array(__CLASS__, 'widgets_init'));

		if (is_admin()) {
			add_action('admin_init', array(__CLASS__, 'admin_init_settings'));
			add_action('admin_menu', array(__CLASS__, 'admin_menu'));
			add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(__CLASS__, 'plugin_action_links'), 10, 1);
			add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_enqueue_scripts'));
			add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
			add_action('save_post', array(__CLASS__, 'save_post'));
			add_action('admin_init', array(__CLASS__, 'admin_init_updater'));
			add_filter('edd_sl_api_request_verify_ssl', '__return_false', 10, 1);
			add_action('admin_notices', array(__CLASS__, 'admin_notices'));
		}
	}

	protected static function register_setting()
	{
		__('Automatic generation of affiliate links easily and effortlessly.', 'bazooka-shopping');
		self::$merchants = array(
			'kelkoo' => __('Kelkoo', 'bazooka-shopping'),
			'amazon' => __('Amazon', 'bazooka-shopping'),
			'ebay' => __('eBay', 'bazooka-shopping'),
			'manomano' => __('ManoMano FR (Awin)', 'bazooka-shopping'),
			// /*pat_test*/
			// 'patest' => __('Patest', 'bazooka-shopping'),

		);
		self::$templates = array(
			'grid' => __('Grid (multi-column)', 'bazooka-shopping'),
			'box' => __('Box (full width)', 'bazooka-shopping')
		);
		$settings = array(
			'license' => array('type' => 'string', 'default' => false, 'sanitize_callback' => array(__CLASS__, 'register_setting_license_sanitize_callback')),
			'before_post' => array('type' => 'boolean', 'default' => get_option('bazooka_shopping_before', false)),
			'before_page' => array('type' => 'boolean', 'default' => get_option('bazooka_shopping_before', false)),
			'after_post' => array('type' => 'boolean', 'default' => get_option('bazooka_shopping_after', false)),
			'after_page' => array('type' => 'boolean', 'default' => get_option('bazooka_shopping_after', false)),
			'count' => array('type' => 'integer', 'default' => 12),
			'template' => array('type' => 'string', 'default' => key(self::$templates)),
			'color' => array('type' => 'string', 'default' => '#007bff'),
			'cta' => array('type' => 'string', 'default' => __('View Offer', 'bazooka-shopping')),
			'structured_data' => array('type' => 'boolean', 'default' => true),
			'cloak' => array('type' => 'boolean', 'default' => false),

			'amazon_tag' => array('type' => 'string', 'default' => ''),
			'amazon_api_key' => array('type' => 'string', 'default' => ''),
			'amazon_api_secret' => array('type' => 'string', 'default' => ''),
			'amazon_country' => array('type' => 'string', 'default' => 'fr'),
			'amazon_tag_guest' => array('type' => 'string', 'default' => ''),

			'kelkoo_api_key' => array('type' => 'string', 'default' => ''),
			'kelkoo_api_country' => array('type' => 'string', 'default' => 'fr'),
			// essai //
			'kelkoo_filterBy' => array('type' => 'string', 'default' => ''),

			'ebay_pubid' => array('type' => 'string', 'default' => ''),
			'ebay_campid' => array('type' => 'string', 'default' => ''),
			'ebay_country' => array('type' => 'string', 'default' => 'fr'),

			// /* start pat_test */
			// 'patest_testid' => array('type' => 'string', 'default'  => ''),
			// 'patest_merchant_id' => array('type' => 'string', 'default' => '18449'),
			// 'patest_code' => array('type' => 'string', 'default' => ''),
			// /* end test */

			'manomano_awin_affiliate_id' => array('type' => 'string', 'default' => ''),
			'manomano_awin_merchant_id' => array('type' => 'string', 'default' => '18448'),

		);
		foreach ($settings as $key => $setting) {
			register_setting('bazooka-shopping', 'bazooka_shopping_' . $key, $setting);
		}
	}


	public static function admin_init_settings()
	{

		add_settings_field('bazooka_shopping_license', __('License Key', 'bazooka-shopping'), function () {
			$plugin = get_plugin_data(__FILE__, false);
			$license = get_option('bazooka_shopping_license');
			$url = $license ? $plugin['AuthorURI'] . 'commande?' . http_build_query(array('edd_license_key' => $license, 'download_id' => self::EDD_SL_ITEM_ID)) : $plugin['PluginURI'];
			echo '<input type="text" id="bazooka_shopping_license" name="bazooka_shopping_license" placeholder="" value="' . $license . '">';
			echo '<input type="submit" class="button button-primary" value="' . __('Activate my license', 'bazooka-shopping') . '" style="margin-left:5px">';
			echo '<p class="description">' . sprintf(__('Enter your license key to receive automatic updates. If your license key has expired, <a href="%s" target="_blank">please renew it</a>.', 'bazooka-shopping'), $url) . '</p>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_license'));

		add_settings_section('bazooka-shopping-options', __('Settings', 'bazooka-shopping'), null, 'bazooka-shopping');
		add_settings_field('bazooka_shopping_before_after', __('Automatic mode', 'bazooka-shopping'), function () {
			echo '<p>' . sprintf(
				__('Automatically display products BEFORE the content of all %s ?', 'bazooka-shopping'),
				'<label for="bazooka_shopping_before_post" style="vertical-align:baseline;margin-left:.5rem"><input type="checkbox" id="bazooka_shopping_before_post" name="bazooka_shopping_before_post" value="1"' . checked(get_option('bazooka_shopping_before_post'), true, false) . '> ' . __('posts', 'bazooka-shopping') . '</label>' .
					'<label for="bazooka_shopping_before_page" style="vertical-align:baseline;margin-left:.5rem"><input type="checkbox" id="bazooka_shopping_before_page" name="bazooka_shopping_before_page" value="1"' . checked(get_option('bazooka_shopping_before_page'), true, false) . '> ' . __('pages', 'bazooka-shopping') . '</label>'
			) . '</p>';
			echo '<p>' . sprintf(
				__('Automatically display products AFTER the content of all %s ?', 'bazooka-shopping'),
				'<label for="bazooka_shopping_after_post" style="vertical-align:baseline;margin-left:.5rem"><input type="checkbox" id="bazooka_shopping_after_post" name="bazooka_shopping_after_post" value="1"' . checked(get_option('bazooka_shopping_after_post'), true, false) . '> ' . __('posts', 'bazooka-shopping') . '</label>' .
					'<label for="bazooka_shopping_after_page" style="vertical-align:baseline;margin-left:.5rem"><input type="checkbox" id="bazooka_shopping_after_page" name="bazooka_shopping_after_page" value="1"' . checked(get_option('bazooka_shopping_after_page'), true, false) . '> ' . __('pages', 'bazooka-shopping') . '</label>'
			) . '</p>';
			echo '<p class="description">' . __('Excluding homepage and legal notice. The title of {article|page} will be used to search for products.', 'bazooka-shopping') . '</p>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_before_after'));
		add_settings_field('bazooka_shopping_count', __('Number of products', 'bazooka-shopping'), function () {
			echo '<input type="number" step="1" min="1" id="bazooka_shopping_count" name="bazooka_shopping_count" value="' . get_option('bazooka_shopping_count') . '">';
			echo '<p class="description">' . __('The number of products displayed by default in automatic mode or if the attribute <code>count</code> is not specified in the shortcode.', 'bazooka-shopping') . '</p>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_count'));
		add_settings_field('bazooka_shopping_template', __('List design', 'bazooka-shopping'), function () {
			echo '<select id="bazooka_shopping_template" name="bazooka_shopping_template">';
			reset(self::$templates);
			$option = get_option('bazooka_shopping_template', key(self::$templates));
			foreach (self::$templates as $value => $label) {
				echo '<option value="' . $value . '" ' . selected($option, $value, false) . '>' . __($label, 'bazooka-shopping') . '</option>';
			}
			echo '</select>';
			echo '<p class="description">' . __('The style used by default in automatic mode or if the attribute <code>template</code> is not specified in the shortcode.', 'bazooka-shopping') . '</p>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_count'));
		add_settings_field('bazooka_shopping_color', __('Colour of the list', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_color" name="bazooka_shopping_color" value="' . get_option('bazooka_shopping_color') . '">';
			echo '<script>jQuery(function(){ jQuery("#bazooka_shopping_color").wpColorPicker(); });</script>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_color'));
		add_settings_field('bazooka_shopping_cta', __('Call to action button text', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_cta" name="bazooka_shopping_cta" placeholder="" value="' . get_option('bazooka_shopping_cta', __('View Offer', 'bazooka-shopping')) . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_cta'));
		add_settings_field('bazooka_shopping_structured_data', __('Structured data', 'bazooka-shopping'), function () {
			echo '<label for="bazooka_shopping_structured_data"><input type="checkbox" id="bazooka_shopping_structured_data" name="bazooka_shopping_structured_data" value="1"' . checked(get_option('bazooka_shopping_structured_data'), true, false) . '> ' . sprintf(__('Add markup to your product pages so Google can provide detailed product information in %srich Search results%s.', 'bazooka-shopping'), '<a href="https://developers.google.com/search/docs/data-types/product" target="_blank" rel="noreferrer">', '</a>') . '</label>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_cloak'));
		add_settings_field('bazooka_shopping_cloak', __('Cloaked redirection', 'bazooka-shopping'), function () {
			echo '<label for="bazooka_shopping_cloak"><input type="checkbox" id="bazooka_shopping_cloak" name="bazooka_shopping_cloak" value="1"' . checked(get_option('bazooka_shopping_cloak'), true, false) . '> ' . __('On every page where there are products, visitors (except robots) will be redirected to the first product displayed.', 'bazooka-shopping') . '</label>';
		}, 'bazooka-shopping', 'bazooka-shopping-options', array('label_for' => 'bazooka_shopping_cloak'));

		add_settings_section('bazooka-shopping-kelkoo', __(self::$merchants['kelkoo'], 'bazooka-shopping'), function () {
			echo __('<a href="https://publisher.kelkoo.com/app/api-credentials" target="_blank">Get your Kelkoo API credentials</a>', 'bazooka-shopping');
		}, 'bazooka-shopping');
		add_settings_field('bazooka_shopping_kelkoo_api_country', __('Country', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_kelkoo_api_country" name="bazooka_shopping_kelkoo_api_country" placeholder="XX" value="' . get_option('bazooka_shopping_kelkoo_api_country') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-kelkoo', array('label_for' => 'bazooka_shopping_kelkoo_api_country'));
		add_settings_field('bazooka_shopping_kelkoo_api_key', __('Token', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_kelkoo_api_key" name="bazooka_shopping_kelkoo_api_key" placeholder="XXXXXXXX" value="' . get_option('bazooka_shopping_kelkoo_api_key') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-kelkoo', array('label_for' => 'bazooka_shopping_kelkoo_api_key'));
		// //TODO:ajout d'un merchantId

		add_settings_field('bazooka_shopping_kelkoo_filterBy', __('Filterby', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_kelkoo_filterBy" name="bazooka_shopping_kelkoo_filterBy" placeholder="XXXXXXXX" value="' . get_option('bazooka_shopping_kelkoo_filterBy') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-kelkoo', array('label_for' => 'bazooka_shopping_kelkoo_filterBy'));

		add_settings_section('bazooka-shopping-amazon', __(self::$merchants['amazon'], 'bazooka-shopping'), function () {
			echo __('<a href="https://affiliate-program.amazon.com/home/account/tag/manage" target="_blank">Get your Amazon Tracking ID</a> | <a href="https://affiliate-program.amazon.com/assoc_credentials/home" target="_blank">Get your Amazon API credentials</a>', 'bazooka-shopping');
		}, 'bazooka-shopping');
		add_settings_field('bazooka_shopping_amazon_country', __('Marketplace', 'bazooka-shopping'), function () {
			echo '<select id="bazooka_shopping_amazon_country" name="bazooka_shopping_amazon_country">';
			echo '<option value=""></option>';
			$countries = array(
				'AU' => 'com.au',
				'BR' => 'com.br',
				'CA' => 'ca',
				'FR' => 'fr',
				'DE' => 'de',
				'IN' => 'in',
				'IT' => 'it',
				'JP' => 'co.jp',
				'MX' => 'com.mx',
				'ES' => 'es',
				'TR' => 'com.tr',
				'AE' => 'ae',
				'UK' => 'co.uk',
				'US' => 'com'
			);
			foreach ($countries as $label => $value) {
				echo '<option value="' . $value . '" ' . selected(get_option('bazooka_shopping_amazon_country'), $value, false) . '>' . $label . '</option>';
			}
			echo '</select>';
		}, 'bazooka-shopping', 'bazooka-shopping-amazon', array('label_for' => 'bazooka_shopping_amazon_country'));
		add_settings_field('bazooka_shopping_amazon_tag', __('Tracking ID', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_amazon_tag" name="bazooka_shopping_amazon_tag" placeholder="XXXXX-XX" value="' . get_option('bazooka_shopping_amazon_tag') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-amazon', array('label_for' => 'bazooka_shopping_amazon_tag'));
		add_settings_field('bazooka_shopping_amazon_api_key', __('API Key', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_amazon_api_key" name="bazooka_shopping_amazon_api_key" placeholder="XXXXXXXXXXXXXXXXXXXX" value="' . get_option('bazooka_shopping_amazon_api_key') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-amazon', array('label_for' => 'bazooka_shopping_amazon_api_key'));
		add_settings_field('bazooka_shopping_amazon_api_secret', __('API Secret', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_amazon_api_secret" name="bazooka_shopping_amazon_api_secret" placeholder="XXXXXXXXXXXXXXXXXXXX" value="' . get_option('bazooka_shopping_amazon_api_secret') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-amazon', array('label_for' => 'bazooka_shopping_amazon_api_secret'));
		add_settings_field('bazooka_shopping_amazon_tag_guest', __('Tracking ID "guest"', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_amazon_tag_guest" name="bazooka_shopping_amazon_tag_guest" placeholder="XXXXX-XX" value="' . get_option('bazooka_shopping_amazon_tag_guest') . '">';
			echo '<p class="description">' . __('Optional. Allows you to assign clicks to another Amazon account than the one used by the API.', 'bazooka-shopping') . '</p>';
		}, 'bazooka-shopping', 'bazooka-shopping-amazon', array('label_for' => 'bazooka_shopping_amazon_tag_guest'));

		add_settings_section('bazooka-shopping-ebay', __(self::$merchants['ebay'], 'bazooka-shopping'), function () {
			echo __('<a href="https://partner.ebay.com/" target="_blank">Get your eBay Partner Network credentials</a>', 'bazooka-shopping');
		}, 'bazooka-shopping');
		add_settings_field('bazooka_shopping_ebay_country', __('Marketplace', 'bazooka-shopping'), function () {
			echo '<select id="bazooka_shopping_ebay_country" name="bazooka_shopping_ebay_country">';
			echo '<option value=""></option>';
			$countries = array(
				'at',
				'au',
				'be-nl',
				'be-fr',
				'ca',
				'de',
				'es',
				'fr',
				'ie',
				'it',
				'nl',
				'uk',
			);
			foreach ($countries as $value) {
				echo '<option value="' . $value . '" ' . selected(get_option('bazooka_shopping_ebay_country'), $value, false) . '>' . strtoupper($value) . '</option>';
			}
			echo '</select>';
		}, 'bazooka-shopping', 'bazooka-shopping-ebay', array('label_for' => 'bazooka_shopping_ebay_country'));
		add_settings_field('bazooka_shopping_ebay_campid', __('Campaign ID', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_ebay_campid" name="bazooka_shopping_ebay_campid" placeholder="XXXXXXXXXX" value="' . get_option('bazooka_shopping_ebay_campid') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-ebay', array('label_for' => 'bazooka_shopping_ebay_campid'));


		/* start test pat */
		// add_settings_section('bazooka-shopping-patest', __(self::$merchants['patest'], 'bazooka-shopping'), function () {
		// 	echo __('<a href="http://knowledgedb.webosity.fr/" target="_blank">Get knowledgdb.webosity.fr</a>', 'bazooka-shopping');
		// }, 'bazooka-shopping');
		// add_settings_field('bazooka_shopping_patest_merchant_id', __('Merchant ID', 'bazooka-shopping'), function () {
		// 	echo '<input type="text" id="bazooka_shopping_patest_merchant_id" name="bazooka_shopping_patest_merchant_id" placeholder="XXXXXX" value="' . get_option('bazooka_shopping_patest_merchant_id') . '">';
		// }, 'bazooka-shopping', 'bazooka-shopping-patest', array('label_for' => 'bazooka_shopping_patest_merchant_id'));
		// add_settings_field('bazooka_shopping_patest_code', __('Code', 'bazooka-shopping'), function () {
		// 	echo '<textarea id="bazooka_shopping_patest_code" name="bazooka_shopping_patest_code" placeholder="XXXXXX" rows="10" cols="50">' . get_option('bazooka_shopping_patest_code') . '</textarea>';
		// }, 'bazooka-shopping', 'bazooka-shopping-patest', array('label_for' => 'bazooka_shopping_patest_code'));

		// add_settings_field('bazooka_shopping_patest_code', __('Code', 'bazooka-shopping'), function () {
		// 	echo '<input type="textarea" id="bazooka_shopping_patest_code" name="bazooka_shopping_patest_code" placeholder="XXXXXX" rows="10" cols="50" value="' . get_option('bazooka_shopping_patest_code') . '">';
		// }, 'bazooka-shopping', 'bazooka-shopping-patest', array('label_for' => 'bazooka_shopping_patest_code'));

		/* end test */


		add_settings_section('bazooka-shopping-manomano', __(self::$merchants['manomano'], 'bazooka-shopping'), function () {
			echo __('<a href="https://ui.awin.com/user/settings/accounts" target="_blank">Get your Awin credentials</a>', 'bazooka-shopping');
		}, 'bazooka-shopping');
		add_settings_field('bazooka_shopping_manomano_awin_merchant_id', __('Awin Program', 'bazooka-shopping'), function () {
			echo '<select id="bazooka_shopping_manomano_awin_merchant_id" name="bazooka_shopping_manomano_awin_merchant_id">';
			$merchants = array(
				'18448' => 'ManoMano Network (18448)',
				'17547' => 'ManoMano FR (17547)',
				'17961' => 'ManoMano DE (17961)',
				'17962' => 'ManoMano IT (17962)',
				'17963' => 'ManoMano ES (17963)',
				'17964' => 'ManoMano GB (17964)',
			);
			foreach ($merchants as $value => $label) {
				echo '<option value="' . $value . '" ' . selected(get_option('bazooka_shopping_manomano_awin_merchant_id'), $value, false) . '>' . $label . '</option>';
			}
			echo '</select>';
		}, 'bazooka-shopping', 'bazooka-shopping-manomano', array('label_for' => 'bazooka_shopping_manomano_awin_merchant_id'));
		add_settings_field('bazooka_shopping_manomano_awin_affiliate_id', __('Publisher ID', 'bazooka-shopping'), function () {
			echo '<input type="text" id="bazooka_shopping_manomano_awin_affiliate_id" name="bazooka_shopping_manomano_awin_affiliate_id" placeholder="XXXXXX" value="' . get_option('bazooka_shopping_manomano_awin_affiliate_id') . '">';
		}, 'bazooka-shopping', 'bazooka-shopping-manomano', array('label_for' => 'bazooka_shopping_manomano_awin_affiliate_id'));
	}

	public static function admin_menu()
	{
		add_options_page(
			__('Bazooka Shopping', 'bazooka-shopping'),
			__('Bazooka Shopping', 'bazooka-shopping'),
			'manage_options',
			'bazooka-shopping',
			array(__CLASS__, 'options_page')
		);
	}

	public static function plugins_loaded()
	{
		load_plugin_textdomain('bazooka-shopping', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

	public static function options_page()
	{
		include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'options_page.php';
	}

	public static function plugin_action_links($actions)
	{

		$actions[] = sprintf('<a href="%s">%s</a>', esc_url(admin_url('options-general.php?page=bazooka-shopping')), esc_html__('Settings', 'bazooka-shopping'));

		return $actions;
	}

	public static function admin_enqueue_scripts()
	{
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('bzkshop', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery-ui-tabs'), 1, true);
	}

	public static function add_meta_boxes()
	{
		add_meta_box(
			'bazooka-shopping',
			__('Bazooka Shopping', 'bazooka-shopping'),
			array(__CLASS__, 'add_meta_boxes_callback'),
			array('post', 'page'),
			'normal',
			'low'
		);
	}

	public static function add_meta_boxes_callback($post)
	{
		include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'meta_box.php';
		include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'help.php';
	}

	public static function save_post($post_id)
	{
		if ((is_multisite() && ms_is_switched())
			|| ($post_id === null)
			|| (!isset($_POST['bazooka_shopping_metabox_nonce']) || !wp_verify_nonce($_POST['bazooka_shopping_metabox_nonce'], 'bazooka_shopping_metabox'))
			|| (!isset($_POST['ID']) || $post_id !== (int) $_POST['ID'])
		) {
			return false;
		}
		$metas = array(
			'bazooka_shopping_before',
			'bazooka_shopping_after',
			'bazooka_shopping_keyword',
			'bazooka_shopping_merchants'
		);
		foreach ($metas as $meta) {
			if (isset($_POST[$meta])) {
				if ($_POST[$meta] === '') {
					delete_post_meta($post_id, '_' . $meta);
				} else {
					update_post_meta($post_id, '_' . $meta, trim($_POST[$meta]));
				}
			}
		}
	}

	public static function admin_init_updater()
	{
		$plugin = get_plugin_data(__FILE__, false);
		require_once plugin_dir_path(__FILE__) . 'library' . DIRECTORY_SEPARATOR . 'EDD_SL_Plugin_Updater.php';
		new Bazzoka_Shopping_EDD_SL_Plugin_Updater($plugin['AuthorURI'], __FILE__, array(
			'version' => $plugin['Version'],
			'license' => trim(get_option('bazooka_shopping_license')),
			'item_id' => self::EDD_SL_ITEM_ID,
			'author' => $plugin['Author'],
			'url' => preg_replace('#^www\.#', '', parse_url(home_url(), PHP_URL_HOST)),
			'wp_override' => false,
			'beta' => false
		));
	}

	public static function register_setting_license_sanitize_callback($new_license)
	{
		static $add_settings_error = false;
		$new_license = trim($new_license);
		$old_license = trim(get_option('bazooka_shopping_license'));
		if ($new_license == $old_license) {
			return $new_license;
		}
		$plugin = get_plugin_data(__FILE__, false);
		$url = preg_replace('#^www\.#', '', parse_url(home_url(), PHP_URL_HOST));
		if ($new_license) {
			$response = wp_remote_post($plugin['AuthorURI'], array('timeout' => 10, 'sslverify' => false, 'body' => array(
				'edd_action' => 'activate_license',
				'item_id' => self::EDD_SL_ITEM_ID,
				'license' => $new_license,
				'url' => $url
			)));
			if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
				!$add_settings_error && add_settings_error('bazooka_shopping_license', 'bazooka_shopping_license', __('An error occurred while activating your license, please try again.', 'bazooka-shopping'), 'error');
				$add_settings_error = true;
				return $old_license;
			}
			$response = json_decode(wp_remote_retrieve_body($response));
			if (!$response->success) {
				switch ($response->error) {
					case 'item_name_mismatch':
					case 'missing':
						$error = __('Your license key is invalid.', 'bazooka-shopping');
						break;
					case 'expired':
						$error = __('Your license key has expired, please renew it.', 'bazooka-shopping');
						break;
					case 'no_activations_left':
						$error = __('Your license key has reached its activation limit.', 'bazooka-shopping');
						break;
					case 'disabled':
					case 'revoked':
						$error = __('Your license key is deactivated.', 'bazooka-shopping');
						break;
					case 'invalid':
					case 'site_inactive':
						$error = __('Your license key is not active for this site.', 'bazooka-shopping');
						break;
					default:
						$error = __('An error occurred while activating your license, please try again.', 'bazooka-shopping');
						break;
				}
				!$add_settings_error && add_settings_error('bazooka_shopping_license', 'bazooka_shopping_license', $error, 'error');
				$add_settings_error = true;
				return $old_license;
			}
			!$add_settings_error && add_settings_error('bazooka_shopping_license', 'bazooka_shopping_license', sprintf(__('Your license key has been activated for the %s site.', 'bazooka-shopping'), $url), 'success');
			$add_settings_error = true;
		}
		if ($old_license) {
			$response = wp_remote_post($plugin['AuthorURI'], array('timeout' => 10, 'sslverify' => false, 'body' => array(
				'edd_action' => 'deactivate_license',
				'item_id' => self::EDD_SL_ITEM_ID,
				'license' => $old_license,
				'url' => $url
			)));
		}
		return $new_license;
	}

	public static function admin_notices()
	{
		if (stristr($_SERVER['REQUEST_URI'], 'bazooka-shopping') || trim(get_option('bazooka_shopping_license'))) {
			return;
		}
		echo '<div class="notice notice-info"><p>' . sprintf(__('<strong>Bazooka Shopping</strong> is installed. Please <a href="%s">add your license key</a> to activate automatic updates.', 'bazooka-shopping'), esc_url(admin_url('options-general.php?page=bazooka-shopping'))) . '</p></div>';
	}

	public static function wp_enqueue_scripts()
	{
		static $enqueue_scripts = false;
		if ($enqueue_scripts) {
			return;
		}
		$enqueue_scripts = true;

		wp_enqueue_style('bzkshop', plugin_dir_url(__FILE__) . 'css/style.css', false, 7);
		$color = get_option('bazooka_shopping_color');
		wp_add_inline_style('bzkshop', '.bzkshop .bzkshop-title,.bzkshop .bzkshop-discount{color:' . $color . '}.bzkshop .bzkshop-button{background-color:' . $color . '}.bzkshop .bzkshop-discount,.bzkshop .bzkshop-item:hover,.bzkshop .bzkshop-item:focus{border-color:' . $color . '}');

		wp_enqueue_script('bzkshop', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), 4, true);
		$cloak = get_option('bazooka_shopping_cloak') && !(is_preview() || is_customize_preview());
		wp_add_inline_script('bzkshop', 'window.bzkshop=' . json_encode(array('c' => $cloak)), 'before');
	}

	public static function widgets_init()
	{
		require_once plugin_dir_path(__FILE__) . 'includes' . DIRECTORY_SEPARATOR . 'bazooka-shopping-widget.php';
		register_widget('Bazooka_Shopping_Widget');
	}

	public static function the_content($content)
	{
		if (is_singular('post') || is_page()) {
			if (!in_the_loop() || !is_main_query() || is_home() || is_front_page()) {
				return $content;
			}
			$privacy = (int) get_option('wp_page_for_privacy_policy');
			if ($privacy && is_page($privacy)) {
				return $content;
			}
			$shortcodes = array('before', 'after');
			foreach ($shortcodes as $s => $position) {
				$shortcodes[$s] = '';
				$add_shortcode = get_post_meta(get_the_ID(), '_bazooka_shopping_' . $position, true);
				if ($add_shortcode === '') {
					$add_shortcode = get_option('bazooka_shopping_' . $position . '_' . (is_page() ? 'page' : 'post'));
				}
				if ($add_shortcode) {
					$key = $position . get_the_ID();
					if (!isset(self::$content_cache[$key])) {
						self::$content_cache[$key] = do_shortcode('[bzkshopping]');
					}
					$shortcodes[$s] = self::$content_cache[$key];
				}
			}
			$content = $shortcodes[0] . $content . $shortcodes[1];
		}
		return $content;
	}

	public static function shortcode($atts)
	{

		if (function_exists('is_amp_endpoint') && is_amp_endpoint()) {
			return;
		}

		$atts = shortcode_atts(array(
			'keyword' => '',
			'count' => get_option('bazooka_shopping_count'),
			'template' => get_option('bazooka_shopping_template'),
			'merchants' => '',
			//TODO:essai
			'filterBy' => '',
		), $atts);

		$keyword = trim($atts['keyword']);
		if (empty($keyword)) {
			if (is_singular()) {
				$keyword = trim(get_post_meta(get_the_ID(), '_bazooka_shopping_keyword', true));
				if ($keyword === '') {
					$keyword = trim(get_the_title());
				}
			} elseif (is_category()) {
				$keyword = trim(single_cat_title('', false));
			} elseif (is_tag()) {
				$keyword = trim(single_tag_title('', false));
			} elseif (is_tax()) {
				$keyword = trim(single_term_title('', false));
			} elseif (is_search()) {
				$keyword = trim(get_search_query());
			}
		}
		if (empty($keyword)) {
			return;
		}

		//TODO:ajout de filtre par filtreBy
		$filterBy = trim($atts['filterBy']) ? trim($atts['filterBy']) : '';
		if (empty($filterBy)) {
			$filterBy = get_option('bazooka_shopping_filterBy');
		}
		if (empty($filterBy)) {
			// si le get_option est vide, on prend le filtre par défaut
			$filterBy = 'all';
		}



		$merchants = trim($atts['merchants']);
		if (empty($merchants) && is_singular()) {
			$merchants = trim(get_post_meta(get_the_ID(), '_bazooka_shopping_merchants', true));
		}
		$merchants = array_values(array_intersect(array_unique(array_map('trim', explode(',', strtolower($merchants)))), array_keys(self::$merchants)));
		if (empty($merchants)) {
			$merchants = self::active_merchants();
		}

		$count_max = 100;
		$count = max(1, min(intval($atts['count']), $count_max));

		reset(self::$templates);
		$template = array_key_exists($atts['template'], self::$templates) ? $atts['template'] : key(self::$templates);

		$_products = array();
		foreach ($merchants as $merchant) {
			$_products[] = call_user_func(array(__CLASS__, 'products_' . $merchant), $keyword, $count_max);
		}

		$count_max = $_products ? max(array_map('count', $_products)) : 0;
		$products = array();
		for ($c = 0; $c < $count_max; $c++) {
			foreach ($_products as &$_product) {
				$_product && array_push($products, array_shift($_product));
			}
		}

		foreach ($products as $p => $product) {
			if (in_array(md5(json_encode($product)), self::$used_products)) {
				unset($products[$p]);
			}
		}
		if (!$products) {
			return;
		}

		$products = array_slice($products, 0, $count);
		foreach ($products as $product) {
			array_push(self::$used_products, md5(json_encode($product)));
		}

		$structured_data = (bool) get_option('bazooka_shopping_structured_data');

		ob_start();
		include plugin_dir_path(__FILE__) . 'views' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $template . '.php';
		return ob_get_clean();
	}

	public static function active_templates()
	{
		reset(self::$templates);
		return self::$templates;
	}

	public static function active_merchants()
	{
		return array_values(array_filter(array_keys(self::$merchants), array(__CLASS__, 'merchant_active')));
	}

	public static function merchant_active($merchant)
	{
		if (!array_key_exists($merchant, self::$merchants)) {
			return false;
		}
		return call_user_func(array(__CLASS__, 'merchant_active_' . $merchant));
	}

	protected static function merchant_active_amazon()
	{

		$tag = trim(get_option('bazooka_shopping_amazon_tag'));
		$key = trim(get_option('bazooka_shopping_amazon_api_key'));
		$secret = trim(get_option('bazooka_shopping_amazon_api_secret'));
		$country = trim(get_option('bazooka_shopping_amazon_country'));
		return !(empty($tag) || empty($key) || empty($secret) || empty($country));
	}

	protected static function products_amazon($keyword, $count)
	{

		$products = array();

		if (!self::merchant_active_amazon()) {
			return $products;
		}

		$tag = trim(get_option('bazooka_shopping_amazon_tag'));
		$key = trim(get_option('bazooka_shopping_amazon_api_key'));
		$secret = trim(get_option('bazooka_shopping_amazon_api_secret'));
		$country = trim(get_option('bazooka_shopping_amazon_country'));
		$tag_guest = trim(get_option('bazooka_shopping_amazon_tag_guest'));

		$transient = array($keyword, $count, $tag, $key, $secret, $country);
		if (!empty($tag_guest) && ($tag_guest != $tag)) {
			$transient[] = $tag_guest;
		}
		$transient = 'bzkshopping_amazon_' . md5(implode('|', $transient));
		if (false !== ($_products = get_transient($transient))) {
			return array_map(array(__CLASS__, 'map_products_cache'), json_decode($_products, true));
		}

		try {
			require_once plugin_dir_path(__FILE__) . 'library' . DIRECTORY_SEPARATOR . 'AwsV4.php';

			$host = 'webservices.amazon.' . $country;
			$path = '/paapi5/searchitems';
			$payload = json_encode(array(
				'PartnerType' => 'Associates',
				'PartnerTag' => $tag,
				'Keywords' => $keyword,
				'SearchIndex' => 'All',
				'Resources' => array(
					'ItemInfo.Title',
					'ItemInfo.Features',
					'ItemInfo.ByLineInfo',
					'ItemInfo.ExternalIds',
					'Images.Primary.Large',
					'Offers.Listings.Price'
				)
			));
			$awsv4 = new Bazzoka_Shopping_AwsV4($key, $secret);
			$regions = array(
				'com.au' => 'us-west-2',
				'com.br' => 'us-east-1',
				'ca' => 'us-east-1',
				'fr' => 'eu-west-1',
				'de' => 'eu-west-1',
				'in' => 'eu-west-1',
				'it' => 'eu-west-1',
				'co.jp' => 'us-west-2',
				'com.mx' => 'us-east-1',
				'es' => 'eu-west-1',
				'com.tr' => 'eu-west-1',
				'ae' => 'eu-west-1',
				'co.uk' => 'eu-west-1',
				'com' => 'us-east-1'
			);
			$awsv4->setRegionName(isset($regions[$country]) ? $regions[$country] : null);
			$awsv4->setServiceName('ProductAdvertisingAPI');
			$awsv4->setPath($path);
			$awsv4->setPayload($payload);
			$awsv4->setRequestMethod('POST');
			$awsv4->addHeader('content-encoding', 'amz-1.0');
			$awsv4->addHeader('content-type', 'application/json; charset=utf-8');
			$awsv4->addHeader('host', $host);
			$awsv4->addHeader('x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems');

			$http = new WP_Http();
			$response = $http->request('https://' . $host . $path, array(
				'method' => 'POST',
				'headers' => $awsv4->getHeaders(),
				'body' => $payload
			));
			if (is_wp_error($response) || $response['response']['code'] != 200) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			$response = json_decode($response['body']);
			if ($response === false || !isset($response->SearchResult->Items[0])) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			foreach ($response->SearchResult->Items as $item) {
				if (!isset($item->DetailPageURL) || !isset($item->ItemInfo->Title->DisplayValue) || !isset($item->Offers->Listings[0]->Price->Amount) || !isset($item->Offers->Listings[0]->Price->Currency) || !isset($item->Images->Primary->Large->URL)) {
					continue;
				}
				$url = $item->DetailPageURL;
				if (!empty($tag_guest) && ($tag_guest != $tag)) {
					$url = preg_replace('/\?.+$/', '?tag=' . $tag_guest, $url);
				}
				$products[] = array(
					'title' => trim($item->ItemInfo->Title->DisplayValue),
					'description' => implode(PHP_EOL, (array) @$item->ItemInfo->Features->DisplayValues),
					'url' => $url,
					'image' => $item->Images->Primary->Large->URL,
					'price' => $item->Offers->Listings[0]->Price->Amount,
					'currency' => $item->Offers->Listings[0]->Price->Currency,
					'discount' => @$item->Offers->Listings[0]->Price->Savings->Percentage,
					'discount_price' => $item->Offers->Listings[0]->Price->Amount + @$item->Offers->Listings[0]->Price->Savings->Amount,
					'merchant' => __('Amazon', 'bazooka-shopping'),
					'merchant_image' => plugin_dir_url(__FILE__) . 'img/amazon.png',
					'brand' => @$item->ItemInfo->ByLineInfo->Brand->DisplayValue,
					'gtin13' => @$item->ItemInfo->ExternalIds->EANs->DisplayValues[0],
					'sku' => @$item->ASIN
				);
				if (count($products) === $count) {
					break;
				}
			}
		} catch (Exception $e) {
			// oops...
		}

		set_transient($transient, json_encode($products), DAY_IN_SECONDS);
		return $products;
	}

	protected static function merchant_active_kelkoo()
	{
		$key = trim(get_option('bazooka_shopping_kelkoo_api_key'));
		$country = trim(get_option('bazooka_shopping_kelkoo_api_country'));
		return !(empty($key) || empty($country));
	}



	protected static function products_kelkoo($keyword, $count)
	{

		// //fonction ternaire pour vérifier si le merchantId est entré dans les options
		//$merchantId = $merchantId ? $merchantId : null;

		$products = array();

		if (!self::merchant_active_kelkoo()) {
			return $products;
		}

		$key = trim(get_option('bazooka_shopping_kelkoo_api_key'));
		$country = trim(get_option('bazooka_shopping_kelkoo_api_country'));
		//ajout de la variable $merchantId
		$merchantId = trim(get_option('bazooka_shopping_kelkoo_filterBy'));
		//TODO:la variable marchantId est optionnelle, si elle n'est pas entrée dans les options, elle prend la valeur null
		$merchantId = $merchantId ? $merchantId : null;



		$transient = 'bzkshopping_kelkoo_' . md5(implode('|', array($keyword, $count, $key, $country, $merchantId)));
		if (false !== ($_products = get_transient($transient))) {
			return array_map(array(__CLASS__, 'map_products_cache'), json_decode($_products, true));
		}
		//si le merchantId est entré dans les options, on l'ajoute dans la requete
		try {
			
			
			if ($merchantId != null) {
				$params = array(
					'country'					=> strtolower($country),
					'query' 					=> $keyword,
					'fieldsAlias' 				=> 'all',
					'pageSize'					=> $count,
					'page'						=> 1,
					'facetValues'				=> 10,
					//filtre sur un marchant//
					'filterBy'					=> 'merchantId:' . $merchantId,
				);
			
			} else {
				$params = array(
					'country'					=> strtolower($country),
					'query' 					=> $keyword,
					'fieldsAlias' 				=> 'all',
					'pageSize'					=> $count,
					'page'						=> 1,
					'facetValues'				=> 10,
				);
			}	

				// $params = array(
				// 	'country'					=> strtolower($country),
				// 	'query' 					=> $keyword,
				// 	'fieldsAlias' 				=> 'all',
				// 	'pageSize'					=> $count,
				// 	'page'						=> 1,
				// 	'facetValues'				=> 10,
				// 	//filtre sur un marchant//
				// 	'filterBy'					=> 'merchantId:' . $merchantId,

				// );

				$remote_kk_url = 'https://api.kelkoogroup.net/publisher/shopping/v2/search/offers';

				$args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $key,
					),
				);
				$response = wp_remote_get($remote_kk_url . '?' . http_build_query($params), $args);


				if (is_wp_error($response) || $response['response']['code'] != 200) {
					set_transient($transient, json_encode($products), DAY_IN_SECONDS);
					return $products;
				}

				$body = wp_remote_retrieve_body($response);
				$data = json_decode($body);

				if ($data === false || !isset($data->offers)) {
					set_transient($transient, json_encode($products), DAY_IN_SECONDS);
					return $products;
				}
				foreach ($data->offers as $item) {
					if (!isset($item->title) || !isset($item->goUrl) || !isset($item->images[0]->url) || !isset($item->price) || !isset($item->currency) || !isset($item->merchant->name)) {
						continue;
					}
					$products[] = array(
						'title' => trim($item->title),
						'url' => trim($item->goUrl),
						'description' => trim(@$item->description),
						'image' => str_replace(array("http:", "width=90&height=90"), array("https:", "width=400&height=400"), trim($item->images[0]->url)),
						'price' => floatval(trim($item->price)),
						'currency' => trim($item->currency),
						'discount' => floatval(trim(@$item->rebatePercentage)),
						'discount_price' => floatval(trim(@$item->priceWithoutRebate)),
						'merchant' => trim($item->merchant->name),
						'merchant_image' => trim(@$item->merchant->logoUrl),
						'brand' => trim(@$item->brand->name),
						'gtin13' => trim(@$item->code->ean),
						'sku' => trim(@$item->code->sku),

					);
					if (count($products) === $count) {
						break;
					}
				}
			} catch (Exception $e) {
				// oops...
			}

			set_transient($transient, json_encode($products), 4 * HOUR_IN_SECONDS);
			return $products;
		
		
	}




	protected static function merchant_active_ebay()
	{

		$campid = trim(get_option('bazooka_shopping_ebay_campid'));
		$country = trim(get_option('bazooka_shopping_ebay_country'));
		return !(empty($campid) || empty($country));
	}

	protected static function products_ebay($keyword, $count)
	{

		$products = array();

		if (!self::merchant_active_ebay()) {
			return $products;
		}

		$campid = trim(get_option('bazooka_shopping_ebay_campid'));
		$country = trim(get_option('bazooka_shopping_ebay_country'));

		$countries = array(
			'at' => array('ebay.at', '5221-53469-19255-0', 'EUR', '.', ',', '16'),
			'au' => array('ebay.com.au', '705-53470-19255-0', 'AUD', ',', '.', '15'),
			'be-nl' => array('benl.ebay.be', '1553-53471-19255-0', 'EUR', '.', ',', '23'),
			'be-fr' => array('befr.ebay.be', '1553-53471-19255-0', 'EUR', ' ', ',', '23'),
			'ca' => array('ebay.ca', '706-53473-19255-0', 'CAD', ',', '.', '2'),
			'de' => array('ebay.de', '707-53477-19255-0', 'EUR', '.', ',', '77'),
			'es' => array('ebay.es', '1185-53479-19255-0', 'EUR', '.', ',', '186'),
			'fr' => array('ebay.fr', '709-53476-19255-0', 'EUR', ' ', ',', '71'),
			'ie' => array('ebay.ie', '5282-53468-19255-0', 'EUR', ',', '.', '205'),
			'it' => array('ebay.it', '724-53478-19255-0', 'EUR', '.', ',', '101'),
			'nl' => array('ebay.nl', '1346-53482-19255-0', 'EUR', '.', ',', '146'),
			'uk' => array('ebay.co.uk', '710-53481-19255-0', 'GBP', ',', '.', '3'),
			//'us' => array('ebay.com', '711-53200-19255-0', 'USD', ',', '.'), @todo:specific xpath
		);
		if (!isset($countries[$country])) {
			return $products;
		}

		$transient = 'bzkshopping_ebay_' . md5(implode('|', array($keyword, $count, $campid, $country)));
		if (false !== ($_products = get_transient($transient))) {
			return array_map(array(__CLASS__, 'map_products_cache'), json_decode($_products, true));
		}

		try {
			$http = new WP_Http();
			$response = $http->request('https://www.' . $countries[$country][0] . '/sch/i.html?' . http_build_query(array('_nkw' => $keyword)), array('user-agent' => 'Mozilla/5.0 (compatible ; Googlebot/2.1 ; +http://www.google.com/bot.html)'));
			if (is_wp_error($response) || $response['response']['code'] != 200) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			$response = @DOMDocument::loadHTML('<?xml encoding="utf-8" ?>' . $response['body']);
			if ($response === false) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			$response = new DOMXPath($response);
			$_products = $response->query('//ul[contains(@class,"srp-results")]//li[contains(@class,"s-item")]');
			$customid = parse_url(home_url(), PHP_URL_HOST);
			foreach ($_products as $item) {
				$title = $response->query('.//h3[contains(@class,"s-item__title")]', $item)->item(0);
				$url = $response->query('.//a[contains(@class,"s-item__link")]', $item)->item(0);
				$image = $response->query('.//img[contains(@class,"s-item__image-img")]', $item)->item(0);
				$price = $response->query('.//span[contains(@class,"s-item__price")]', $item)->item(0);
				if (!isset($title) || !isset($url) || !isset($image) || !isset($price)) {
					continue;
				}
				$description = $response->query('.//div[contains(@class,"s-item__subtitle")]', $item)->item(0);
				$price = trim(str_replace(html_entity_decode('&nbsp;'), ' ', $price->nodeValue));
				preg_match('/(\d+' . $countries[$country][3] . ')*\d+' . $countries[$country][4] . '\d+/', $price, $price);
				$price = floatval(str_replace(array($countries[$country][3], $countries[$country][4]), array('', '.'), current($price)));
				if (empty($price)) {
					continue;
				}
				$discount = 0;
				$discount_price = $response->query('.//span[contains(@class,"s-item__trending-price")]//span[contains(@class,"STRIKETHROUGH")]', $item)->item(0);
				if ($discount_price) {
					$discount_price = trim(str_replace(html_entity_decode('&nbsp;'), ' ', $discount_price->nodeValue));
					preg_match('/(\d+' . $countries[$country][3] . ')*\d+' . $countries[$country][4] . '\d+/', $discount_price, $discount_price);
					$discount_price = floatval(str_replace(array($countries[$country][3], $countries[$country][4]), array('', '.'), current($discount_price)));
					if (!empty($discount_price)) {
						$discount = round(100 - ($price * 100 / $discount_price));
					}
				} else {
					$discount_price = 0;
				}
				$url = explode('?', $url->getAttribute('href'));
				$url = $url[0] . '?' . http_build_query(array('mkcid' => '1', 'mkrid' => $countries[$country][1], 'siteid' => $countries[$country][5], 'campid' => $campid, 'customid' => '', 'toolid' => '10001', 'mkevt' => '1'));
				$products[] = array(
					'title' => trim($title->nodeValue),
					'url' => $url,
					'description' => $description ? trim($description->nodeValue) : null,
					'image' => $image->getAttribute('src'),
					'price' => $price,
					'currency' => $countries[$country][2],
					'discount' => $discount,
					'discount_price' => $discount_price,
					'merchant' => __('eBay', 'bazooka-shopping'),
					'merchant_image' => plugin_dir_url(__FILE__) . 'img/ebay.png',
					'brand' => null,
					'gtin13' => null,
					'sku' => md5($url)
				);
				if (count($products) === $count) {
					break;
				}
			}
		} catch (Exception $e) {
			// oops...
		}

		set_transient($transient, json_encode($products), DAY_IN_SECONDS);
		return $products;
	}


	protected static function merchant_active_manomano()
	{

		$id = trim(get_option('bazooka_shopping_manomano_awin_affiliate_id'));
		$mid = trim(get_option('bazooka_shopping_manomano_awin_merchant_id'));
		return !(empty($id) || empty($mid));
	}

	protected static function products_manomano($keyword, $count)
	{

		$products = array();

		if (!self::merchant_active_manomano()) {
			return $products;
		}

		$id = trim(get_option('bazooka_shopping_manomano_awin_affiliate_id'));
		$mid = trim(get_option('bazooka_shopping_manomano_awin_merchant_id'));

		$mids = array(
			'18448' => array('https://www.manomano.fr/', 'recherche/', 'EUR'),
			'17547' => array('https://www.manomano.fr/', 'recherche/', 'EUR'),
			'17961' => array('https://www.manomano.de/', 'suche/', 'EUR'),
			'17962' => array('https://www.manomano.it/', 'ricerca/', 'EUR'),
			'17963' => array('https://www.manomano.es/', 'busqueda/', 'EUR'),
			'17964' => array('https://www.manomano.co.uk/', 'search/', 'GBP')
		);
		if (!isset($mids[$mid])) {
			return $products;
		}


		$transient = 'bzkshopping_manomano_' . md5(implode('|', array($keyword, $count, $id, $mid)));
		if (false !== ($_products = get_transient($transient))) {
			return array_map(array(__CLASS__, 'map_products_cache'), json_decode($_products, true));
		}

		try {
			$result = wp_remote_head($mids[$mid][0] . $mids[$mid][1] . urlencode($keyword));
			if (isset($result['headers']['location'])) {
				$request = ltrim($result['headers']['location'], '/');
			} else {
				$request = $mids[$mid][1] . urlencode($keyword);
			}

			if (!isset($request)) {
				return $products;
			}

			$ch = curl_init();
			$agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36';
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
			curl_setopt($ch, CURLOPT_TIMEOUT, 300);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $mids[$mid][0] . $request);
			$response = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if (!$response || $httpcode != 200) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			$response = @DOMDocument::loadHTML(utf8_decode($response));

			if ($response === false) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}
			$response = new DOMXPath($response);
			$_products = $response->query('//script[contains(@id,"NEXT_DATA")]')->item(0)->nodeValue;

			$_products = json_decode($_products);
			$_products = $_products->props->pageProps->initialReduxState->productDiscovery->listing->products;

			if (!isset($_products)) {
				set_transient($transient, json_encode($products), DAY_IN_SECONDS);
				return $products;
			}

			$clickref = parse_url(home_url(), PHP_URL_HOST);
			foreach ($_products as $_product) {

				if (!isset($_product->url) || !isset($_product->articleId) || !isset($_product->defaultTitle) || !isset($_product->imageFullpath) || !isset($_product->prices->perItem->actualPrice->withVAT->asFloat)) {
					continue;
				}

				$url = current(explode('#', $_product->url));
				if (strpos($url, 'http') !== 0) {
					$url = $mids[$mid][0] . 'catalogue/p/' . ltrim($url, '/') . '-' . $_product->articleId;
				}
				$url = 'http://www.awin1.com/awclick.php?mid=' . $mid . '&id=' . $id . '&p=' . urlencode($url) . '&clickref=' . $clickref;

				$price = $_product->prices->perItem->actualPrice->withVAT->asFloat;
				$discount_price = $discount = 0;
				if (isset($_product->prices->perItem->retailPrice->withVAT->asFloat)) {
					$discount_price = $_product->prices->perItem->retailPrice->withVAT->asFloat;
					$discount = round(100 - ($price * 100 / $discount_price));
				}

				$products[] = array(
					'title' => trim($_product->defaultTitle),
					'url' => $url,
					'description' => '',
					'image' => $_product->imageFullpath,
					'price' => (float) $price,
					'currency' => $mids[$mid][2],
					'discount' => $discount,
					'discount_price' => (float) @$discount_price,
					'merchant' => __('ManoMano', 'bazooka-shopping'),
					'merchant_image' => plugin_dir_url(__FILE__) . 'img/manomano.jpg',
					'brand' => null,
					'gtin13' => null,
					'sku' => md5($url)
				);
				if (count($products) === $count) {
					break;
				}
			}
			// exit;
		} catch (Exception $e) {
			// oops...
		}

		set_transient($transient, json_encode($products), WEEK_IN_SECONDS);
		return $products;
	}

	protected static function map_products_cache($product)
	{
		$product['merchant_image'] = set_url_scheme($product['merchant_image']);
		return $product;
	}

	public static function currency(array $product = array(), $symbol = false)
	{
		static $symbols = array(
			'EUR' => '€',
			'GBP' => '£',
			'USD' => '$'
		);
		$currency = !empty($product['currency']) ? $product['currency'] : 'EUR';
		if ($symbol) {
			$currency = str_replace(array_keys($symbols), array_values($symbols), $currency);
		}
		return $currency;
	}
}

Bazooka_Shopping::init();
