<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugin Name: XML for O.Yandex (Яндекс Объявления)
 * Plugin URI: https://icopydoc.ru/category/documentation/
 * Description: Подключите свой магазин к Яндекс Объявлениям чтобы увеличить продажи!
 * Version: 2.0.6
 * Requires at least: 4.5
 * Requires PHP: 5.6
 * Author: Maxim Glazunov
 * Author URI: https://icopydoc.ru
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: xml-for-o-yandex
 * Domain Path: /languages
 * Tags: xml, yandex, export, woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 9.3.3
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * Copyright 2018-2023 (Author emails: djdiplomat@yandex.ru, support@icopydoc.ru)
 */

require_once plugin_dir_path( __FILE__ ) . '/functions.php'; // Подключаем файл функций
require_once plugin_dir_path( __FILE__ ) . '/offer.php';
;
register_activation_hook( __FILE__, array( 'XmlforOYandex', 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'XmlforOYandex', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'XmlforOYandex', 'on_uninstall' ) );
add_action( 'plugins_loaded', array( 'XmlforOYandex', 'init' ) );
add_action( 'plugins_loaded', 'xfoy_load_plugin_textdomain' ); // load translation
function xfoy_load_plugin_textdomain() {
	load_plugin_textdomain( 'xfoy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
class XmlforOYandex {
	protected static $instance;
	public static function init() {
		is_null( self::$instance ) and self::$instance = new self;
		return self::$instance;
	}

	public function __construct() {
		// xfoy_DIR contains /home/p135/www/site.ru/wp-content/plugins/myplagin/
		define( 'xfoy_DIR', plugin_dir_path( __FILE__ ) );
		// xfoy_URL contains http://site.ru/wp-content/plugins/myplagin/
		define( 'xfoy_URL', plugin_dir_url( __FILE__ ) );
		// xfoy_UPLOAD_DIR contains /home/p256/www/site.ru/wp-content/uploads
		$upload_dir = (object) wp_get_upload_dir();
		define( 'xfoy_UPLOAD_DIR', $upload_dir->basedir );
		// xfoy_UPLOAD_DIR contains /home/p256/www/site.ru/wp-content/uploads/xml-for-o-yandex
		$name_dir = $upload_dir->basedir . "/xml-for-o-yandex";
		define( 'xfoy_NAME_DIR', $name_dir );
		$xfoy_keeplogs = xfoy_optionGET( 'xfoy_keeplogs' );
		define( 'xfoy_KEEPLOGS', $xfoy_keeplogs );
		define( 'xfoy_VER', '2.0.6' );
		$xfoy_version = xfoy_optionGET( 'xfoy_version' );
		if ( $xfoy_version !== xfoy_VER ) {
			xfoy_set_new_options();
		} // автообновим настройки, если нужно	
		if ( ! defined( 'xfoy_ALLNUMFEED' ) ) {
			define( 'xfoy_ALLNUMFEED', '3' );
		}

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_filter( 'upload_mimes', array( $this, 'xfoy_add_mime_types' ) );

		add_filter( 'cron_schedules', array( $this, 'cron_add_seventy_sec' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_add_five_min' ) );
		add_filter( 'cron_schedules', array( $this, 'cron_add_six_hours' ) );

		add_action( 'xfoy_cron_sborki', array( $this, 'xfoy_do_this_seventy_sec' ), 10, 1 );
		add_action( 'xfoy_cron_period', array( $this, 'xfoy_do_this_event' ), 10, 1 );

		// индивидуальные опции доставки товара
		// add_action('add_meta_boxes', array($this, 'xfoy_add_custom_box'));
		add_action( 'save_post', array( $this, 'xfoy_save_post_product_function' ), 50, 3 );
		// пришлось юзать save_post вместо save_post_product ибо wc блочит обновы

		// https://wpruse.ru/woocommerce/custom-fields-in-products/
		// https://wpruse.ru/woocommerce/custom-fields-in-variations/
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'xfoy_added_wc_tabs' ), 10, 1 );
		add_action( 'admin_footer', array( $this, 'xfoy_art_added_tabs_icon' ), 10, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'xfoy_art_added_tabs_panel' ), 10, 1 );

		/* Мета-поля для категорий товаров */
		add_action( "product_cat_edit_form_fields", array( $this, 'xfoy_add_meta_product_cat' ), 10, 1 );
		add_action( 'edited_product_cat', array( $this, 'xfoy_save_meta_product_cat' ), 10, 1 );
		add_action( 'create_product_cat', array( $this, 'xfoy_save_meta_product_cat' ), 10, 1 );

		add_action( 'admin_notices', array( $this, 'xfoy_admin_notices_function' ) );

		/* Регаем стили только для страницы настроек плагина */
		add_action( 'admin_init', function () {
			wp_register_style( 'xfoy-admin-css', plugins_url( 'css/xfoy_style.css', __FILE__ ) );
		}, 9999 );
	}

	public static function xfoy_admin_css_func() {
		/* Ставим css-файл в очередь на вывод */
		wp_enqueue_style( 'xfoy-admin-css' );
	}

	public static function xfoy_admin_head_css_func() {
		/* печатаем css в шапке админки */
		print '<style>/* xml for O.Yandex */
		.metabox-holder .postbox-container .empty-container {height: auto !important;}
		.icp_img1 {background-image: url(' . xfoy_URL . '/img/sl1.jpg);}
		.icp_img2 {background-image: url(' . xfoy_URL . '/img/sl2.jpg);}
		.icp_img3 {background-image: url(' . xfoy_URL . '/img/sl3.jpg);}
		.icp_img4 {background-image: url(' . xfoy_URL . '/img/sl4.jpg);}
		.icp_img5 {background-image: url(' . xfoy_URL . '/img/sl5.jpg);}
		.icp_img6 {background-image: url(' . xfoy_URL . '/img/sl6.jpg);}
		.icp_img7 {background-image: url(' . xfoy_URL . '/img/sl7.jpg);}
	</style>';
	}

	// Срабатывает при активации плагина (вызывается единожды)
	public static function on_activation() {
		$upload_dir = (object) wp_get_upload_dir();
		$name_dir = $upload_dir->basedir . '/xml-for-o-yandex';
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log( 'ERROR: Ошибка создания папки ' . $name_dir . '; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			}
		}
		$numFeed = '1'; // (string)
		$name_dir = $upload_dir->basedir . '/xml-for-o-yandex/feed' . $numFeed;
		if ( ! is_dir( $name_dir ) ) {
			if ( ! mkdir( $name_dir ) ) {
				error_log( 'ERROR: Ошибка создания папки ' . $name_dir . '; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			}
		}
		$xfoy_settings_arr = array(
			'1' => xfoy_set_default_feed_settings_arr()
		);

		$xfoy_registered_feeds_arr = array(
			0 => array( 'last_id' => '1' ),
			1 => array( 'id' => '1' )
		);

		if ( is_multisite() ) {
			add_blog_option( get_current_blog_id(), 'xfoy_version', '2.0.6' );
			add_blog_option( get_current_blog_id(), 'xfoy_keeplogs', '0' );
			add_blog_option( get_current_blog_id(), 'xfoy_disable_notices', '0' );
			add_blog_option( get_current_blog_id(), 'xfoy_enable_five_min', '0' );
			add_blog_option( get_current_blog_id(), 'xfoy_feed_content', '' );

			add_blog_option( get_current_blog_id(), 'xfoy_settings_arr', $xfoy_settings_arr );
			add_blog_option( get_current_blog_id(), 'xfoy_registered_feeds_arr', $xfoy_registered_feeds_arr );
		} else {
			add_option( 'xfoy_version', '2.0.6' );
			add_option( 'xfoy_keeplogs', '0' );
			add_option( 'xfoy_disable_notices', '0' );
			add_option( 'xfoy_enable_five_min', '0' );
			add_option( 'xfoy_feed_content', '' );

			add_option( 'xfoy_settings_arr', $xfoy_settings_arr );
			add_option( 'xfoy_registered_feeds_arr', $xfoy_registered_feeds_arr );
		}
	}

	// Срабатывает при отключении плагина (вызывается единожды)
	public static function on_deactivation() {
		$numFeed = '1'; // (string)
		for ( $i = 1; $i < xfoy_get_number_all_feeds() + 1; $i++ ) {
			wp_clear_scheduled_hook( 'xfoy_cron_period', array( $numFeed ) );
			wp_clear_scheduled_hook( 'xfoy_cron_sborki', array( $numFeed ) );
			$numFeed++;
		}
		deactivate_plugins( 'xml-for-o-yandex-pro/xml-for-o-yandex-pro.php' );
	}

	// Срабатывает при удалении плагина (вызывается единожды)
	public static function on_uninstall() {
		if ( is_multisite() ) {
			delete_blog_option( get_current_blog_id(), 'xfoy_version' );
			delete_blog_option( get_current_blog_id(), 'xfoy_keeplogs' );
			delete_blog_option( get_current_blog_id(), 'xfoy_disable_notices' );
			delete_blog_option( get_current_blog_id(), 'xfoy_enable_five_min' );
			delete_blog_option( get_current_blog_id(), 'xfoy_feed_content' );
			delete_blog_option( get_current_blog_id(), 'xfoy_settings_arr' );
			delete_blog_option( get_current_blog_id(), 'xfoy_registered_feeds_arr' );
		} else {
			delete_option( 'xfoy_version' );
			delete_option( 'xfoy_keeplogs' );
			delete_option( 'xfoy_disable_notices' );
			delete_option( 'xfoy_enable_five_min' );
			delete_option( 'xfoy_feed_content' );
			delete_option( 'xfoy_settings_arr' );
			delete_option( 'xfoy_registered_feeds_arr' );
		}
		if ( is_multisite() ) {
			$xfoy_settings_arr = get_blog_option( get_current_blog_id(), 'xfoy_settings_arr' );
		} else {
			$xfoy_settings_arr = get_option( 'xfoy_settings_arr' );
		}

		if ( $xfoy_settings_arr !== false ) {
			$numFeed = '1';
			for ( $i = 0; $i < count( $xfoy_settings_arr ); $i++ ) {
				xfoy_optionDEL( 'xfoy_status_sborki', $numFeed );
				$numFeed++;
			}
		}
	}

	// Добавляем пункты меню
	public function add_admin_menu() {
		$page_suffix = add_menu_page( null, __( 'Экспорт на Яндекс Объявления', 'xfoy' ), 'manage_options', 'xfoyexport', 'xfoy_export_page', 'dashicons-redo', 51 );
		require_once xfoy_DIR . '/export.php'; // Подключаем файл настроек
		// создаём хук, чтобы стили выводились только на странице настроек
		add_action( 'admin_print_styles-' . $page_suffix, array( $this, 'xfoy_admin_css_func' ) );
		add_action( 'admin_print_styles-' . $page_suffix, array( $this, 'xfoy_admin_head_css_func' ) );

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		require_once plugin_dir_path( __FILE__ ) . '/includes/class_wp_list_table.php';

		add_submenu_page( 'xfoyexport', __( 'Отладка', 'xfoy' ), __( 'Страница отладки', 'xfoy' ), 'manage_options', 'xfoydebug', 'xfoy_debug_page' );
		require_once xfoy_DIR . '/debug.php';
		$page_subsuffix = add_submenu_page( 'xfoyexport', __( 'Добавить расширение', 'xfoy' ), __( 'Расширения', 'xfoy' ), 'manage_options', 'xfoyextensions', 'xfoy_extensions_page' );
		require_once xfoy_DIR . '/extensions.php';
		add_action( 'admin_print_styles-' . $page_subsuffix, array( $this, 'xfoy_admin_css_func' ) );
	}

	// Разрешим загрузку xml и csv файлов
	public function xfoy_add_mime_types( $mimes ) {
		$mimes['csv'] = 'text/csv';
		$mimes['xml'] = 'text/xml';
		return $mimes;
	}

	/* добавляем интервалы крон в 70 секунд и 6 часов */
	public function cron_add_seventy_sec( $schedules ) {
		$schedules['seventy_sec'] = array(
			'interval' => 70,
			'display' => '70 sec'
		);
		return $schedules;
	}
	public function cron_add_five_min( $schedules ) {
		$schedules['five_min'] = array(
			'interval' => 360,
			'display' => '5 min'
		);
		return $schedules;
	}
	public function cron_add_six_hours( $schedules ) {
		$schedules['six_hours'] = array(
			'interval' => 21600,
			'display' => '6 hours'
		);
		return $schedules;
	}
	/* end добавляем интервалы крон в 70 секунд и 6 часов */

	// Сохраняем данные блока, когда пост сохраняется
	function xfoy_save_post_product_function( $post_id, $post, $update ) {
		xfoy_error_log( 'Стартовала функция xfoy_save_post_product_function! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );

		if ( $post->post_type !== 'product' ) {
			return;
		} // если это не товар вукомерц
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		} // если это ревизия
		// проверяем nonce нашей страницы, потому что save_post может быть вызван с другого места.
		// если это автосохранение ничего не делаем
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// проверяем права юзера
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Все ОК. Теперь, нужно найти и сохранить данные
		// Очищаем значение поля input.
		xfoy_error_log( 'Работает функция xfoy_save_post_product_function! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );

		// Убедимся что поле установлено.
		if ( isset( $_POST['_xfoy_condition'] ) ) {
			$xfoy_condition = sanitize_text_field( $_POST['_xfoy_condition'] );

			// Обновляем данные в базе данных
			update_post_meta( $post_id, '_xfoy_condition', $xfoy_condition );
		}

		$numFeed = '1'; // (string) создадим строковую переменную
		// нужно ли запускать обновление фида при перезаписи файла
		$allNumFeed = (int) xfoy_ALLNUMFEED;
		for ( $i = 1; $i < $allNumFeed + 1; $i++ ) {
			xfoy_error_log( 'FEED № ' . $numFeed . '; Шаг $i = ' . $i . ' цикла по формированию кэша файлов; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );

			$result_xml_unit = xfoy_unit( $post_id, $numFeed ); // формируем фид товара
			if ( is_array( $result_xml_unit ) ) {
				$result_xml = $result_xml_unit[0];
				$ids_in_xml = $result_xml_unit[1];
			} else {
				$result_xml = $result_xml_unit;
				$ids_in_xml = '';
			}
			xfoy_wf( $result_xml, $post_id, $numFeed, $ids_in_xml ); // записываем кэш-файл

			$xfoy_ufup = xfoy_optionGET( 'xfoy_ufup', $numFeed, 'set_arr' );
			if ( $xfoy_ufup !== 'on' ) {
				$numFeed++;
				continue; /*return;*/
			}
			$status_sborki = (int) xfoy_optionGET( 'xfoy_status_sborki', $numFeed );
			if ( $status_sborki > -1 ) {
				$numFeed++;
				continue; /*return;*/
			} // если идет сборка фида - пропуск

			$xfoy_date_save_set = xfoy_optionGET( 'xfoy_date_save_set', $numFeed, 'set_arr' );
			$xfoy_date_sborki = xfoy_optionGET( 'xfoy_date_sborki', $numFeed, 'set_arr' );

			if ( $numFeed === '1' ) {
				$prefFeed = '';
			} else {
				$prefFeed = $numFeed;
			}
			if ( is_multisite() ) {
				/*
				 *	wp_get_upload_dir();
				 *   'path'    => '/home/site.ru/public_html/wp-content/uploads/2016/04',
				 *	'url'     => 'http://site.ru/wp-content/uploads/2016/04',
				 *	'subdir'  => '/2016/04',
				 *	'basedir' => '/home/site.ru/public_html/wp-content/uploads',
				 *	'baseurl' => 'http://site.ru/wp-content/uploads',
				 *	'error'   => false,
				 */
				$upload_dir = (object) wp_get_upload_dir();
				$filenamefeed = $upload_dir->basedir . "/xml-for-o-yandex/" . $prefFeed . "feed-o-yandex-" . get_current_blog_id() . ".xml";
			} else {
				$upload_dir = (object) wp_get_upload_dir();
				$filenamefeed = $upload_dir->basedir . "/xml-for-o-yandex/" . $prefFeed . "feed-o-yandex-0.xml";
			}
			if ( ! file_exists( $filenamefeed ) ) {
				xfoy_error_log( 'FEED № ' . $numFeed . '; WARNING: Файла filenamefeed = ' . $filenamefeed . ' не существует! Пропускаем быструю сборку; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				$numFeed++;
				continue; /*return;*/
			} // файла с фидом нет

			clearstatcache(); // очищаем кэш дат файлов
			$last_upd_file = filemtime( $filenamefeed );
			xfoy_error_log( 'FEED № ' . $numFeed . '; $xfoy_date_save_set=' . $xfoy_date_save_set . ';$filenamefeed=' . $filenamefeed, 0 );
			xfoy_error_log( 'FEED № ' . $numFeed . '; Начинаем сравнивать даты! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			if ( $xfoy_date_save_set > $last_upd_file ) {
				// настройки фида сохранялись позже, чем создан фид		
				// нужно полностью пересобрать фид
				xfoy_error_log( 'FEED № ' . $numFeed . '; NOTICE: Настройки фида сохранялись позже, чем создан фид; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				$xfoy_status_cron = xfoy_optionGET( 'xfoy_status_cron', $numFeed );
				$recurrence = $xfoy_status_cron;
				wp_clear_scheduled_hook( 'xfoy_cron_period', array( $numFeed ) );
				wp_schedule_event( time(), $recurrence, 'xfoy_cron_period', array( $numFeed ) );
				xfoy_error_log( 'FEED № ' . $numFeed . '; xfoy_cron_period внесен в список заданий! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			} else { // нужно лишь обновить цены	
				xfoy_error_log( 'FEED № ' . $numFeed . '; NOTICE: Настройки фида сохранялись раньше, чем создан фид. Нужно лишь обновить цены; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				xfoy_clear_file_ids_in_xml( $numFeed ); /* С версии 3.1.0 */
				xfoy_onlygluing( $numFeed );
			}
			$numFeed++;
		}
		return;
	}

	/* функции крона */
	public function xfoy_do_this_seventy_sec( $numFeed = '1' ) {
		xfoy_error_log( 'FEED № ' . $numFeed . '; Крон xfoy_do_this_seventy_sec запущен; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
		$this->xfoy_construct_xml( $numFeed ); // делаем что-либо каждые 70 сек
	}
	public function xfoy_do_this_event( $numFeed = '1' ) {
		xfoy_error_log( 'FEED № ' . $numFeed . '; Крон xfoy_do_this_event включен. Делаем что-то каждый час; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
		$step_export = (int) xfoy_optionGET( 'xfoy_step_export', $numFeed, 'set_arr' );
		if ( $step_export === 0 ) {
			$step_export = 500;
		}
		xfoy_optionUPD( 'xfoy_status_sborki', $step_export, $numFeed );

		wp_clear_scheduled_hook( 'xfoy_cron_sborki', array( $numFeed ) );

		// Возвращает nul/false. null когда планирование завершено. false в случае неудачи.
		$res = wp_schedule_event( time(), 'seventy_sec', 'xfoy_cron_sborki', array( $numFeed ) );
		if ( $res === false ) {
			xfoy_error_log( 'FEED № ' . $numFeed . '; ERROR: Не удалось запланировань CRON seventy_sec; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
		} else {
			xfoy_error_log( 'FEED № ' . $numFeed . '; CRON seventy_sec успешно запланирован; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
		}
	}
	/* end функции крона */

	public static function xfoy_added_wc_tabs( $tabs ) {
		$tabs['xfoy_special_panel'] = array(
			'label' => __( 'Яндекс.Объявления', 'xfoy' ), // название вкладки
			'target' => 'xfoy_added_wc_tabs', // идентификатор вкладки
			'class' => array( 'hide_if_grouped' ), // классы управления видимостью вкладки в зависимости от типа товара
			'priority' => 70, // приоритет вывода
		);
		return $tabs;
	}

	public static function xfoy_art_added_tabs_icon() {
		// https://rawgit.com/woothemes/woocommerce-icons/master/demo.html 
		?>
		<style>
			#woocommerce-coupon-data ul.wc-tabs li.xfoy_special_panel_options a::before,
			#woocommerce-product-data ul.wc-tabs li.xfoy_special_panel_options a::before,
			.woocommerce ul.wc-tabs li.xfoy_special_panel_options a::before {
				font-family: WooCommerce;
				content: "\e014";
			}
		</style>
		<?php
	}

	public static function xfoy_art_added_tabs_panel() {
		global $post; ?>
		<div id="xfoy_added_wc_tabs" class="panel woocommerce_options_panel">
			<?php do_action( 'xfoy_before_options_group', $post ); ?>
			<div class="options_group">
				<h2><strong>
						<?php _e( 'Индивидуальные настройки товара для XML фида для Яндекс.Объявления', 'xfoy' ); ?>
					</strong></h2>
				<?php do_action( 'xfoy_prepend_options_group', $post ); ?>
				<?php
				woocommerce_wp_select( array(
					'id' => '_xfoy_condition',
					'label' => __( 'Состояние товара', 'xfoy' ),
					'placeholder' => '1',
					'description' => __( 'Обязательный элемент', 'xfoy' ) . ' <strong>Condition</strong>',
					'options' => array(
						'default' => __( 'По умолчанию', 'xfoy' ),
						'new' => __( 'Новый', 'xfoy' ),
						'used' => __( 'Б/у', 'xfoy' ),
						'inapplicable' => __( 'Неприменимо', 'xfoy' ),
					),
				) ); ?>
				<?php do_action( 'xfoy_append_options_group', $post ); ?>
			</div>
			<?php do_action( 'xfoy_after_options_group', $post ); ?>
		</div>
		<?php
	}

	public static function xfoy_add_meta_product_cat( $term ) { ?>
		<?php $result_arr = xfoy_option_construct( $term );
		$xfoy_o_yandex_product_category = esc_attr( get_term_meta( $term->term_id, 'xfoy_o_yandex_product_category', 1 ) );
		?>
		<tr class="form-field term-parent-wrap">
			<th scope="row" valign="top"><label>
					<?php _e( 'Категория для Яндекс.Объявления', 'xfoy' ); ?>
				</label></th>
			<td>
				<select name="xfoy_cat_meta[xfoy_o_yandex_product_category]" id="xfoy_o_yandex_product_category">
					<option value="" <?php selected( $xfoy_o_yandex_product_category, '' ); ?>><?php _e( 'Не установлено', 'xfoy' ); ?></option>
					<?php echo $result_arr[0]; ?>
				</select><br />
				<p class="description">
					<?php _e( 'Укажите какой категори на Яндекс.Объявления соответствует данная категория', 'xfoy' ); ?>.
					<?php _e( 'Обязательный элемент', 'xfoy' ); ?> <strong>category</strong>. <a
						href="//yandex.ru/support/o-desktop/price-list-requirements.html" target="_blank">
						<?php _e( 'Подробнее', 'xfoy' ); ?>
					</a>
				</p>
			</td>
		</tr>
		<?php
	}
	/* Сохранение данных в БД */
	function xfoy_save_meta_product_cat( $term_id ) {
		if ( ! isset( $_POST['xfoy_cat_meta'] ) ) {
			return;
		}
		$xfoy_cat_meta = array_map( 'sanitize_text_field', $_POST['xfoy_cat_meta'] );
		foreach ( $xfoy_cat_meta as $key => $value ) {
			if ( empty( $value ) ) {
				delete_term_meta( $term_id, $key );
				continue;
			}
			update_term_meta( $term_id, $key, $value );
		}
		return $term_id;
	}

	// Вывод различных notices
	public function xfoy_admin_notices_function() {
		$numFeed = '1'; // (string) создадим строковую переменную
		// нужно ли запускать обновление фида при перезаписи файла
		$allNumFeed = (int) xfoy_ALLNUMFEED;

		$xfoy_disable_notices = xfoy_optionGET( 'xfoy_disable_notices' );
		if ( $xfoy_disable_notices !== 'on' ) {
			for ( $i = 1; $i < $allNumFeed + 1; $i++ ) {
				$status_sborki = xfoy_optionGET( 'xfoy_status_sborki', $numFeed );
				if ( $status_sborki == false ) {
					$numFeed++;
					continue;
				} else {
					$status_sborki = (int) $status_sborki;
				}
				if ( $status_sborki !== -1 ) {
					$count_posts = wp_count_posts( 'product' );
					$vsegotovarov = $count_posts->publish;
					$step_export = (int) xfoy_optionGET( 'xfoy_step_export', $numFeed, 'set_arr' );
					if ( $step_export === 0 ) {
						$step_export = 500;
					}
					$vobrabotke = $status_sborki - $step_export;
					if ( $vsegotovarov > $vobrabotke ) {
						$vyvod = 'FEED № ' . $numFeed . ' ' . __( 'Прогресс', 'xfoy' ) . ': ' . $vobrabotke . ' ' . __( 'из', 'xfoy' ) . ' ' . $vsegotovarov . ' ' . __( 'товаров', 'xfoy' ) . '.<br />' . __( 'Если индикаторы прогресса не изменились в течение 20 минут, попробуйте уменьшить "Шаг экспорта" в настройках плагина', 'xfoy' );
					} else {
						$vyvod = 'FEED № ' . $numFeed . ' ' . __( 'До завершения менее 70 секунд', 'xfoy' );
					}
					print '<div class="updated notice notice-success is-dismissible"><p>' . __( 'Идет автоматическое создание файла. XML-фид в скором времени будет создан', 'xfoy' ) . '. ' . $vyvod . '.</p></div>';
				}
				$numFeed++;
			}
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			print '<div class="notice error is-dismissible"><p>' . __( 'Для работы требуется плагин WooCommerce', 'xfoy' ) . '!</p></div>';
		}

		if ( isset( $_REQUEST['xfoy_submit_action'] ) ) {
			$run_text = '';
			if ( sanitize_text_field( $_POST['xfoy_run_cron'] ) !== 'off' ) {
				$run_text = '. ' . __( 'Создание XML-фида запущено. Вы можете продолжить работу с сайтом', 'xfoy' );
			}
			print '<div class="updated notice notice-success is-dismissible"><p>' . __( 'Обновлено', 'xfoy' ) . $run_text . '.</p></div>';
		}

		if ( isset( $_REQUEST['xfoy_submit_debug_page'] ) ) {
			print '<div class="updated notice notice-success is-dismissible"><p>' . __( 'Обновлено', 'xfoy' ) . '.</p></div>';
		}

		if ( isset( $_REQUEST['xfoy_submit_clear_logs'] ) ) {
			$upload_dir = (object) wp_get_upload_dir();
			$name_dir = $upload_dir->basedir . "/xml-for-o-yandex";
			$filename = $name_dir . '/xml-for-o-yandex.log';
			$res = unlink( $filename );
			if ( $res == true ) {
				print '<div class="notice notice-success is-dismissible"><p>' . __( 'Логи были очищены', 'xfoy' ) . '.</p></div>';
			} else {
				print '<div class="notice notice-warning is-dismissible"><p>' . __( 'Ошибка доступа к log-файлу. Возможно log-файл был удален ранее', 'xfoy' ) . '.</p></div>';
			}
		}

		/* сброс настроек */
		if ( isset( $_REQUEST['xfoy_submit_reset'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'xfoy_nonce_action_reset', 'xfoy_nonce_field_reset' ) ) {
				$this->on_uninstall();
				$this->on_activation();
				do_action( 'xfoy_submit_reset' );
				print '<div class="updated notice notice-success is-dismissible"><p>' . __( 'Настройки были сброшены', 'xfoy' ) . '.</p></div>';
			}
		} /* end сброс настроек */

		/* отправка отчёта */
		if ( isset( $_REQUEST['xfoy_submit_send_stat'] ) ) {
			if ( ! empty( $_POST ) && check_admin_referer( 'xfoy_nonce_action_send_stat', 'xfoy_nonce_field_send_stat' ) ) {
				if ( is_multisite() ) {
					$xfoy_is_multisite = 'включен';
					$xfoy_keeplogs = get_blog_option( get_current_blog_id(), 'xfoy_keeplogs' );
				} else {
					$xfoy_is_multisite = 'отключен';
					$xfoy_keeplogs = get_option( 'xfoy_keeplogs' );
				}
				$numFeed = '1'; // (string)
				$unixtime = current_time( 'Y-m-d H:i' );
				$mail_content = '<h1>Заявка (#' . $unixtime . ')</h1>';
				$mail_content .= "Версия плагина: " . xfoy_VER . "<br />";
				$mail_content .= "Версия WP: " . get_bloginfo( 'version' ) . "<br />";
				$woo_version = xfoy_get_woo_version_number();
				$mail_content .= "Версия WC: " . $woo_version . "<br />";
				$mail_content .= "Версия PHP: " . phpversion() . "<br />";
				$mail_content .= "Режим мультисайта: " . $xfoy_is_multisite . "<br />";
				$mail_content .= "Вести логи: " . $xfoy_keeplogs . "<br />";
				$mail_content .= 'Расположение логов: <a href="' . $upload_dir['baseurl'] . '/xml-for-o-yandex/xml-for-o-yandex.log" target="_blank">' . $upload_dir['basedir'] . '/xml-for-o-yandex/xml-for-o-yandex.log</a><br />';
				$possible_problems_arr = xfoy_possible_problems_list();
				if ( $possible_problems_arr[1] > 0 ) {
					$possible_problems_arr[3] = str_replace( '<br/>', "<br />", $possible_problems_arr[3] );
					$mail_content .= "Самодиагностика: " . "<br />" . $possible_problems_arr[3];
				} else {
					$mail_content .= "Самодиагностика: Функции самодиагностики не выявили потенциальных проблем" . "<br />";
				}
				if ( ! class_exists( 'XmlforOYandexPro' ) ) {
					$mail_content .= "Pro: не активна" . "<br />";
				} else {
					if ( ! defined( 'xfoyp_VER' ) ) {
						define( 'xfoyp_VER', 'н/д' );
					}
					$mail_content .= "Pro: активна (v " . xfoyp_VER . ")" . "<br />";
				}
				if ( isset( $_REQUEST['xfoy_its_ok'] ) ) {
					$mail_content .= "<br />" . "Помог ли плагин: " . sanitize_text_field( $_REQUEST['xfoy_its_ok'] );
				}
				if ( isset( $_POST['xfoy_email'] ) ) {
					$mail_content .= '<br />Почта: <a href="mailto:' . sanitize_email( $_POST['xfoy_email'] ) . '?subject=XML for O.Yandex (Яндекс Объявления) (#' . $unixtime . ')" target="_blank" rel="nofollow noreferer" title="' . sanitize_email( $_POST['xfoy_email'] ) . '">' . sanitize_email( $_POST['xfoy_email'] ) . '</a>';
				}
				if ( isset( $_POST['xfoy_message'] ) ) {
					$mail_content .= "<br />" . "Сообщение: " . sanitize_text_field( $_POST['xfoy_message'] );
				}
				$argsp = array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', );
				$products = new WP_Query( $argsp );
				$vsegotovarov = $products->found_posts;
				$mail_content .= "<br />" . "Число товаров на выгрузку: " . $vsegotovarov;
				$allNumFeed = (int) xfoy_ALLNUMFEED;
				for ( $i = 1; $i < $allNumFeed + 1; $i++ ) {
					$status_sborki = (int) xfoy_optionGET( 'xfoy_status_sborki', $numFeed );
					$xfoy_file_url = urldecode( xfoy_optionGET( 'xfoy_file_url', $numFeed, 'set_arr' ) );
					$xfoy_file_file = urldecode( xfoy_optionGET( 'xfoy_file_file', $numFeed, 'set_arr' ) );
					$xfoy_whot_export = xfoy_optionGET( 'xfoy_whot_export', $numFeed, 'set_arr' );
					$xfoy_skip_missing_products = xfoy_optionGET( 'xfoy_skip_missing_products', $numFeed, 'set_arr' );
					$xfoy_skip_backorders_products = xfoy_optionGET( 'xfoy_skip_backorders_products', $numFeed, 'set_arr' );
					$xfoy_status_cron = xfoy_optionGET( 'xfoy_status_cron', $numFeed, 'set_arr' );
					$xfoy_ufup = xfoy_optionGET( 'xfoy_ufup', $numFeed, 'set_arr' );
					$xfoy_date_sborki = xfoy_optionGET( 'xfoy_date_sborki', $numFeed, 'set_arr' );
					$xfoy_main_product = xfoy_optionGET( 'xfoy_main_product', $numFeed, 'set_arr' );
					$xfoy_errors = xfoy_optionGET( 'xfoy_errors', $numFeed, 'set_arr' );

					$mail_content .= "<br />" . "ФИД №: " . $numFeed . "<br />" . "<br />";
					$mail_content .= "status_sborki: " . $status_sborki . "<br />";
					$mail_content .= "УРЛ: " . get_site_url() . "<br />";
					$mail_content .= "УРЛ XML-фида: " . $xfoy_file_url . "<br />";
					$mail_content .= "Временный файл: " . $xfoy_file_file . "<br />";
					$mail_content .= "Что экспортировать: " . $xfoy_whot_export . "<br />";
					$mail_content .= "Исключать товары которых нет в наличии (кроме предзаказа): " . $xfoy_skip_missing_products . "<br />";
					$mail_content .= "Исключать из фида товары для предзаказа: " . $xfoy_skip_backorders_products . "<br />";
					$mail_content .= "Автоматическое создание файла: " . $xfoy_status_cron . "<br />";
					$mail_content .= "Обновить фид при обновлении карточки товара: " . $xfoy_ufup . "<br />";
					$mail_content .= "Дата последней сборки XML: " . $xfoy_date_sborki . "<br />";
					$mail_content .= "Что продаёт: " . $xfoy_main_product . "<br />";
					$mail_content .= "Ошибки: " . $xfoy_errors . "<br />";
					$numFeed++;
				}

				add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
				wp_mail( 'support@icopydoc.ru', 'Отчёт XML for O.Yandex (Яндекс Объявления)', $mail_content );
				// Сбросим content-type, чтобы избежать возможного конфликта
				remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

				print '<div class="updated notice notice-success is-dismissible"><p>' . __( 'Данные были отправлены. Спасибо', 'xfoy' ) . '.</p></div>';
			}
		}
		/* end отправка отчёта */
	}

	public static function set_html_content_type() {
		return 'text/html';
	}

	// сборка
	public static function xfoy_construct_xml( $numFeed = '1' ) {
		xfoy_error_log( 'FEED № ' . $numFeed . '; Стартовала xfoy_construct_xml. Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );

		$result_xml = '';
		$status_sborki = (int) xfoy_optionGET( 'xfoy_status_sborki', $numFeed );

		// файл уже собран. На всякий случай отключим крон сборки
		if ( $status_sborki == -1 ) {
			wp_clear_scheduled_hook( 'xfoy_cron_sborki', array( $numFeed ) );
			return;
		}

		$xfoy_date_save_set = xfoy_optionGET( 'xfoy_date_save_set', $numFeed, 'set_arr' );
		if ( $xfoy_date_save_set == '' ) {
			$unixtime = current_time( 'timestamp', 1 ); // 1335808087 - временная зона GMT(Unix формат)
			xfoy_optionUPD( 'xfoy_date_save_set', $unixtime, $numFeed, 'yes', 'set_arr' );
		}
		$xfoy_date_sborki = xfoy_optionGET( 'xfoy_date_sborki', $numFeed, 'set_arr' );

		if ( $numFeed === '1' ) {
			$prefFeed = '';
		} else {
			$prefFeed = $numFeed;
		}
		if ( is_multisite() ) {
			/*
			 * wp_get_upload_dir();
			 * 'path'    => '/home/site.ru/public_html/wp-content/uploads/2016/04',
			 * 'url'     => 'http://site.ru/wp-content/uploads/2016/04',
			 * 'subdir'  => '/2016/04',
			 * 'basedir' => '/home/site.ru/public_html/wp-content/uploads',
			 * 'baseurl' => 'http://site.ru/wp-content/uploads',
			 * 'error'   => false,
			 */
			$upload_dir = (object) wp_get_upload_dir();
			$filenamefeed = $upload_dir->basedir . "/xml-for-o-yandex/" . $prefFeed . "feed-o-yandex-" . get_current_blog_id() . ".xml";
		} else {
			$upload_dir = (object) wp_get_upload_dir();
			$filenamefeed = $upload_dir->basedir . "/xml-for-o-yandex/" . $prefFeed . "feed-o-yandex-0.xml";
		}
		if ( file_exists( $filenamefeed ) ) {
			xfoy_error_log( 'FEED № ' . $numFeed . '; Файл с фидом ' . $filenamefeed . ' есть. Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			// return; // файла с фидом нет
			clearstatcache(); // очищаем кэш дат файлов
			$last_upd_file = filemtime( $filenamefeed );
			xfoy_error_log( 'FEED № ' . $numFeed . '; $xfoy_date_save_set=' . $xfoy_date_save_set . '; $filenamefeed=' . $filenamefeed, 0 );
			xfoy_error_log( 'FEED № ' . $numFeed . '; Начинаем сравнивать даты! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			if ( $xfoy_date_save_set < $last_upd_file ) {
				xfoy_error_log( 'FEED № ' . $numFeed . '; NOTICE: Нужно лишь обновить цены во всём фиде! Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				xfoy_clear_file_ids_in_xml( $numFeed ); /* С версии 3.1.0 */
				xfoy_onlygluing( $numFeed );
				return;
			}
		}
		// далее исходим из того, что файла с фидом нет, либо нужна полная сборка

		$step_export = (int) xfoy_optionGET( 'xfoy_step_export', $numFeed, 'set_arr' );
		if ( $step_export == 0 ) {
			$step_export = 500;
		}

		if ( $status_sborki == $step_export ) { // начинаем сборку файла
			do_action( 'xfoy_before_construct', 'full' ); // сборка стартовала
			$result_xml = xfoy_feed_header( $numFeed );
			/* создаем файл или перезаписываем старый удалив содержимое */
			$result = xfoy_write_file( $result_xml, 'w+', $numFeed );
			if ( $result !== true ) {
				xfoy_error_log( 'FEED № ' . $numFeed . '; xfoy_write_file вернула ошибку! $result =' . $result . '; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				return;
			} else {
				xfoy_error_log( 'FEED № ' . $numFeed . '; xfoy_write_file отработала успешно; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
			}
			xfoy_clear_file_ids_in_xml( $numFeed );
		}
		if ( $status_sborki > 1 ) {
			$result_xml = '';
			$offset = $status_sborki - $step_export;
			$whot_export = xfoy_optionGET( 'xfoy_whot_export', $numFeed, 'set_arr' );
			if ( $whot_export === 'vygruzhat' ) {
				$args = array(
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export,
					'offset' => $offset,
					'relation' => 'AND',
					'meta_query' => array(
						array(
							'key' => '_xfoy_vygruzhat',
							'value' => 'on'
						)
					)
				);
			} else { // if ($whot_export == 'all' || $whot_export == 'simple')
				$args = array(
					'post_type' => 'product',
					'post_status' => 'publish',
					'posts_per_page' => $step_export, // сколько выводить товаров
					'offset' => $offset,
					'relation' => 'AND'
				);
			}

			$args = apply_filters( 'xfoy_query_arg_filter', $args, $numFeed );
			$featured_query = new WP_Query( $args );
			$prod_id_arr = array();
			if ( $featured_query->have_posts() ) {
				for ( $i = 0; $i < count( $featured_query->posts ); $i++ ) {
					// $prod_id_arr[] .= $featured_query->posts[$i]->ID;
					$prod_id_arr[ $i ]['ID'] = $featured_query->posts[ $i ]->ID;
					$prod_id_arr[ $i ]['post_modified_gmt'] = $featured_query->posts[ $i ]->post_modified_gmt;
				}
				wp_reset_query(); /* Remember to reset */
				unset( $featured_query ); // чутка освободим память
				xfoy_gluing( $prod_id_arr, $numFeed );
				$status_sborki = $status_sborki + $step_export;
				xfoy_error_log( 'FEED № ' . $numFeed . '; status_sborki увеличен на ' . $step_export . ' и равен ' . $status_sborki . '; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				xfoy_optionUPD( 'xfoy_status_sborki', $status_sborki, $numFeed );
			} else {
				// если постов нет, пишем концовку файла
				xfoy_error_log( 'FEED № ' . $numFeed . '; Постов больше нет, пишем концовку файла; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				$result_xml = apply_filters( 'xfoy_after_offers_filter', $result_xml, $numFeed );
				$result_xml .= "</offers>" . PHP_EOL . "</feed>";
				/* создаем файл или перезаписываем старый удалив содержимое */
				$result = xfoy_write_file( $result_xml, 'a', $numFeed );
				xfoy_error_log( 'FEED № ' . $numFeed . '; Файл фида готов. Осталось только переименовать временный файл в основной; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				xfoy_rename_file( $numFeed );
				// выставляем статус сборки в "готово"
				$status_sborki = -1;
				if ( $result === true ) {
					xfoy_optionUPD( 'xfoy_status_sborki', $status_sborki, $numFeed );
					// останавливаем крон сборки
					wp_clear_scheduled_hook( 'xfoy_cron_sborki', array( $numFeed ) );
					do_action( 'xfoy_after_construct', 'full' ); // сборка закончена
					xfoy_error_log( 'FEED № ' . $numFeed . '; SUCCESS: Сборка успешно завершена; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
				} else {
					xfoy_error_log( 'FEED № ' . $numFeed . '; ERROR: На завершающем этапе xfoy_write_file вернула ошибку! Я не смог записать концовку файла... $result =' . $result . '; Файл: xml-for-o-yandex.php; Строка: ' . __LINE__, 0 );
					do_action( 'xfoy_after_construct', 'false' ); // сборка закончена
					return;
				}
			} // end if ($featured_query->have_posts())
		} // end if ($status_sborki > 1)
	} // end public static function xfoy_construct_xml
} /* end class XmlforOYandex */