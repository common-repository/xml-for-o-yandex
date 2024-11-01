<?php if (!defined('ABSPATH')) {exit;}
include_once ABSPATH . 'wp-admin/includes/plugin.php'; // без этого не будет работать вне адмники is_plugin_active
function xfoy_feed_header($numFeed = '1') {
 xfoy_error_log('FEED № '.$numFeed.'; Стартовала xfoy_feed_header; Файл: offer.php; Строка: '.__LINE__, 0);	
 $result_xml = '';
 $result_xml .= '<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
 $result_xml .= '<feed version="1">'.PHP_EOL; 
 $unixtime = current_time('Y-m-d H:i'); // время в unix формате 
 xfoy_optionUPD('xfoy_date_sborki', $unixtime, $numFeed, 'yes', 'set_arr');
 $result_xml .= '<offers>'.PHP_EOL; 
 do_action('xfoy_before_items');
 return $result_xml;
}
/*
* @since 1.0.0
*
* @return array($result_xml, $ids_in_xml)
* @return empty string ''
*/
function xfoy_unit($postId, $numFeed = '1') {	
 xfoy_error_log('FEED № '.$numFeed.'; Стартовала xfoy_unit. $postId = '.$postId.'; Файл: offer.php; Строка: '.__LINE__, 0);	
 $result_xml = ''; $ids_in_xml = ''; $skip_flag = false;

 $product = wc_get_product($postId);
 if ($product == null) {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к get_post вернула null; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}

 if ($product->is_type('grouped')) {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к сгруппированный; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}
 
 // что выгружать
 if ($product->is_type('variable')) {
	$xfoy_whot_export = xfoy_optionGET('xfoy_whot_export', $numFeed, 'set_arr');
	if ($xfoy_whot_export === 'simple') {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к вариативный; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}
 }

 $special_data_for_flag = '';
 $special_data_for_flag = apply_filters('xfoy_special_data_for_flag_filter', $special_data_for_flag, $product, $numFeed);

 $skip_flag = apply_filters('xfoy_skip_flag', $skip_flag, $postId, $product, $special_data_for_flag, $numFeed); 
 if ($skip_flag === true) {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен по флагу; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;} 
 
 if (get_post_meta($postId, 'xfoyp_removefromxml', true) === 'on') {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен принудительно; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}

 /* общие данные для вариативных и обычных товаров */
 $xfoy_address = stripslashes(htmlspecialchars(xfoy_optionGET('xfoy_address', $numFeed, 'set_arr')));
 if ($xfoy_address == '') {
	xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к не указан адрес; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;
 }
 $xfoy_phone = xfoy_optionGET('xfoy_phone', $numFeed, 'set_arr');
 if ($xfoy_phone == '') {
	xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к не указан телефон; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;
 }

 $result_xml_seller = '';
 $result_xml_seller .= '<seller>'.PHP_EOL;
 $result_xml_seller .= '<contacts>'.PHP_EOL;
 $result_xml_seller .= '<phone>'.$xfoy_phone.'</phone>'.PHP_EOL;
 $xfoy_contact_method = xfoy_optionGET('xfoy_contact_method', $numFeed, 'set_arr');
 if ($xfoy_contact_method !== '') {
	$result_xml_seller .= '<contact-method>'.$xfoy_contact_method.'</contact-method>'.PHP_EOL;
 }
 $result_xml_seller .= '</contacts>'.PHP_EOL;
 $result_xml_seller .= '<locations>'.PHP_EOL;
 $result_xml_seller .= '<location>'.PHP_EOL;
 $result_xml_seller .= '<address>'.$xfoy_address.'</address>'.PHP_EOL;
 $result_xml_seller .= '</location>'.PHP_EOL; 
 $result_xml_seller .= '</locations>'.PHP_EOL;
 $result_xml_seller .= '</seller>'.PHP_EOL;

 $result_xml_seller = apply_filters('xfoy_xml_seller', $result_xml_seller, $postId, $product, array($xfoy_address, $xfoy_phone, $xfoy_contact_method), $numFeed);
 
 $result_xml_name = htmlspecialchars($product->get_title(), ENT_NOQUOTES); // название товара
 $result_xml_name = apply_filters('xfoy_change_name', $result_xml_name, $postId, $product, $numFeed);
		  
 // описание
 $xfoy_desc = xfoy_optionGET('xfoy_desc', $numFeed, 'set_arr');
 $xfoy_the_content = xfoy_optionGET('xfoy_the_content', $numFeed, 'set_arr'); 
 $result_xml_desc = ''; 
 switch ($xfoy_desc) { 
	case "full": $description_xml = $product->get_description(); break;
	case "excerpt": $description_xml = $product->get_short_description(); break;
	case "fullexcerpt": 
		$description_xml = $product->get_description(); 
		if (empty($description_xml)) {
			$description_xml = $product->get_short_description();
		}
	break;
	case "excerptfull": 
		$description_xml = $product->get_short_description();		 
		if (empty($description_xml)) {
			$description_xml = $product->get_description();
		} 
	break;
	case "fullplusexcerpt": 
		$description_xml = $product->get_description().'<br/>'.$product->get_short_description();
	break;
	case "excerptplusfull": 
		$description_xml = $product->get_short_description().'<br/>'.$product->get_description(); 
	break;	
	default: $description_xml = $product->get_description();
 }	
 $result_xml_desc = '';
 if (!empty($description_xml)) {
	$enable_tags = '<b>,<i>,<sup>,<sub>,<strong>,<tt>,<br>,<em>,<p>,<ul>,<ol>,<li>';
	$enable_tags = apply_filters('xfoy_enable_tags_filter', $enable_tags, $numFeed);
	if ($xfoy_the_content === 'enabled') {
		$description_xml = html_entity_decode(apply_filters('the_content', $description_xml));
	}	
	$description_xml = strip_tags($description_xml, $enable_tags);
	$description_xml = str_replace('<br>', '<br/>', $description_xml);
	$description_xml = strip_shortcodes($description_xml);
	$description_xml = apply_filters('xfoy_description_filter', $description_xml, $postId, $product, $numFeed);
	$description_xml = apply_filters('xfoy_description_filter_simple', $description_xml, $postId, $product, $numFeed);

	$description_xml = trim($description_xml);
	if ($description_xml !== '') {
		$result_xml_desc = '<description><![CDATA['.$description_xml.']]></description>'.PHP_EOL;
	} 
 }

 // "Категории 
 $catid = '';  
 if (class_exists('WPSEO_Primary_Term')) {		  
	 $catWPSEO = new WPSEO_Primary_Term('product_cat', $postId);
	 $catidWPSEO = $catWPSEO->get_primary_term();	
	 if ($catidWPSEO !== false) { 
	  $catid = $catidWPSEO;
	 } else {
	  $termini = get_the_terms($postId, 'product_cat');	
	  if ($termini !== false) {
	   foreach ($termini as $termin) {
		 $catid = $termin->term_taxonomy_id;
		 break; // т.к. у товара может быть лишь 1 категория - выходим досрочно.
	   }
	  } else { // если база битая. фиксим id категорий
	   xfoy_error_log('FEED № '.$numFeed.'; WARNING: Для товара $postId = '.$postId.' get_the_terms = false. Возможно база битая. Пробуем задействовать wp_get_post_terms; Файл: offer.php; Строка: '.__LINE__, 0);
	   $product_cats = wp_get_post_terms($postId, 'product_cat', array("fields" => "ids"));	  
	   // Раскомментировать строку ниже для автопочинки категорий в БД (место 1 из 2)
	   // wp_set_object_terms($postId, $product_cats, 'product_cat');
	   if (is_array($product_cats) && count($product_cats)) {
		 $catid = $product_cats[0];
		 xfoy_error_log('FEED № '.$numFeed.'; WARNING: Для товара $postId = '.$postId.' база наверняка битая. wp_get_post_terms вернула массив. $catid = '.$catid.'; Файл: offer.php; Строка: '.__LINE__, 0);
	   }
	  }
	 }
  } else {	
	$termini = get_the_terms($postId, 'product_cat');	
	if ($termini !== false) {
	  foreach ($termini as $termin) {
		$catid = $termin->term_taxonomy_id;
		break; // т.к. у товара может быть лишь 1 категория - выходим досрочно.
	  }
	} else { // если база битая. фиксим id категорий
	  xfoy_error_log('FEED № '.$numFeed.'; WARNING: Для товара $postId = '.$postId.' get_the_terms = false. Возможно база битая. Пробуем задействовать wp_get_post_terms; Файл: offer.php; Строка: '.__LINE__, 0);
	  $product_cats = wp_get_post_terms($postId, 'product_cat', array("fields" => "ids"));	  
	  // Раскомментировать строку ниже для автопочинки категорий в БД (место 1 из 2)
	  // wp_set_object_terms($postId, $product_cats, 'product_cat');
	  if (is_array($product_cats) && count($product_cats)) {
		$catid = $product_cats[0];
		xfoy_error_log('FEED № '.$numFeed.'; WARNING: Для товара $postId = '.$postId.' база наверняка битая. wp_get_post_terms вернула массив. $catid = '.$catid.'; Файл: offer.php; Строка: '.__LINE__, 0);
	  }
	}
 }
 /* $termin->ID - понятное дело, ID элемента
 * $termin->slug - ярлык элемента
 * $termin->term_group - значение term group
 * $termin->term_taxonomy_id - ID самой таксономии
 * $termin->taxonomy - название таксономии
 * $termin->description - описание элемента
 * $termin->parent - ID родительского элемента
 * $termin->count - количество содержащихся в нем постов
 */	

 if ($catid == '') {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к нет категорий; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}

 $result_xml_o_yandex_cat = '';
 if (get_term_meta($catid, 'xfoy_o_yandex_product_category', true) !== '') {
	$xfoy_o_yandex_product_category = get_term_meta($catid, 'xfoy_o_yandex_product_category', true);
	$xfoy_o_yandex_product_category = str_replace('_', ' ', $xfoy_o_yandex_product_category);
	$result_xml_o_yandex_cat = '<category>'.htmlspecialchars($xfoy_o_yandex_product_category).'</category>'.PHP_EOL;
 } else {
	xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к отсутствует Category; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;
 }

 if (get_post_meta($postId, '_xfoy_condition', true) === '' || get_post_meta($postId, '_xfoy_condition', true) === 'default') {	
	$xfoy_condition = xfoy_optionGET('xfoy_condition', $numFeed, 'set_arr');
 } else {	 
	$xfoy_condition = get_post_meta($postId, '_xfoy_condition', true);
 }
 $result_xml_condition = '<condition>'.$xfoy_condition.'</condition>'.PHP_EOL;
 /* end общие данные для вариативных и обычных товаров */
 
 $stop_flag = false;
 /* Вариации */
 // если вариация - нам нет смысла выгружать общее предложение
 if ($product->is_type('variable')) {
	xfoy_error_log('FEED № '.$numFeed.'; У нас вариативный товар. Файл: offer.php; Строка: '.__LINE__, 0);	
	$xfoy_var_desc_priority = xfoy_optionGET('xfoy_var_desc_priority', $numFeed, 'set_arr');
	$xfoy_desc = xfoy_optionGET('xfoy_desc', $numFeed, 'set_arr');
	$variations = array();
	if ($product->is_type('variable')) {
		$variations = $product->get_available_variations();
		$variation_count = count($variations);
	} 

	$n = 0; // число вариаций, которые попали в фид
	for ($i = 0; $i<$variation_count; $i++) {
	
		$offer_id = (($product->is_type('variable')) ? $variations[$i]['variation_id'] : $product->get_id());
		$offer = new WC_Product_Variation($offer_id); // получим вариацию
		/*
		* $offer->get_price() - актуальная цена (равна sale_price или regular_price если sale_price пуст)
		* $offer->get_regular_price() - обычная цена
		* $offer->get_sale_price() - цена скидки
		*/
		
		$price_xml = $offer->get_price(); // цена вариации
		$price_xml = apply_filters('xfoy_variable_price_filter', $price_xml, $product, $offer, $offer_id, $numFeed);
		// если цены нет - пропускаем вариацию 			 
		if ($price_xml == 0 || empty($price_xml)) {xfoy_error_log('FEED № '.$numFeed.'; Вариация товара с postId = '.$postId.' пропущена т.к нет цены; Файл: offer.php; Строка: '.__LINE__, 0); continue;}
		
		if (class_exists('XmlforOYandexPro')) {
			if ((xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr') !== false) && (xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr') !== '')) {
			 $xfoyp_compare_value = xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr');
			 $xfoyp_compare = xfoy_optionGET('xfoyp_compare', $numFeed);			 
			 if ($xfoyp_compare == '>=') {
				if ($price_xml < $xfoyp_compare_value) {continue;}
			 } else {
				if ($price_xml >= $xfoyp_compare_value) {continue;}
			 }
			}
		}		

		$thumb_xml = get_the_post_thumbnail_url($offer->get_id(), 'full');
		if (empty($thumb_xml)) {			
			// убираем default.png из фида
			$no_default_png_products = xfoy_optionGET('xfoy_no_default_png_products', $numFeed, 'set_arr');
			if (($no_default_png_products === 'on') && (!has_post_thumbnail($postId))) {$picture_xml = '';} else {
				$thumb_id = get_post_thumbnail_id($postId);
				$thumb_url = wp_get_attachment_image_src($thumb_id,'full', true);	
				$thumb_xml = $thumb_url[0]; /* урл оригинал миниатюры товара */
				$picture_xml = '<image>'.xfoy_deleteGET($thumb_xml).'</image>'.PHP_EOL;
			}
		} else {
			$picture_xml = '<image>'.xfoy_deleteGET($thumb_xml).'</image>'.PHP_EOL;
		}
		$picture_xml = apply_filters('xfoy_pic_variable_offer_filter', $picture_xml, $product, $offer, $numFeed);
			
		// пропускаем вариации без картинок
		if ($picture_xml == '') {	  
			xfoy_error_log('FEED № '.$numFeed.'; Вариация товара с postId = '.$postId.' пропущена т.к нет картинки даже в галерее; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml; /*continue;*/  
		}

		// пропуск вариаций, которых нет в наличии
		$xfoy_skip_missing_products = xfoy_optionGET('xfoy_skip_missing_products', $numFeed, 'set_arr');
		if ($xfoy_skip_missing_products == 'on') {
			if ($offer->is_in_stock() == false) {xfoy_error_log('FEED № '.$numFeed.'; Вариация товара с postId = '.$postId.' пропущена т.к ее нет в наличии; Файл: offer.php; Строка: '.__LINE__, 0); continue;}
		}
					 
		// пропускаем вариации на предзаказ
		$skip_backorders_products = xfoy_optionGET('xfoy_skip_backorders_products', $numFeed, 'set_arr');
		if ($skip_backorders_products == 'on') {
		 if ($offer->get_manage_stock() == true) { // включено управление запасом			  
			if (($offer->get_stock_quantity() < 1) && ($offer->get_backorders() !== 'no')) {xfoy_error_log('FEED № '.$numFeed.'; Вариация товара с postId = '.$postId.' пропущена т.к запрещен предзаказ и включено управление запасом; Файл: offer.php; Строка: '.__LINE__, 0); continue;}
		 }
		}

		$stop_flag = apply_filters('xfoy_before_variable_offer_stop_flag', $stop_flag, $i, $n, $variation_count, $offer_id, $offer, $special_data_for_flag, $numFeed);
		if ($stop_flag == true) {break;}		

		$skip_flag = apply_filters('xfoy_skip_flag_variable', $skip_flag, $postId, $product, $offer, $special_data_for_flag, $numFeed);
		if ($skip_flag === true) {xfoy_error_log('FEED № '.$numFeed.'; Вариация товара с postId = '.$postId.', offer_id = '.$offer_id.' пропущен по флагу; Файл: offer.php; Строка: '.__LINE__, 0); continue;}		
			 
		do_action('xfoy_before_variable_offer', $numFeed);		

		$result_xml .= '<offer>'.PHP_EOL;
		$result_xml .= '<id>'.$offer->get_id().'</id>'.PHP_EOL;	
	
		$xfoy_group_id = xfoy_optionGET('xfoy_group_id', $numFeed, 'set_arr');
		if ($xfoy_group_id === 'enabled') {
			$result_xml .= '<group_id>'.$postId.'</group_id>'.PHP_EOL;
		}

		$result_xml .= $result_xml_seller;
		$result_xml_name = apply_filters('xfoy_change_name_variable', $result_xml_name, $postId, $product, $offer, $numFeed);
		$result_xml .= "<title>".htmlspecialchars(mb_substr($result_xml_name, 0, 79), ENT_NOQUOTES)."</title>".PHP_EOL;

		// Описание.		
		if ($xfoy_var_desc_priority === 'on' || empty($description_xml)) {
			switch ($xfoy_desc) { 
				case "excerptplusfull": 
					$description_xml = $product->get_short_description().'<br/>'.$offer->get_description(); 
				break;
				case "fullplusexcerpt": 
					$description_xml = $offer->get_description().'<br/>'.$product->get_short_description();
				break;	
				default: $description_xml = $offer->get_description();
			}		
		}		
		if (!empty($description_xml)) {
			$enable_tags = '<b>,<i>,<sup>,<sub>,<strong>,<tt>,<br>,<em>,<p>,<ul>,<ol>,<li>';
			$enable_tags = apply_filters('xfoy_enable_tags_filter', $enable_tags, $numFeed);
			$xfoy_the_content = xfoy_optionGET('xfoy_the_content', $numFeed, 'set_arr'); 
			if ($xfoy_the_content === 'enabled') {
				$description_xml = html_entity_decode(apply_filters('the_content', $description_xml));
			}			
			$description_xml = strip_tags($description_xml, $enable_tags);			
			$description_xml = str_replace('<br>', '<br/>', $description_xml);
			$description_xml = strip_shortcodes($description_xml);			
			$description_xml = apply_filters('xfoy_description_filter', $description_xml, $postId, $product, $numFeed);
			$description_xml = apply_filters('xfoy_description_filter_variable', $description_xml, $postId, $product, $offer, $numFeed);
			$description_xml = trim($description_xml);
			if ($description_xml !== '') {
				$result_xml .= '<description><![CDATA['.$description_xml.']]></description>'.PHP_EOL;
			}
		} else {
			// если у вариации нет своего описания - пробуем подставить общее
			if (!empty($result_xml_desc)) {$result_xml .= $result_xml_desc;}
		}
		
		$result_xml .= $result_xml_o_yandex_cat;			

		if ($picture_xml !== '') {
			$result_xml .= '<images>'.PHP_EOL.$picture_xml.'</images>'.PHP_EOL;	
		}
		
		$price_xml = $offer->get_price();
		if (xfoy_salary_check($xfoy_o_yandex_product_category) === false) {
			$result_xml .= '<price>'.round($price_xml, 0, PHP_ROUND_HALF_UP).'</price>'.PHP_EOL;
			$result_xml .= $result_xml_condition;
		} else {
			$result_xml .= '<salary>'.round($price_xml, 0, PHP_ROUND_HALF_UP).'</salary>'.PHP_EOL;
			$result_xml .= '<condition>inapplicable</condition>'.PHP_EOL;
		}
		
		$result_xml = apply_filters('xfoy_append_item_variable', $result_xml, $postId, $product, $offer, $numFeed);

		$result_xml .= '</offer>'.PHP_EOL;
		$n++;

		do_action('xfoy_after_variable_offer');

		$ids_in_xml .= $postId.';'.$offer_id.';'.$price_xml.';'.$catid.PHP_EOL;
		xfoy_error_log('FEED № '.$numFeed.'; Вариация с id = '.$offer->get_id().' загружена. Файл: offer.php; Строка: '.__LINE__, 0);	
		$stop_flag = apply_filters('xfoy_after_variable_offer_stop_flag', $stop_flag, $i, $n, $variation_count, $offer_id, $offer, $special_data_for_flag, $numFeed); 
		if ($stop_flag == true) {xfoy_error_log('FEED № '.$numFeed.'; stop_flag к вариации с id = '.$offer->get_id().' применён. Файл: offer.php; Строка: '.__LINE__, 0); break;}
		xfoy_error_log('FEED № '.$numFeed.'; stop_flag к вариации с id = '.$offer->get_id().' не применялся. Файл: offer.php; Строка: '.__LINE__, 0);
	} // end for ($i = 0; $i<$variation_count; $i++) 
	xfoy_error_log('FEED № '.$numFeed.'; Все вариации выгрузили. '.$ids_in_xml.' Файл: offer.php; Строка: '.__LINE__, 0);	
	
	return array($result_xml, $ids_in_xml); // все вариации выгрузили	
 } // end if ($product->is_type('variable'))	 
 /* end Вариации */

 /* Обычный товар */

 // убираем default.png из фида
 $no_default_png_products = xfoy_optionGET('xfoy_no_default_png_products', $numFeed, 'set_arr');
 if (($no_default_png_products === 'on') && (!has_post_thumbnail($postId))) {$picture_xml = '';} else {
	$thumb_id = get_post_thumbnail_id($postId);
	$thumb_url = wp_get_attachment_image_src($thumb_id, 'full', true);	
	$thumb_xml = $thumb_url[0]; /* урл оригинал миниатюры товара */
	$picture_xml = '<image>'.xfoy_deleteGET($thumb_xml).'</image>'.PHP_EOL;
 }
 $picture_xml = apply_filters('xfoy_pic_simple_offer_filter', $picture_xml, $product, $numFeed);

 // пропускаем товары без картинок
 if ($picture_xml == '') {	  
	xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к нет картинки даже в галерее; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml; /*continue;*/  
 }

 // пропуск товаров, которых нет в наличии
 $xfoy_skip_missing_products = xfoy_optionGET('xfoy_skip_missing_products', $numFeed, 'set_arr');
 if ($xfoy_skip_missing_products == 'on') {
	if ($product->is_in_stock() == false) { xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к нет в наличии; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}
 }		  

 // пропускаем товары на предзаказ
 $skip_backorders_products = xfoy_optionGET('xfoy_skip_backorders_products', $numFeed, 'set_arr');
 if ($skip_backorders_products == 'on') {
	if ($product->get_manage_stock() == true) { // включено управление запасом  
		if (($product->get_stock_quantity() < 1) && ($product->get_backorders() !== 'no')) {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к запрещен предзаказ и включено управление запасом; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml; /*continue;*/}
	} else {
		if ($product->get_stock_status() !== 'instock') { xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к запрещен предзаказ; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml; /*continue;*/}
	}
 }

 $price_xml = $product->get_price();
 $price_xml = apply_filters('xfoy_simple_price_filter', $price_xml, $product, $numFeed);
 if ($price_xml == 0 || empty($price_xml)) {xfoy_error_log('FEED № '.$numFeed.'; Товар с postId = '.$postId.' пропущен т.к нет цены; Файл: offer.php; Строка: '.__LINE__, 0); return $result_xml;}
 if (class_exists('XmlforOYandexPro')) {
	if ((xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr') !== false) && (xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr') !== '')) {
		$xfoyp_compare_value = xfoy_optionGET('xfoyp_compare_value', $numFeed, 'set_arr');
		$xfoyp_compare = xfoy_optionGET('xfoyp_compare', $numFeed);			 
		if ($xfoyp_compare == '>=') {
			if ($price_xml < $xfoyp_compare_value) {return $result_xml;}
		} else {
			if ($price_xml >= $xfoyp_compare_value) {return $result_xml;}
		}
	}
 } 

 $result_xml .= '<offer>'.PHP_EOL;
 $result_xml .= '<id>'.$postId.'</id>'.PHP_EOL;
 $result_xml .= $result_xml_seller;

 $result_xml_name = apply_filters('xfoy_change_name_simple', $result_xml_name, $postId, $product, $numFeed);
 $result_xml .= "<title>".htmlspecialchars(mb_substr($result_xml_name, 0, 79), ENT_NOQUOTES)."</title>".PHP_EOL;
 $result_xml .= $result_xml_desc;
 $result_xml .= $result_xml_o_yandex_cat;

 if ($picture_xml !== '') {
	$result_xml .= '<images>'.PHP_EOL.$picture_xml.'</images>'.PHP_EOL;	
 }

 if (xfoy_salary_check($xfoy_o_yandex_product_category) === false) {
	$result_xml .= $result_xml_condition;
	$result_xml .= '<price>'.round($price_xml, 0, PHP_ROUND_HALF_UP).'</price>'.PHP_EOL;
 } else {
	$result_xml .= '<salary>'.round($price_xml, 0, PHP_ROUND_HALF_UP).'</salary>'.PHP_EOL;
	$result_xml .= '<condition>inapplicable</condition>'.PHP_EOL;
 }

 $result_xml = apply_filters('xfoy_append_item_simple', $result_xml, $postId, $product, $numFeed);

 $result_xml .= '</offer>'.PHP_EOL;
		  
 do_action('xfoy_after_simple_offer');

 $ids_in_xml .= $postId.';'.$postId.';'.$price_xml.';'.$catid.PHP_EOL;

 return array($result_xml, $ids_in_xml);
} // end function xfoy_unit($postId) {
?>