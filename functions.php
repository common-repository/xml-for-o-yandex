<?php if (!defined('ABSPATH')) {exit;}
/*
* С версии 1.0.0
* Добавлен параметр $n
* Записывает или обновляет файл фида.
* Возвращает всегда true
*/
function xfoy_write_file($result_xml, $cc, $numFeed = '1') {
 /* $cc = 'w+' или 'a'; */	 
 xfoy_error_log('FEED № '.$numFeed.'; Стартовала xfoy_write_file c параметром cc = '.$cc.'; Файл: functions.php; Строка: '.__LINE__, 0);
 $filename = urldecode(xfoy_optionGET('xfoy_file_file', $numFeed, 'set_arr'));
 if ($numFeed === '1') {$prefFeed = '';} else {$prefFeed = $numFeed;}

 if ($filename == '') {	
	$upload_dir = (object)wp_get_upload_dir(); // $upload_dir->basedir
	$filename = $upload_dir->basedir."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-0-tmp.xml"; // $upload_dir->path
 }
		
 // if ((validate_file($filename) === 0)&&(file_exists($filename))) {
 if (file_exists($filename)) {
	// файл есть
	if (!$handle = fopen($filename, $cc)) {
		xfoy_error_log('FEED № '.$numFeed.'; Не могу открыть файл '.$filename.'; Файл: functions.php; Строка: '.__LINE__, 0);
		xfoy_errors_log('FEED № '.$numFeed.'; Не могу открыть файл '.$filename.'; Файл: functions.php; Строка: '.__LINE__, 0);
	}
	if (fwrite($handle, $result_xml) === FALSE) {
		xfoy_error_log('FEED № '.$numFeed.'; Не могу произвести запись в файл '.$handle.'; Файл: functions.php; Строка: '.__LINE__, 0);
		xfoy_errors_log('FEED № '.$numFeed.'; Не могу произвести запись в файл '.$handle.'; Файл: functions.php; Строка: '.__LINE__, 0);
	} else {
		xfoy_error_log('FEED № '.$numFeed.'; Ура! Записали; Файл: Файл: functions.php; Строка: '.__LINE__, 0);
		xfoy_error_log($filename, 0);
		return true;
	}
	fclose($handle);
 } else {
	xfoy_error_log('FEED № '.$numFeed.'; Файла $filename = '.$filename.' еще нет. Файл: functions.php; Строка: '.__LINE__, 0);
	// файла еще нет
	// попытаемся создать файл
	if (is_multisite()) {
		$upload = wp_upload_bits($prefFeed.'feed-o-yandex-'.get_current_blog_id().'-tmp.xml', null, $result_xml ); // загружаем shop2_295221-xml в папку загрузок
	} else {
		$upload = wp_upload_bits($prefFeed.'feed-o-yandex-0-tmp.xml', null, $result_xml ); // загружаем shop2_295221-xml в папку загрузок
	}
	/*
	*	для работы с csv или xml требуется в плагине разрешить загрузку таких файлов
	*	$upload['file'] => '/var/www/wordpress/wp-content/uploads/2010/03/feed-xml.xml', // путь
	*	$upload['url'] => 'http://site.ru/wp-content/uploads/2010/03/feed-xml.xml', // урл
	*	$upload['error'] => false, // сюда записывается сообщение об ошибке в случае ошибки
	*/
	// проверим получилась ли запись
	if ($upload['error']) {
		xfoy_error_log('FEED № '.$numFeed.'; Запись вызвала ошибку: '. $upload['error'].'; Файл: functions.php; Строка: '.__LINE__, 0);
		$err = 'FEED № '.$numFeed.'; Запись вызвала ошибку: '. $upload['error'].'; Файл: functions.php; Строка: '.__LINE__ ;
		xfoy_errors_log($err);
	} else {
		xfoy_optionUPD('xfoy_file_file', urlencode($upload['file']), $numFeed, 'yes', 'set_arr');
		xfoy_error_log('FEED № '.$numFeed.'; Запись удалась! Путь файла: '. $upload['file'] .'; УРЛ файла: '. $upload['url'], 0);
		return true;
	}		
 }
}
/*
* С версии 1.0.0
* Перименовывает временный файл фида в основной.
* Возвращает false/true
*/
function xfoy_rename_file($numFeed = '1') {
 xfoy_error_log('FEED № '.$numFeed.'; Cтартовала xfoy_rename_file; Файл: functions.php; Строка: '.__LINE__, 0);	
 if ($numFeed === '1') {$prefFeed = '';} else {$prefFeed = $numFeed;}	
 /* Перименовывает временный файл в основной. Возвращает true/false */
 if (is_multisite()) {
	$upload_dir = (object)wp_get_upload_dir();
	$filenamenew = $upload_dir->basedir."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-".get_current_blog_id().".xml";
	$filenamenewurl = $upload_dir->baseurl."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-".get_current_blog_id().".xml";		
	// $filenamenew = BLOGUPLOADDIR."feed-o-yandex-".get_current_blog_id().".xml";
	// надо придумать как поулчить урл загрузок конкретного блога
 } else {
	$upload_dir = (object)wp_get_upload_dir();
	/*
	*   'path'    => '/home/site.ru/public_html/wp-content/uploads/2016/04',
	*	'url'     => 'http://site.ru/wp-content/uploads/2016/04',
	*	'subdir'  => '/2016/04',
	*	'basedir' => '/home/site.ru/public_html/wp-content/uploads',
	*	'baseurl' => 'http://site.ru/wp-content/uploads',
	*	'error'   => false,
	*/
	$filenamenew = $upload_dir->basedir."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-0.xml";
	$filenamenewurl = $upload_dir->baseurl."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-0.xml";
 }
 $filenameold = urldecode(xfoy_optionGET('xfoy_file_file', $numFeed, 'set_arr'));

 xfoy_error_log('FEED № '.$numFeed.'; $filenameold = '.$filenameold.'; Файл: functions.php; Строка: '.__LINE__, 0);
 xfoy_error_log('FEED № '.$numFeed.'; $filenamenew = '.$filenamenew.'; Файл: functions.php; Строка: '.__LINE__, 0);

 if (rename($filenameold, $filenamenew) === FALSE) {
	xfoy_error_log('FEED № '.$numFeed.'; Не могу переименовать файл из '.$filenameold.' в '.$filenamenew.'! Файл: functions.php; Строка: '.__LINE__, 0);
	return false;
 } else {
	xfoy_optionUPD('xfoy_file_url', urlencode($filenamenewurl), $numFeed, 'yes', 'set_arr');
	xfoy_error_log('FEED № '.$numFeed.'; Файл переименован! Файл: functions.php; Строка: '.__LINE__, 0);
	return true;
 }
}
/*
* С версии 1.0.0
* Возвращает URL без get-параметров или возвращаем только get-параметры
*/	
function xfoy_deleteGET($url, $whot = 'url') {
 $url = str_replace("&amp;", "&", $url); // Заменяем сущности на амперсанд, если требуется
 list($url_part, $get_part) = array_pad(explode("?", $url), 2, ""); // Разбиваем URL на 2 части: до знака ? и после
 if ($whot == 'url') {
	$url_part = str_replace(" ", "%20", $url_part); // заменим пробел на сущность 
	return $url_part; // Возвращаем URL без get-параметров (до знака вопроса)
 } else if ($whot == 'get') {
	return $get_part; // Возвращаем get-параметры (без знака вопроса)
 } else {
	return false;
 }
}
/*
* С версии 1.0.0
* Записывает текст ошибки, чтобы потом можно было отправить в отчет
*/
function xfoy_errors_log($message) {
 if (is_multisite()) {
	update_blog_option(get_current_blog_id(), 'xfoy_errors', $message);
 } else {
	update_option('xfoy_errors', $message);
 }
}
/*
* С версии 1.0.0
* Возвращает версию Woocommerce (string) или (null)
*/ 
function xfoy_get_woo_version_number() {
 // If get_plugins() isn't available, require it
 if (!function_exists('get_plugins')) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php');
 }
 // Create the plugins folder and file variables
 $plugin_folder = get_plugins('/' . 'woocommerce');
 $plugin_file = 'woocommerce.php';
	
 // If the plugin version number is set, return it 
 if (isset( $plugin_folder[$plugin_file]['Version'] ) ) {
	return $plugin_folder[$plugin_file]['Version'];
 } else {	
	return NULL;
 }
}
/*
* С версии 1.0.0
* Возвращает дерево таксономий, обернутое в <option></option>
*/
function xfoy_cat_tree($TermName='', $termID=-1, $value_arr = array(), $separator='', $parent_shown=true) {
 /* 
 * $value_arr - массив id отмеченных ранее select-ов
 */
 $result = '';
 $args = 'hierarchical=1&taxonomy='.$TermName.'&hide_empty=0&orderby=id&parent=';
 if ($parent_shown) {
	$term = get_term($termID , $TermName); 
	$selected = '';
	if (!empty($value_arr)) {
	 foreach ($value_arr as $value) {		
	  if ($value == $term->term_id) {
		$selected = 'selected'; break;
	  }
	 }
	}
	// $result = $separator.$term->name.'('.$term->term_id.')<br/>';
	$result = '<option title="'.$term->name.'; ID: '.$term->term_id.'; '. __('товаров', 'xfoy'). ': '.$term->count.'" class="hover" value="'.$term->term_id.'" '.$selected .'>'.$separator.$term->name.'</option>';
	$parent_shown = false;
 }
 $separator .= '-';  
 $terms = get_terms($TermName, $args . $termID);
 if (count($terms) > 0) {
	foreach ($terms as $term) {
	 $selected = '';
	 if (!empty($value_arr)) {
	  foreach ($value_arr as $value) {
	   if ($value == $term->term_id) {
		$selected = 'selected'; break;
	   }
	  }
	 }
	 $result .= '<option title="'.$term->name.'; ID: '.$term->term_id.'; '. __('товаров', 'xfoy'). ': '.$term->count.'" class="hover" value="'.$term->term_id.'" '.$selected .'>'.$separator.$term->name.'</option>';
	 // $result .=  $separator.$term->name.'('.$term->term_id.')<br/>';
	 $result .= xfoy_cat_tree($TermName, $term->term_id, $value_arr, $separator, $parent_shown);
	}
 }
 return $result; 
}
/*
* @since 1.0.0
*
* @param string $option_name (require)
* @param string $value (require)
* @param string $n (not require)
* @param string $autoload (not require) (yes/no)
* @param string $type (not require)
* @param string $source_settings_name (not require) (@since 2.0.0)
*
* @return true/false
* Возвращает то, что может быть результатом add_blog_option, add_option
*/
function xfoy_optionADD($option_name, $value = '', $n = '', $autoload = 'yes', $type = 'option', $source_settings_name = '') {
	if ($option_name == '') {return false;}
	switch ($type) {
		case "set_arr":
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
			$xfoy_settings_arr[$n][$option_name] = $value;
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), 'xfoy_settings_arr', $xfoy_settings_arr);
			} else {
				return update_option('xfoy_settings_arr', $xfoy_settings_arr, $autoload);
			}
		break;
		case "custom_set_arr":
			if ($source_settings_name === '') {return false;}
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET($source_settings_name);
			$xfoy_settings_arr[$n][$option_name] = $value;
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), $source_settings_name, $xfoy_settings_arr);
			} else {
				return update_option($source_settings_name, $xfoy_settings_arr, $autoload);
			}
		break;
		default:
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return add_blog_option(get_current_blog_id(), $option_name, $value);
			} else {
				return add_option($option_name, $value, '', $autoload);
			}
	}
}
/*
* @since 1.0.0
*
* @param string $option_name (require)
* @param string $value (not require)
* @param string $n (not require)
* @param string $autoload (not require) (yes/no)
* @param string $type (not require)
* @param string $source_settings_name (not require) (@since 2.0.0)
*
* @return true/false
* Возвращает то, что может быть результатом update_blog_option, update_option
*/
function xfoy_optionUPD($option_name, $value = '', $n = '', $autoload = 'yes', $type = '', $source_settings_name = '') {
	if ($option_name == '') {return false;}
	switch ($type) {
		case "set_arr": 
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
			$xfoy_settings_arr[$n][$option_name] = $value;
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), 'xfoy_settings_arr', $xfoy_settings_arr);
			} else {
				return update_option('xfoy_settings_arr', $xfoy_settings_arr, $autoload);
			}
		break;
		case "custom_set_arr": 
			if ($source_settings_name === '') {return false;}
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET($source_settings_name);
			$xfoy_settings_arr[$n][$option_name] = $value;
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), $source_settings_name, $xfoy_settings_arr);
			} else {
				return update_option($source_settings_name, $xfoy_settings_arr, $autoload);
			}
		break;
		default:
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), $option_name, $value);
			} else {
				return update_option($option_name, $value, $autoload);
			}
	}
}
/*
* @since 1.0.0
*
* @param string $option_name (require)
* @param string $n (not require)
* @param string $type (not require)
* @param string $source_settings_name (not require) (@since 2.0.0)
*
* @return Значение опции или false
* Возвращает то, что может быть результатом get_blog_option, get_option
*/
function xfoy_optionGET($option_name, $n = '', $type = '', $source_settings_name = '') {
	if (defined('xfoyp_VER')) {$pro_ver_number = xfoyp_VER;} else {$pro_ver_number = '2.0.0';}
	if (version_compare($pro_ver_number, '2.0.0', '<')) { // если версия PRO ниже 2.0.0
		if ($option_name === 'xfoyp_compare_value') {
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return get_blog_option(get_current_blog_id(), $option_name);
			} else {
				return get_option($option_name);
			}
		}
		if ($option_name === 'xfoyp_compare') {
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return get_blog_option(get_current_blog_id(), $option_name);
			} else {
				return get_option($option_name);
			}
		}
	}

	if ($option_name == '') {return false;}	
	switch ($type) {
		case "set_arr": 
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
			if (isset($xfoy_settings_arr[$n][$option_name])) {
				return $xfoy_settings_arr[$n][$option_name];
			} else {
				return false;
			}
		break;
		case "custom_set_arr":
			if ($source_settings_name === '') {return false;}
			if ($n === '') {$n = '1';}
			$xfoy_settings_arr = xfoy_optionGET($source_settings_name);
			if (isset($xfoy_settings_arr[$n][$option_name])) {
				return $xfoy_settings_arr[$n][$option_name];
			} else {
				return false;
			}
		break;
		case "for_update_option":
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return get_blog_option(get_current_blog_id(), $option_name);
			} else {
				return get_option($option_name);
			}		
		break;
		default:
			/* for old premium versions */
			if ($option_name === 'xfoy_desc') {return xfoy_optionGET($option_name, $n, $type = 'set_arr');}		
			if ($option_name === 'xfoy_no_default_png_products') {return xfoy_optionGET($option_name, $n, $type = 'set_arr');}
			if ($option_name === 'xfoy_whot_export') {return xfoy_optionGET($option_name, $n, $type = 'set_arr');}
			if ($option_name === 'xfoy_feed_assignment') {return xfoy_optionGET($option_name, $n, $type = 'set_arr');}
			/* for old premium versions */
			if ($n === '1') {$n = '';}
			$option_name = $option_name.$n;
			if (is_multisite()) { 
				return get_blog_option(get_current_blog_id(), $option_name);
			} else {
				return get_option($option_name);
			}
	}
}
/*
* @since 1.0.0
*
* @param string $option_name (require)
* @param string $n (not require)
* @param string $type (not require)
* @param string $source_settings_name (not require) (@since 3.6.4)
*
* @return true/false
* Возвращает то, что может быть результатом delete_blog_option, delete_option
*/
function xfoy_optionDEL($option_name, $n = '', $type = '', $source_settings_name = '') {
	if ($option_name == '') {return false;}	 
	switch ($type) {
		case "set_arr": 
			if ($n === '') {$n = '1';} 
			$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
			unset($xfoy_settings_arr[$n][$option_name]);
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), 'xfoy_settings_arr', $xfoy_settings_arr);
			} else {
				return update_option('xfoy_settings_arr', $xfoy_settings_arr);
			}
		break;
		case "custom_set_arr": 
			if ($source_settings_name === '') {return false;}
			if ($n === '') {$n = '1';} 
			$xfoy_settings_arr = xfoy_optionGET($source_settings_name);
			unset($xfoy_settings_arr[$n][$option_name]);
			if (is_multisite()) { 
				return update_blog_option(get_current_blog_id(), $source_settings_name, $xfoy_settings_arr);
			} else {
				return update_option($source_settings_name, $xfoy_settings_arr);
			}
		break;
		default:
		if ($n === '1') {$n = '';} 
		$option_name = $option_name.$n;
		if (is_multisite()) { 
			return delete_blog_option(get_current_blog_id(), $option_name);
		} else {
			return delete_option($option_name);
		}
	}
}  
/*
* @since 1.0.0
* 
* Создает tmp файл-кэш товара
*/
function xfoy_wf($result_xml, $postId, $numFeed = '1', $ids_in_xml = '') {
	$upload_dir = (object)wp_get_upload_dir();
	$name_dir = $upload_dir->basedir.'/xml-for-o-yandex/feed'.$numFeed;
	if (!is_dir($name_dir)) {
		error_log('WARNING: Папки $name_dir ='.$name_dir.' нет; Файл: functions.php; Строка: '.__LINE__, 0);
		if (!mkdir($name_dir)) {
			error_log('ERROR: Создать папку $name_dir ='.$name_dir.' не вышло; Файл: functions.php; Строка: '.__LINE__, 0);
		}
	}
	if (is_dir($name_dir)) {
		$filename = $name_dir.'/'.$postId.'.tmp';
		$fp = fopen($filename, "w");
		fwrite($fp, $result_xml); // записываем в файл текст
		fclose($fp); // закрываем
	
		$filename = $name_dir.'/'.$postId.'-in.tmp';
		$fp = fopen($filename, "w");
		fwrite($fp, $ids_in_xml);
		fclose($fp);		
	} else {
		error_log('ERROR: Нет папки xml-for-o-yandex! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
	}
}
/*
* @since 1.0.0
* Функция склейки/сборки
*/
function xfoy_gluing($id_arr, $numFeed = '1') {
 /*	
 * $id_arr[$i]['ID'] - ID товара
 * $id_arr[$i]['post_modified_gmt'] - Время обновления карточки товара
 * global $wpdb;
 * $res = $wpdb->get_results("SELECT ID, post_modified_gmt FROM $wpdb->posts WHERE post_type = 'product' AND post_status = 'publish'");	
 */	
 xfoy_error_log('FEED № '.$numFeed.'; Стартовала xfoy_gluing; Файл: functions.php; Строка: '.__LINE__, 0);
 if ($numFeed === '1') {$prefFeed = '';} else {$prefFeed = $numFeed;} 

 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir.'/xml-for-o-yandex';
 if (!is_dir($name_dir)) {
  if (!mkdir($name_dir)) {
	 error_log('ERROR: Ошибка создания папки '.$name_dir.'; Файл: xml-for-o-yandex.php; Строка: '.__LINE__, 0);
	 //return false;
  }
 }

 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir.'/xml-for-o-yandex/feed'.$numFeed;
 if (!is_dir($name_dir)) {
	if (!mkdir($name_dir)) {
		error_log('FEED № '.$numFeed.'; Нет папки xfoy! И создать не вышло! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
	} else {
		error_log('FEED № '.$numFeed.'; Создали папку xfoy! Файл: functions.php; Строка: '.__LINE__, 0);
	}
 }
 
 $xfoy_file_file = urldecode(xfoy_optionGET('xfoy_file_file', $numFeed, 'set_arr'));
 $xfoy_file_ids_in_xml = urldecode(xfoy_optionGET('xfoy_file_ids_in_xml', $numFeed, 'set_arr'));

 $xfoy_date_save_set = xfoy_optionGET('xfoy_date_save_set', $numFeed, 'set_arr');
 clearstatcache(); // очищаем кэш дат файлов
 // $prod_id
 foreach ($id_arr as $product) {
	$filename = $name_dir.'/'.$product['ID'].'.tmp';
	$filenameIn = $name_dir.'/'.$product['ID'].'-in.tmp';
	xfoy_error_log('FEED № '.$numFeed.'; RAM '.round(memory_get_usage()/1024, 1).' Кб. ID товара/файл = '.$product['ID'].'.tmp; Файл: functions.php; Строка: '.__LINE__, 0);
	if (is_file($filename) && is_file($filenameIn)) { // if (file_exists($filename)) {
		$last_upd_file = filemtime($filename); // 1318189167			
		if (($last_upd_file < strtotime($product['post_modified_gmt'])) || ($xfoy_date_save_set > $last_upd_file)) {
			// Файл кэша обновлен раньше чем время модификации товара
			// или файл обновлен раньше чем время обновления настроек фида
			xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Файл кэша '.$filename.' обновлен РАНЬШЕ чем время модификации товара или время сохранения настроек фида! Файл: functions.php; Строка: '.__LINE__, 0);	
			$result_xml_unit = xfoy_unit($product['ID'], $numFeed);
			if (is_array($result_xml_unit)) {
				$result_xml = $result_xml_unit[0];
				$ids_in_xml = $result_xml_unit[1];
			} else {
				$result_xml = $result_xml_unit;
				$ids_in_xml = '';
			}	
			xfoy_wf($result_xml, $product['ID'], $numFeed, $ids_in_xml);
			file_put_contents($xfoy_file_file, $result_xml, FILE_APPEND);			
			file_put_contents($xfoy_file_ids_in_xml, $ids_in_xml, FILE_APPEND);
		} else {
			// Файл кэша обновлен позже чем время модификации товара
			// или файл обновлен позже чем время обновления настроек фида
			xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Файл кэша '.$filename.' обновлен ПОЗЖЕ чем время модификации товара или время сохранения настроек фида; Файл: functions.php; Строка: '.__LINE__, 0);
			xfoy_error_log('FEED № '.$numFeed.'; Пристыковываем файл кэша без изменений; Файл: functions.php; Строка: '.__LINE__, 0);
			$result_xml = file_get_contents($filename);
			file_put_contents($xfoy_file_file, $result_xml, FILE_APPEND);
			$ids_in_xml = file_get_contents($filenameIn);
			file_put_contents($xfoy_file_ids_in_xml, $ids_in_xml, FILE_APPEND);
		}
	} else { // Файла нет
		xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Файла кэша товара '.$filename.' ещё нет! Создаем... Файл: functions.php; Строка: '.__LINE__, 0);		
		$result_xml_unit = xfoy_unit($product['ID'], $numFeed);
		if (is_array($result_xml_unit)) {
			$result_xml = $result_xml_unit[0];
			$ids_in_xml = $result_xml_unit[1];
		} else {
			$result_xml = $result_xml_unit;
			$ids_in_xml = '';
		}
		xfoy_wf($result_xml, $product['ID'], $numFeed, $ids_in_xml);
		xfoy_error_log('FEED № '.$numFeed.'; Создали! Файл: functions.php; Строка: '.__LINE__, 0);
		file_put_contents($xfoy_file_file, $result_xml, FILE_APPEND);
		file_put_contents($xfoy_file_ids_in_xml, $ids_in_xml, FILE_APPEND);
	}
 }
} // end function xfoy_gluing()
/*
* @since 1.0.0
* Функция склейки
*/
function xfoy_onlygluing($numFeed = '1') {
 xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Стартовала xfoy_onlygluing; Файл: functions.php; Строка: '.__LINE__, 0); 	
 do_action('xfoy_before_construct', 'cache');
 $result_xml = xfoy_feed_header($numFeed);
 /* создаем файл или перезаписываем старый удалив содержимое */
 $result = xfoy_write_file($result_xml, 'w+', $numFeed);
 if ($result !== true) {
	xfoy_error_log('FEED № '.$numFeed.'; xfoy_write_file вернула ошибку! $result ='.$result.'; Файл: functions.php; Строка: '.__LINE__, 0);
 } 
 
 xfoy_optionUPD('xfoy_status_sborki', '-1', $numFeed); 
 $whot_export = xfoy_optionGET('xfoy_whot_export', $numFeed, 'set_arr');

 $result_xml = '';
 $step_export = -1;
 $prod_id_arr = array(); 
 
 if ($whot_export === 'vygruzhat') {
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => $step_export, // сколько выводить товаров
		// 'offset' => $offset,
		'relation' => 'AND',
		'fields'  => 'ids',
		'meta_query' => array(
			array(
				'key' => '_xfoy_vygruzhat',
				'value' => 'on'
			)
		)
	);	
 } else { //  if ($whot_export == 'all' || $whot_export == 'simple')
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		'posts_per_page' => $step_export, // сколько выводить товаров
		// 'offset' => $offset,
		'relation' => 'AND',
		'fields'  => 'ids'
	);
 }

 $args = apply_filters('xfoy_query_arg_filter', $args, $numFeed);
 xfoy_error_log('FEED № '.$numFeed.'; NOTICE: xfoy_onlygluing до запуска WP_Query RAM '.round(memory_get_usage()/1024, 1) . ' Кб; Файл: functions.php; Строка: '.__LINE__, 0); 
 $featured_query = new WP_Query($args);
 xfoy_error_log('FEED № '.$numFeed.'; NOTICE: xfoy_onlygluing после запуска WP_Query RAM '.round(memory_get_usage()/1024, 1) . ' Кб; Файл: functions.php; Строка: '.__LINE__, 0); 
 
 global $wpdb;
 if ($featured_query->have_posts()) { 
	for ($i = 0; $i < count($featured_query->posts); $i++) {
		/*	
		*	если не юзаем 'fields'  => 'ids'
		*	$prod_id_arr[$i]['ID'] = $featured_query->posts[$i]->ID;
		*	$prod_id_arr[$i]['post_modified_gmt'] = $featured_query->posts[$i]->post_modified_gmt;
		*/
		$curID = $featured_query->posts[$i];
		$prod_id_arr[$i]['ID'] = $curID;

		$res = $wpdb->get_results($wpdb->prepare("SELECT post_modified_gmt FROM $wpdb->posts WHERE id=%d", $curID), ARRAY_A);
		$prod_id_arr[$i]['post_modified_gmt'] = $res[0]['post_modified_gmt']; 	
		// get_post_modified_time('Y-m-j H:i:s', true, $featured_query->posts[$i]);
	}
	wp_reset_query(); /* Remember to reset */
	unset($featured_query); // чутка освободим память
 }
 if (!empty($prod_id_arr)) {
	xfoy_error_log('FEED № '.$numFeed.'; NOTICE: xfoy_onlygluing передала управление xfoy_gluing; Файл: functions.php; Строка: '.__LINE__, 0);
	xfoy_gluing($prod_id_arr, $numFeed);
 }
 
 // если постов нет, пишем концовку файла
 xfoy_error_log('FEED № '.$numFeed.'; Постов больше нет, пишем концовку файла; Файл: functions.php; Строка: '.__LINE__, 0); 
 $result_xml = apply_filters('xfoy_after_offers_filter', $result_xml, $numFeed);
 $result_xml .= "</offers>".PHP_EOL."</feed>";
 /* создаем файл или перезаписываем старый удалив содержимое */
 $result = xfoy_write_file($result_xml, 'a', $numFeed);
 xfoy_rename_file($numFeed);	 
 // выставляем статус сборки в "готово"
 $status_sborki = -1;
 if ($result == true) {
	xfoy_optionGET('xfoy_status_sborki', $status_sborki, $numFeed);	
	// останавливаем крон сборки
	wp_clear_scheduled_hook('xfoy_cron_sborki');
	do_action('xfoy_after_construct', 'cache');
 } else {
	xfoy_error_log('FEED № '.$numFeed.'; xfoy_write_file вернула ошибку! Я не смог записать концовку файла... $result ='.$result.'; Файл: functions.php; Строка: '.__LINE__, 0);
	do_action('xfoy_after_construct', 'false');
 }
} // end function xfoy_onlygluing()
/*
* С версии 1.0.0
* Записывает файл логов /wp-content/uploads/xml-for-o-yandex/xml-for-o-yandex.log
*/
function xfoy_error_log($text, $i) {
 if (xfoy_KEEPLOGS !== 'on') {return;}
 $upload_dir = (object)wp_get_upload_dir();
 $name_dir = $upload_dir->basedir."/xml-for-o-yandex";
 // подготовим массив для записи в файл логов
 if (is_array($text)) {$r = xfoy_array_to_log($text); unset($text); $text = $r;}
 if (is_dir($name_dir)) {
	$filename = $name_dir.'/xml-for-o-yandex.log';
	file_put_contents($filename, '['.date('Y-m-d H:i:s').'] '.$text.PHP_EOL, FILE_APPEND);		
 } else {
	if (!mkdir($name_dir)) {
		error_log('Нет папки xfoy! И создать не вышло! $name_dir ='.$name_dir.'; Файл: functions.php; Строка: '.__LINE__, 0);
	} else {
		error_log('Создали папку xfoy!; Файл: functions.php; Строка: '.__LINE__, 0);
		$filename = $name_dir.'/xml-for-o-yandex.log';
		file_put_contents($filename, '['.date('Y-m-d H:i:s').'] '.$text.PHP_EOL, FILE_APPEND);
	}
 } 
 return;
}
/*
* С версии 1.0.0
* Позволяте писать в логи массив /wp-content/uploads/xml-for-o-yandex/xml-for-o-yandex.log
*/
function xfoy_array_to_log($text, $i=0, $res = '') {
 $tab = ''; for ($x = 0; $x<$i; $x++) {$tab = '---'.$tab;}
 if (is_array($text)) { 
  $i++;
  foreach ($text as $key => $value) {
	if (is_array($value)) {	// массив
		$res .= PHP_EOL .$tab."[$key] => ";
		$res .= $tab.xfoy_array_to_log($value, $i);
	} else { // не массив
		$res .= PHP_EOL .$tab."[$key] => ". $value;
	}
  }
 } else {
	$res .= PHP_EOL .$tab.$text;
 }
 return $res;
}
/*
* С версии 1.0.0
* получить все атрибуты вукомерца 
*/
function xfoy_get_attributes() {
 $result = array();
 $attribute_taxonomies = wc_get_attribute_taxonomies();
 if (count($attribute_taxonomies) > 0) {
	$i = 0;
    foreach($attribute_taxonomies as $one_tax ) {
		/**
		* $one_tax->attribute_id => 6
		* $one_tax->attribute_name] => слаг (на инглише или русском)
		* $one_tax->attribute_label] => Еще один атрибут (это как раз название)
		* $one_tax->attribute_type] => select 
		* $one_tax->attribute_orderby] => menu_order
		* $one_tax->attribute_public] => 0			
		*/
		$result[$i]['id'] = $one_tax->attribute_id;
		$result[$i]['name'] = $one_tax->attribute_label;
		$i++;
    }
 }
 return $result;
}
 
/*
* @since 1.0.0
*
* @param string $numFeed (not require)
*
* @return nothing
* Создает пустой файл ids_in_xml.tmp или очищает уже имеющийся
*/
function xfoy_clear_file_ids_in_xml($numFeed = '1') {
	$xfoy_file_ids_in_xml = urldecode(xfoy_optionGET('xfoy_file_ids_in_xml', $numFeed, 'set_arr'));
	if (!is_file($xfoy_file_ids_in_xml)) {
		xfoy_error_log('FEED № '.$numFeed.'; WARNING: Файла c idшниками $xfoy_file_ids_in_xml = '.$xfoy_file_ids_in_xml.' нет! Создадим пустой; Файл: function.php; Строка: '.__LINE__, 0);

		$upload_dir = (object)wp_get_upload_dir();
		$name_dir = $upload_dir->basedir."/xml-for-o-yandex";

		$xfoy_file_ids_in_xml = $name_dir .'/feed'.$numFeed.'/ids_in_xml.tmp';		
		$res = file_put_contents($xfoy_file_ids_in_xml, '');
		if ($res !== false) {
			xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Файл c idшниками $xfoy_file_ids_in_xml = '.$xfoy_file_ids_in_xml.' успешно создан; Файл: function.php; Строка: '.__LINE__, 0);
			xfoy_optionUPD('xfoy_file_ids_in_xml', urlencode($xfoy_file_ids_in_xml), $numFeed, 'yes', 'set_arr');
		} else {
			xfoy_error_log('FEED № '.$numFeed.'; ERROR: Ошибка создания файла $xfoy_file_ids_in_xml = '.$xfoy_file_ids_in_xml.'; Файл: function.php; Строка: '.__LINE__, 0);
		}
	} else {
		xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Обнуляем файл $xfoy_file_ids_in_xml = '.$xfoy_file_ids_in_xml.'; Файл: function.php; Строка: '.__LINE__, 0);
		file_put_contents($xfoy_file_ids_in_xml, '');
	}
}
/*
* @since 1.0.0
*
* @return nothing
* Обновляет настройки плагина
* Updates plugin settings
*/
function xfoy_set_new_options() {
	wp_clean_plugins_cache();
	wp_clean_update_cache();
	add_filter('pre_site_transient_update_plugins', '__return_null');
	wp_update_plugins();
	remove_filter('pre_site_transient_update_plugins', '__return_null');
		
	$numFeed = '1'; // (string)
	if (!defined('xfoy_ALLNUMFEED')) {define('xfoy_ALLNUMFEED', '3');}
	if (is_multisite()) { 
		if (get_blog_option(get_current_blog_id(), 'xfoy_settings_arr') === false) {$allNumFeed = (int)xfoy_ALLNUMFEED; xfoy_add_settings_arr($allNumFeed);}
	} else {
		if (get_option('xfoy_settings_arr') === false) {$allNumFeed = (int)xfoy_ALLNUMFEED; xfoy_add_settings_arr($allNumFeed);}
	}

	$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
	$xfoy_settings_arr_keys_arr = array_keys($xfoy_settings_arr);
	for ($i = 0; $i < count($xfoy_settings_arr_keys_arr); $i++) {
		$numFeed = (string)$xfoy_settings_arr_keys_arr[$i];
	   	if (!isset($xfoy_settings_arr[$numFeed]['xfoy_currencies'])) {xfoy_optionUPD('xfoy_currencies', 'enabled', $numFeed, 'yes', 'set_arr');}
		if (!isset($xfoy_settings_arr[$numFeed]['xfoy_ebay_stock'])) {xfoy_optionUPD('xfoy_ebay_stock', '0', $numFeed, 'yes', 'set_arr');}
		if (!isset($xfoy_settings_arr[$numFeed]['xfoy_barcode_post_meta_var'])) {xfoy_optionUPD('xfoy_barcode_post_meta_var', '', $numFeed, 'yes', 'set_arr');}
		if (!isset($xfoy_settings_arr[$numFeed]['xfoy_period_of_validity_days'])) {xfoy_optionUPD('xfoy_period_of_validity_days', 'disabled', $numFeed, 'yes', 'set_arr');}
	}

	if (defined('xfoy_VER')) {
		if (is_multisite()) {
			update_blog_option(get_current_blog_id(), 'xfoy_version', xfoy_VER);
		} else {
			update_option('xfoy_version', xfoy_VER);
		}
	}
}
/*
* @since 2.0.1
*
* @return int
* Возвращает количетсво всех фидов
*/
function xfoy_number_all_feeds() {
	$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
	if ($xfoy_settings_arr === false) {
		return -1;
	} else {
		return count($xfoy_settings_arr);
	}
}
/*
* @since 2.0.0
*
* @return nothing
*/
function xfoy_add_settings_arr($allNumFeed) {
	$numFeed = '1';
	for ($i = 1; $i<$allNumFeed+1; $i++) {	 
		wp_clear_scheduled_hook('xfoy_cron_period', array($numFeed));
		wp_clear_scheduled_hook('xfoy_cron_sborki', array($numFeed));
		$numFeed++;
	}

	$xfoy_settings_arr = array();
	$numFeed = '1';

	xfoy_error_log('FEED № '.$numFeed.'; NOTICE: Стартовала xfoy_onlygluing; Файл: functions.php; Строка: '.__LINE__, 0);

	for ($i = 1; $i<$allNumFeed+1; $i++) { 
		xfoy_error_log('xfoy_add_settings_arr1 $i = '.$i.__LINE__, 0);
		$xfoy_settings_arr[$numFeed]['xfoy_status_cron'] = xfoy_optionGET('xfoy_status_cron', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_step_export'] = xfoy_optionGET('xfoy_step_export', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_status_sborki'] = xfoy_optionGET('xfoy_status_sborki',$numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_date_sborki'] = xfoy_optionGET('xfoy_date_sborki', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_file_url'] = xfoy_optionGET('xfoy_file_url', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_file_file'] = xfoy_optionGET('xfoy_file_file', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_file_ids_in_xml'] = xfoy_optionGET('xfoy_file_ids_in_xml', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_magazin_type'] = xfoy_optionGET('xfoy_magazin_type', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_date_save_set'] = xfoy_optionGET('xfoy_date_save_set', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_errors'] = xfoy_optionGET('xfoy_errors', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_run_cron'] = xfoy_optionGET('xfoy_run_cron', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_ufup'] = xfoy_optionGET('xfoy_ufup', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_feed_assignment'] = xfoy_optionGET('xfoy_feed_assignment', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_whot_export'] = xfoy_optionGET('xfoy_whot_export', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_desc'] = xfoy_optionGET('xfoy_desc', $numFeed); 
		$xfoy_settings_arr[$numFeed]['xfoy_the_content'] = xfoy_optionGET('xfoy_the_content', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_var_desc_priority'] = xfoy_optionGET('xfoy_var_desc_priority', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_phone'] = xfoy_optionGET('xfoy_phone', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_address'] = xfoy_optionGET('xfoy_address', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_contact_method'] = xfoy_optionGET('xfoy_contact_method', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_main_product'] = xfoy_optionGET('xfoy_main_product', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_condition'] = xfoy_optionGET('xfoy_condition', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_no_default_png_products'] = xfoy_optionGET('xfoy_no_default_png_products', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_skip_missing_products'] = xfoy_optionGET('xfoy_skip_missing_products', $numFeed);
		$xfoy_settings_arr[$numFeed]['xfoy_skip_backorders_products'] = xfoy_optionGET('xfoy_skip_backorders_products', $numFeed);
		$numFeed++;

		$xfoy_registered_feeds_arr = array(
			0 => array('last_id' => $i),
			1 => array('id' => $i)
		);
	}

	if (is_multisite()) {
		update_blog_option(get_current_blog_id(), 'xfoy_settings_arr', $xfoy_settings_arr);
		update_blog_option(get_current_blog_id(), 'xfoy_registered_feeds_arr', $xfoy_registered_feeds_arr);
	} else {
		update_option('xfoy_settings_arr', $xfoy_settings_arr);
		update_option('xfoy_registered_feeds_arr', $xfoy_registered_feeds_arr);
	}

	$numFeed = '1';
	for ($i = 1; $i<$allNumFeed+1; $i++) { 
		xfoy_optionDEL('xfoy_status_cron', $numFeed);
		xfoy_optionDEL('xfoy_step_export', $numFeed);
	//	xfoy_optionDEL('xfoy_status_sborki', $numFeed);
		xfoy_optionDEL('xfoy_type_sborki', $numFeed);
		xfoy_optionDEL('xfoy_file_url', $numFeed);
		xfoy_optionDEL('xfoy_file_file', $numFeed);
		xfoy_optionDEL('xfoy_file_ids_in_xml', $numFeed);
		xfoy_optionDEL('xfoy_magazin_type', $numFeed);
		xfoy_optionDEL('xfoy_date_save_set', $numFeed);
		xfoy_optionDEL('xfoy_errors', $numFeed);

		xfoy_optionDEL('xfoy_run_cron', $numFeed);
		xfoy_optionDEL('xfoy_ufup', $numFeed);
		xfoy_optionDEL('xfoy_feed_assignment', $numFeed);
		xfoy_optionDEL('xfoy_whot_export', $numFeed); 
		xfoy_optionDEL('xfoy_desc', $numFeed);
		xfoy_optionDEL('xfoy_the_content', $numFeed);
		xfoy_optionDEL('xfoy_var_desc_priority', $numFeed);
		xfoy_optionDEL('xfoy_phone', $numFeed);
		xfoy_optionDEL('xfoy_address', $numFeed);
		xfoy_optionDEL('xfoy_contact_method', $numFeed);

		xfoy_optionDEL('xfoy_main_product', $numFeed);
		xfoy_optionDEL('xfoy_condition', $numFeed);
		xfoy_optionDEL('xfoy_no_default_png_products', $numFeed);
		xfoy_optionDEL('xfoy_skip_missing_products', $numFeed);
		xfoy_optionDEL('xfoy_skip_backorders_products', $numFeed);
		$numFeed++;
	}
	// перезапустим крон-задачи
	for ($i = 1; $i < xfoy_number_all_feeds(); $i++) {
		$numFeed = (string)$i;
		$status_sborki = (int)xfoy_optionGET('xfoy_status_sborki', $numFeed);
		$xfoy_status_cron = xfoy_optionGET('xfoy_status_cron', $numFeed, 'set_arr');
		if ($xfoy_status_cron === 'off') {continue;}
		$recurrence = $xfoy_status_cron;
		wp_clear_scheduled_hook('xfoy_cron_period', array($numFeed));
		wp_schedule_event(time(), $recurrence, 'xfoy_cron_period', array($numFeed));
		xfoy_error_log('FEED № '.$numFeed.'; xfoy_cron_period внесен в список заданий; Файл: export.php; Строка: '.__LINE__, 0);
	}
}
/*
* @since 2.0.0
*
* @return array
* Возвращает массив настроек фида по умолчанию
*/
function xfoy_set_default_feed_settings_arr($whot = 'feed') {
	if ($whot === 'feed') {
		return array(
			'xfoy_status_cron' => 'off',
			'xfoy_step_export' => '500',
			'xfoy_status_sborki' => '-1', // статус сборки файла
			'xfoy_date_sborki' => 'unknown', // дата последней сборки
			'xfoy_file_url' => '', // урл до файла
			'xfoy_file_file' => '', // путь до файла
			'xfoy_file_ids_in_xml' => '',
			'xfoy_magazin_type' => 'woocommerce', // тип плагина магазина
			'xfoy_date_save_set' => 'unknown', // дата сохранения настроек
			'xfoy_errors' => '',
		
			'xfoy_run_cron' => 'off',
			'xfoy_ufup' => '0',
			'xfoy_feed_assignment' => '',
			'xfoy_whot_export' => 'all', // что выгружать (все или там где галка)
			'xfoy_desc' => 'fullexcerpt', 
			'xfoy_the_content' => 'enabled',
			'xfoy_var_desc_priority' => 'on',

			'xfoy_phone' => '',
			'xfoy_address' => '',
			'xfoy_contact_method' => 'any',
		
			'xfoy_main_product' => 'other', // какие товары продаёте
			'xfoy_condition' => 'new',
			'xfoy_no_default_png_products' => '0',
			'xfoy_group_id' => 'disabled',
			'xfoy_skip_missing_products' => '0',
			'xfoy_skip_backorders_products' => '0',
		);
		do_action('xfoy_set_default_feed_settings_result_arr_action', $result_arr, $whot); 
		$result_arr = apply_filters('xfoy_set_default_feed_settings_result_arr_filter', $result_arr, $whot); 	
		return $result_arr;
	} 
}
/*
* @since 1.0.0
*
* @return formatted string
*/
function xfoy_formatSize($bytes) {
	if ($bytes >= 1073741824) {
		   $bytes = number_format($bytes / 1073741824, 2) . ' GB';
	}
	elseif ($bytes >= 1048576) {
		   $bytes = number_format($bytes / 1048576, 2) . ' MB';
	}
	elseif ($bytes >= 1024) {
	   $bytes = number_format($bytes / 1024, 2) . ' KB';
	}
	elseif ($bytes > 1) {
		$bytes = $bytes . ' B';
	}
	elseif ($bytes == 1) {
	   $bytes = $bytes . ' B';
	}
	else {
	   $bytes = '0 B';
	}
	return $bytes;
}
/*
* @since 1.0.0
*
* @return array()
*/
function xfoy_option_construct($term) {
 // https://www.php.net/manual/ru/class.simplexmlelement.php
 $result_arr = array();
 $xml_url = plugin_dir_path(__FILE__).'data/categories.xml';
 $xml_string = file_get_contents($xml_url);
 // $xml_object = simplexml_load_string($xml_string);
 $xml_object = new SimpleXMLElement($xml_string);
   
 $xfoy_o_yandex_product_category = esc_attr(get_term_meta($term->term_id, 'xfoy_o_yandex_product_category', 1)); 
   
 $resultCategory = '';
 $flag = true;
   
 foreach ($xml_object->children() as $second_gen) {
	if ($xfoy_o_yandex_product_category == str_replace(' ', '_', $second_gen['name'])) {$selected = 'selected';} else {$selected = '';}
	$resultCategory .= '<option value="'.str_replace(' ', '_', $second_gen['name']).'" '.$selected.'>'.$second_gen['name'].'</option>'.PHP_EOL;
 }
	
 $result_arr = array($resultCategory, $resultGoodsType, $resultGoodsSubType);
 return $result_arr;
}
/*
* @since 1.0.3
*
* @return array
*/
function xfoy_possible_problems_list() {
 $possibleProblems = ''; $possibleProblemsCount = 0; $conflictWithPlugins = 0; $conflictWithPluginsList = ''; 
 $check_global_attr_count = wc_get_attribute_taxonomies();
 if (count($check_global_attr_count) < 1) {
	$possibleProblemsCount++;
	$possibleProblems .= '<li>'. __('Ваш сайт не имеет глобальных атрибутов! Это может повлиять на качество XML-фида. Это также может вызвать трудности при настройке плагина', 'xfoy'). '. <a href="https://icopydoc.ru/global-and-local-attributes-in-woocommerce/?utm_source=xml-for-o-yandex&utm_medium=organic&utm_campaign=in-plugin-xml-for-o-yandex&utm_content=debug-page&utm_term=no-local-attr">'. __('Пожалуйста, прочитайте рекомендации', 'xfoy'). '</a>.</li>';
 }	
 if (is_plugin_active('snow-storm/snow-storm.php')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Snow Storm<br/>';
 }
 if (is_plugin_active('email-subscribers/email-subscribers.php')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
 }
 if (is_plugin_active('saphali-search-castom-filds/saphali-search-castom-filds.php')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Email Subscribers & Newsletters<br/>';
 }
 if (is_plugin_active('w3-total-cache/w3-total-cache.php')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'W3 Total Cache<br/>';
 }
 if (is_plugin_active('docket-cache/docket-cache.php')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Docket Cache<br/>';
 }					
 if (class_exists('MPSUM_Updates_Manager')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Easy Updates Manager<br/>';
 }
 if (class_exists('OS_Disable_WordPress_Updates')) {
	$possibleProblemsCount++;
	$conflictWithPlugins++;
	$conflictWithPluginsList .= 'Disable All WordPress Updates<br/>';
 }
 if ($conflictWithPlugins > 0) {
	$possibleProblemsCount++;
	$possibleProblems .= '<li><p>'. __('Скорее всего, эти плагины негативно влияют на работу', 'xfoy'). ' XML for O.Yandex (Яндекс Объявления):</p>'.$conflictWithPluginsList.'<p>'. __('Если вы разработчик одного из плагинов из списка выше, пожалуйста, свяжитесь со мной', 'xfoy').': <a href="mailto:support@icopydoc.ru">support@icopydoc.ru</a>.</p></li>';
 }
 return array($possibleProblems, $possibleProblemsCount, $conflictWithPlugins, $conflictWithPluginsList);
}
/*
* @since 1.0.4
*
* @return true/false
*/
function xfoy_salary_check($xfoy_o_yandex_product_category) { 
 $pos = strpos($xfoy_o_yandex_product_category, "Вакансии");
 if ($pos === false) {
	$pos = strpos($xfoy_o_yandex_product_category, "Резюме");
	if ($pos === false) {
		return false;
	} else {
		return true;
	}
 } else {
	return true;
 }
}
/*
* @since 2.0.0
*
* @param string $dir (require)
*
* @return nothing
*/
function xfoy_remove_directory($dir) {
	if ($objs = glob($dir."/*")) {
		foreach($objs as $obj) {
			is_dir($obj) ? xfoy_remove_directory($obj) : unlink($obj);
		}
	}
	rmdir($dir);
}
/*
* @since 2.0.0
*
* @return int
* Возвращает количетсво всех фидов
*/
function xfoy_get_number_all_feeds() {
	$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
	if ($xfoy_settings_arr === false) {
		return -1;
	} else {
		return count($xfoy_settings_arr);
	}
}
?>