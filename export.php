<?php if (!defined('ABSPATH')) {exit;} // Защита от прямого вызова скрипта
function xfoy_export_page() { 
 $numFeed = '1'; // (string)
 if (isset($_REQUEST['xfoy_submit_send_select_feed'])) {
  if (!empty($_POST) && check_admin_referer('xfoy_nonce_action_send_select_feed', 'xfoy_nonce_field_send_select_feed')) {
	$numFeed = $_POST['xfoy_num_feed'];
  } 
 }

 if (isset($_GET['numFeed'])) {
	$numFeed = sanitize_text_field($_GET['numFeed']);
 }

 $status_sborki = (int)xfoy_optionGET('xfoy_status_sborki', $numFeed);
 if (isset($_REQUEST['xfoy_submit_action'])) {
  if (!empty($_POST) && check_admin_referer('xfoy_nonce_action', 'xfoy_nonce_field')) {
	do_action('xfoy_prepend_submit_action', $numFeed);  
	
	$numFeed = sanitize_text_field($_POST['xfoy_num_feed_for_save']);
	
	$unixtime = current_time('timestamp', 1); // 1335808087 - временная зона GMT (Unix формат)
	xfoy_optionUPD('xfoy_date_save_set', $unixtime, $numFeed, 'yes', 'set_arr');

	if (isset($_POST['xfoy_ufup'])) {
		xfoy_optionUPD('xfoy_ufup', sanitize_text_field($_POST['xfoy_ufup']), $numFeed, 'yes', 'set_arr');
	} else {
		xfoy_optionUPD('xfoy_ufup', '0', $numFeed, 'yes', 'set_arr');
	} 
	xfoy_optionUPD('xfoy_whot_export', sanitize_text_field($_POST['xfoy_whot_export']), $numFeed, 'yes', 'set_arr');
	xfoy_optionUPD('xfoy_feed_assignment', sanitize_text_field($_POST['xfoy_feed_assignment']), $numFeed, 'yes', 'set_arr');
	xfoy_optionUPD('xfoy_phone', sanitize_text_field($_POST['xfoy_phone']), $numFeed, 'yes', 'set_arr');
	xfoy_optionUPD('xfoy_address', sanitize_text_field($_POST['xfoy_address']), $numFeed, 'yes', 'set_arr');	
	xfoy_optionUPD('xfoy_contact_method', sanitize_text_field($_POST['xfoy_contact_method']), $numFeed, 'yes', 'set_arr');

	xfoy_optionUPD('xfoy_desc', sanitize_text_field($_POST['xfoy_desc']), $numFeed, 'yes', 'set_arr');
	xfoy_optionUPD('xfoy_the_content', sanitize_text_field($_POST['xfoy_the_content']), $numFeed, 'yes', 'set_arr');
	if (isset($_POST['xfoy_var_desc_priority'])) {
		xfoy_optionUPD('xfoy_var_desc_priority', sanitize_text_field($_POST['xfoy_var_desc_priority']), $numFeed, 'yes', 'set_arr');
	} else {
		xfoy_optionUPD('xfoy_var_desc_priority', '0', $numFeed, 'yes', 'set_arr');
	}
	xfoy_optionUPD('xfoy_main_product', sanitize_text_field($_POST['xfoy_main_product']), $numFeed, 'yes', 'set_arr');
	if (isset($_POST['xfoy_no_default_png_products'])) {
		xfoy_optionUPD('xfoy_no_default_png_products', sanitize_text_field($_POST['xfoy_no_default_png_products']), $numFeed, 'yes', 'set_arr');
	} else {
		xfoy_optionUPD('xfoy_no_default_png_products', '0', $numFeed, 'yes', 'set_arr');
	}
	xfoy_optionUPD('xfoy_group_id', sanitize_text_field($_POST['xfoy_group_id']), $numFeed, 'yes', 'set_arr');
	if (isset($_POST['xfoy_skip_missing_products'])) {
		xfoy_optionUPD('xfoy_skip_missing_products', sanitize_text_field($_POST['xfoy_skip_missing_products']), $numFeed, 'yes', 'set_arr');
	} else {
		xfoy_optionUPD('xfoy_skip_missing_products', '0', $numFeed, 'yes', 'set_arr');
	}	
	if (isset($_POST['xfoy_skip_backorders_products'])) {
		xfoy_optionUPD('xfoy_skip_backorders_products', sanitize_text_field($_POST['xfoy_skip_backorders_products']), $numFeed, 'yes', 'set_arr');
	} else {
		xfoy_optionUPD('xfoy_skip_backorders_products', '0', $numFeed, 'yes', 'set_arr');
	}
	xfoy_optionUPD('xfoy_condition', sanitize_text_field($_POST['xfoy_condition']), $numFeed, 'yes', 'set_arr');

	xfoy_optionUPD('xfoy_step_export', sanitize_text_field($_POST['xfoy_step_export']), $numFeed, 'yes', 'set_arr');	
	$arr_maybe = array("off", "five_min", "hourly", "six_hours", "twicedaily", "daily");
	$xfoy_run_cron = sanitize_text_field($_POST['xfoy_run_cron']);
	if (in_array($xfoy_run_cron, $arr_maybe)) {		
		xfoy_optionUPD('xfoy_status_cron', $xfoy_run_cron, $numFeed, 'yes', 'set_arr');
		if ($xfoy_run_cron === 'off') {
			// отключаем крон
			wp_clear_scheduled_hook('xfoy_cron_period', array($numFeed));
			xfoy_optionUPD('xfoy_status_cron', 'off', $numFeed, 'yes', 'set_arr');
			
			wp_clear_scheduled_hook('xfoy_cron_sborki', array($numFeed));
			xfoy_optionUPD('xfoy_status_sborki', '-1', $numFeed);
		} else {
			$recurrence = $xfoy_run_cron;
			wp_clear_scheduled_hook('xfoy_cron_period', array($numFeed));
			wp_schedule_event(time(), $recurrence, 'xfoy_cron_period', array($numFeed));
			xfoy_error_log('FEED № '.$numFeed.'; xfoy_cron_period внесен в список заданий; Файл: export.php; Строка: '.__LINE__, 0);
		}
	} else {
		xfoy_error_log('Крон '.$xfoy_run_cron.' не зарегистрирован. Файл: export.php; Строка: '.__LINE__, 0);
	}
  }
 } 

 $xfoy_status_cron = xfoy_optionGET('xfoy_status_cron', $numFeed, 'set_arr');
 $xfoy_ufup = xfoy_optionGET('xfoy_ufup', $numFeed, 'set_arr');
 $xfoy_whot_export = xfoy_optionGET('xfoy_whot_export', $numFeed, 'set_arr'); 
 $xfoy_feed_assignment = xfoy_optionGET('xfoy_feed_assignment', $numFeed, 'set_arr'); 
 $xfoy_desc = xfoy_optionGET('xfoy_desc', $numFeed, 'set_arr');
 $xfoy_the_content = xfoy_optionGET('xfoy_the_content', $numFeed, 'set_arr');
 $xfoy_var_desc_priority = xfoy_optionGET('xfoy_var_desc_priority', $numFeed, 'set_arr');
 
 $xfoy_phone = stripslashes(htmlspecialchars(xfoy_optionGET('xfoy_phone', $numFeed, 'set_arr')));
 $xfoy_address = stripslashes(htmlspecialchars(xfoy_optionGET('xfoy_address', $numFeed, 'set_arr')));
 $xfoy_contact_method = stripslashes(htmlspecialchars(xfoy_optionGET('xfoy_contact_method', $numFeed, 'set_arr')));

 $xfoy_main_product = xfoy_optionGET('xfoy_main_product', $numFeed, 'set_arr');
 $xfoy_step_export = xfoy_optionGET('xfoy_step_export', $numFeed, 'set_arr');
 $xfoy_no_default_png_products = xfoy_optionGET('xfoy_no_default_png_products', $numFeed, 'set_arr');
 $xfoy_group_id = xfoy_optionGET('xfoy_group_id', $numFeed, 'set_arr');
 $xfoy_skip_missing_products = xfoy_optionGET('xfoy_skip_missing_products', $numFeed, 'set_arr'); 
 $xfoy_skip_backorders_products = xfoy_optionGET('xfoy_skip_backorders_products', $numFeed, 'set_arr'); 

 $xfoy_condition = xfoy_optionGET('xfoy_condition', $numFeed, 'set_arr');
 
 $xfoy_file_url = urldecode(xfoy_optionGET('xfoy_file_url', $numFeed, 'set_arr'));
 $xfoy_date_sborki = xfoy_optionGET('xfoy_date_sborki', $numFeed, 'set_arr');
?>
<div class="wrap">
 <h1><?php _e('Экспорт O.Yandex', 'xfoy'); ?></h1>
 <div class="notice notice-info">
  <p><span class="xfoy_bold">XML for O.Yandex (Яндекс Объявления) Pro</span> - <?php _e('необходимое расширение для тех, кто хочет', 'xfoy'); ?> <span class="xfoy_bold" style="color: green;"><?php _e('выгружать только часть товаров', 'xfoy'); ?></span> <?php _e('на', 'xfoy'); ?> XML for O.Yandex! <a href="https://icopydoc.ru/product/plagin-xml-for-o-yandex-yandeks-obyavleniya-pro/?utm_source=xml-for-o-yandex&utm_medium=organic&utm_campaign=in-plugin-xml-for-o-yandex&utm_content=settings&utm_term=about-xml-pro"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
 </div>
 <?php do_action('xfoy_before_poststuff', $numFeed); ?>
 <div id="poststuff"><div id="post-body" class="columns-2">
  <div id="postbox-container-1" class="postbox-container"><div class="meta-box-sortables">
  	<?php do_action('xfoy_prepend_container_1', $numFeed); ?>
	<div class="postbox"> 
	 <div class="inside">	
	  <p style="text-align: center;"><strong style="color: green;"><?php _e('Инструкция', 'xfoy'); ?>:</strong> <a href="https://icopydoc.ru/kak-sozdat-fid-dlya-o-yandex-instruktsiya/?utm_source=xml-for-o-yandex&utm_medium=organic&utm_campaign=in-plugin-xml-for-o-yandex&utm_content=settings&utm_term=main-instruction" target="_blank"><?php _e('Как создать XML-фид', 'xfoy'); ?></a>.</p>
	  <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">	
		<select style="width: 100%" name="xfoy_num_feed" id="xfoy_num_feed">
			<?php if (is_multisite()) {$cur_blog_id = get_current_blog_id();} else {$cur_blog_id = '0';}		
			$allNumFeed = (int)xfoy_ALLNUMFEED; $ii = '1';
			for ($i = 1; $i<$allNumFeed+1; $i++) : ?>
			<option value="<?php echo $i; ?>" <?php selected($numFeed, $i); ?>><?php _e('Фид', 'xfoy'); ?> <?php echo $i; ?>: feed-o-yandex-<?php echo $cur_blog_id; ?>.xml <?php $assignment = xfoy_optionGET('xfoy_feed_assignment', $ii); if ($assignment === '') {} else {echo '('.$assignment.')';} ?></option>
			<?php $ii++; endfor; ?>
		</select>
		<?php wp_nonce_field('xfoy_nonce_action_send_select_feed', 'xfoy_nonce_field_send_select_feed'); ?>
		<input style="width: 100%; margin: 10px 0 10px 0;" class="button" type="submit" name="xfoy_submit_send_select_feed" value="<?php _e('Выбрать фид', 'xfoy'); ?>" />
	  </form>
  	 </div>
	</div>
	<?php do_action('xfoy_before_support_project'); ?>
	<div class="postbox">
	 <h2 class="hndle"><?php _e('Пожалуйста, поддержите проект', 'xfoy'); ?>!</h2>
	 <div class="inside">	  
		<p><?php _e('Спасибо за использование плагина', 'xfoy'); ?> <strong>XML for O.Yandex</strong></p>
		<p><?php _e('Пожалуйста, помогите сделать плагин лучше', 'xfoy'); ?> <a href="https://forms.gle/BQPrHrgBEzBqSYbT7" target="_blank" ><?php _e('ответив на 6 вопросов', 'xfoy'); ?>!</a></p>
		<p><?php _e('Если этот плагин полезен вам, пожалуйста, поддержите проект', 'xfoy'); ?>:</p>
		<ul class="xfoy_ul">
			<li><a href="https://wordpress.org/support/plugin/xml-for-o-yandex/reviews/" target="_blank"><?php _e('Оставьте комментарий на странице плагина', 'xfoy'); ?></a>.</li>
			<li><?php _e('Поддержите проект деньгами', 'xfoy'); ?>. <a href="https://sobe.ru/na/xml_for_o_yandex" target="_blank"> <?php _e('Поддержать проект', 'xfoy'); ?></a>.</li>
			<li><?php _e('Заметили ошибку или есть идея как улучшить качество плагина', 'xfoy'); ?>? <a href="mailto:support@icopydoc.ru"><?php _e('Напишите мне', 'xfoy'); ?></a>.</li>
		</ul>
		<p><?php _e('С уважением, Максим Глазунов', 'xfoy'); ?>.</p>
		<p><span style="color: red;"><?php _e('Принимаю заказы на индивидуальные доработки плагина', 'xfoy'); ?></span>:<br /><a href="mailto:support@icopydoc.ru"><?php _e('Оставить заявку', 'xfoy'); ?></a>.</p>
	  </div>
	</div>		
	<?php do_action('xfoy_between_container_1', $numFeed); ?>
	<div class="postbox">
	<h2 class="hndle"><?php _e('Отправить данные о работе плагина', 'xfoy'); ?></h2>
	  <div class="inside">
		<p><?php _e('Отправляя статистику, вы помогаете сделать плагин еще лучше', 'xfoy'); ?>! <?php _e('Следующие данные будут переданы', 'xfoy'); ?>:</p>
		<ul class="xfoy_ul">
			<li><?php _e('УРЛ XML-фида', 'xfoy'); ?>;</li>
			<li><?php _e('Статус генерации фида', 'xfoy'); ?>;</li>
			<li><?php _e('Включен ли режим multisite', 'xfoy'); ?>?</li>
		</ul>
		<p><?php _e('Помог ли Вам плагин загрузить продукцию на', 'xfoy'); ?> O.Yandex?</p>
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
		 <input type="hidden" name="xfoy_num_feed_for_save" value="<?php echo $numFeed; ?>">
		 <p>
			<input type="radio" name="xfoy_its_ok" value="yes"><?php _e('Да', 'xfoy'); ?><br />
			<input type="radio" name="xfoy_its_ok" value="no"><?php _e('Нет', 'xfoy'); ?>
		 </p>
		 <p><?php _e("Если вы не возражаете, чтобы с Вами связались в случае возникновения дополнительных вопросов по поводу работы плагина, то укажите Ваш адрес электронной почты", "xfoy"); ?>. <span class="xfoy_bold"><?php _e('И если вы хотите получить ответ, не забудьте указать свой адрес электронной почты', 'xfoy'); ?></span>.</p>
		 <p><input type="email" name="xfoy_email"></p>
		 <p><?php _e("Ваше сообщение", "xfoy"); ?>:</p>
		 <p><textarea rows="6" cols="32" name="xfoy_message" placeholder="<?php _e('Введите текст, чтобы отправить мне сообщение (Вы можете написать мне на русском или английском языке). Я проверяю свою электронную почту несколько раз в день', 'xfoy'); ?>"></textarea></p>
		 <?php wp_nonce_field('xfoy_nonce_action_send_stat', 'xfoy_nonce_field_send_stat'); ?><input class="button-primary" type="submit" name="xfoy_submit_send_stat" value="<?php _e('Отправить данные', 'xfoy'); ?>" />
	  </form>
	  </div>
	</div>
	<?php do_action('xfoy_append_container_1', $numFeed); ?>
  </div></div>

  <div id="postbox-container-2" class="postbox-container"><div class="meta-box-sortables">
  	<?php do_action('xfoy_prepend_container_2', $numFeed); ?>
	  <div class="postbox">
	 <h2 class="hndle"><?php _e('Фид', 'xfoy'); if (is_multisite()) {$cur_blog_id = get_current_blog_id();} else {$cur_blog_id = '0';} ?> <?php echo $numFeed; ?>: <?php if ($numFeed !== '1') {echo $numFeed;} ?>feed-o-yandex-<?php echo $cur_blog_id; ?>.xml <?php $assignment = xfoy_optionGET('xfoy_feed_assignment', $numFeed, 'set_arr'); if ($assignment === '') {} else {echo '('.$assignment.')';} ?> <?php if (empty($xfoy_file_url)) : ?><?php _e('ещё не создавался', 'xfoy'); ?><?php else : ?><?php if ($status_sborki !== -1) : ?><?php _e('обновляется', 'xfoy'); ?><?php else : ?><?php _e('создан', 'xfoy'); ?><?php endif; ?><?php endif; ?></h2>	
	 <div class="inside">
	 <p><strong style="color: green;"><?php _e('Инструкция', 'xfoy'); ?>:</strong> <a href="https://icopydoc.ru/kak-sozdat-fid-dlya-o-yandex-instruktsiya/?utm_source=xml-for-o-yandex&utm_medium=organic&utm_campaign=in-plugin-xml-for-o-yandex&utm_content=settings&utm_term=main-instruction" target="_blank"><?php _e('Как создать XML-фид', 'xfoy'); ?></a>.</p>
		<?php if (empty($xfoy_file_url)) : ?> 
			<?php if ($status_sborki !== -1) : ?>
				<p><?php _e('Идет автоматическое создание файла. XML-фид в скором времени будет создан', 'xfoy'); ?>.</p>
			<?php else : ?>
				<p><span class="xfoy_bold"><?php _e('Перейдите в "Товары" -> "Категории". Отредактируйте имющиеся у вас на сайте категории выбрав соответствующее значение напротив пункта: "Категория для Яндекс.Объявления"', 'xfoy'); ?>.</span></p>
				<p><?php _e('После вернитесь на данную страницу и в поле "Автоматическое создание файла" выставите значение, отличное от значения "отключено", при необходимости измените значение других полей и нажмите "Сохранить"', 'xfoy'); ?>.</p>
				<p><?php _e('Через 1 - 7 минут (зависит от числа товаров), фид будет сгенерирован и вместо данного сообщения появится ссылка', 'xfoy'); ?>.</p>
			<?php endif; ?>
		<?php else : ?>
			<?php if ($status_sborki !== -1) : ?>
				<p><?php _e('Идет автоматическое создание файла. XML-фид в скором времени будет создан', 'xfoy'); ?>.</p>
			<?php else : ?>
				<p><strong><?php _e('Ваш XML фид здесь', 'xfoy'); ?>:</strong><br/><a target="_blank" href="<?php echo $xfoy_file_url; ?>"><?php echo $xfoy_file_url; ?></a>
				<br/><?php _e('Размер файла', 'xfoy'); ?>: <?php clearstatcache();
				if ($numFeed === '1') {$prefFeed = '';} else {$prefFeed = $numFeed;}
				$upload_dir = (object)wp_get_upload_dir();
				if (is_multisite()) {
					$filename = $upload_dir->basedir."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-".get_current_blog_id().".xml";
				} else {
					$filename = $upload_dir->basedir."/xml-for-o-yandex/".$prefFeed."feed-o-yandex-0.xml";				
				}
				if (is_file($filename)) {echo xfoy_formatSize(filesize($filename));} else {echo '0 KB';} ?>
				<br/><?php _e('Сгенерирован', 'xfoy'); ?>: <?php echo $xfoy_date_sborki; ?></p>
			<?php endif; ?>		
		<?php endif; ?>
		<p><?php _e('Обратите внимание, что Яндекс проверяет XML не более 3 раз в день! Это означает, что изменения на Яндекс не являются мгновенными', 'xfoy'); ?>!</p>
		<p><span class="xfoy_bold"><?php _e('Если фид пустой', 'xfoy'); ?>:</span> <?php _e('Перейдите в "Товары" -> "Категории". Отредактируйте имющиеся у вас на сайте категории выбрав соответствующее значение напротив пункта: "Категория для Яндекс.Объявления"', 'xfoy'); ?>.</p>
	  </div>
	</div>	  
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">
	 <?php do_action('xfoy_prepend_form_container_2', $numFeed); ?>
	 <input type="hidden" name="xfoy_num_feed_for_save" value="<?php echo $numFeed; ?>">
	 <div class="postbox">
	  <h2 class="hndle"><?php _e('Основные параметры', 'xfoy'); ?></h2>
	   <div class="inside">	    
		<table class="form-table"><tbody>
		<tr>
			<th scope="row"><label for="xfoy_run_cron"><?php _e('Автоматическое создание файла', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_run_cron" id="xfoy_run_cron">
					<option value="off" <?php selected($xfoy_status_cron, 'off'); ?>><?php _e('Отключено', 'xfoy'); ?></option>
					<?php $xfoy_enable_five_min = xfoy_optionGET('xfoy_enable_five_min'); if ($xfoy_enable_five_min === 'on') : ?>
					<option value="five_min" <?php selected($xfoy_status_cron, 'five_min' );?> ><?php _e('Каждые пять минут', 'xfoy'); ?></option>
					<?php endif; ?>
					<option value="hourly" <?php selected($xfoy_status_cron, 'hourly' );?> ><?php _e('Раз в час', 'xfoy'); ?></option>
					<option value="six_hours" <?php selected($xfoy_status_cron, 'six_hours' ); ?> ><?php _e('Каждые 6 часов', 'xfoy'); ?></option>
					<option value="twicedaily" <?php selected($xfoy_status_cron, 'twicedaily' );?> ><?php _e('2 раза в день', 'xfoy'); ?></option>
					<option value="daily" <?php selected($xfoy_status_cron, 'daily' );?> ><?php _e('Раз в день', 'xfoy'); ?></option>
				</select><br />
				<span class="description"><?php _e('Интервал обновления вашего фида', 'xfoy'); ?></span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_ufup"><?php _e('Обновить фид при обновлении карточки товара', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_ufup" id="xfoy_ufup" <?php checked($xfoy_ufup, 'on' ); ?>/>
			</td>
		 </tr>
		 <?php do_action('xfoy_after_ufup_option', $numFeed); ?>
		 <tr>
			<th scope="row"><label for="xfoy_feed_assignment"><?php _e('Назначение фида', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="text" maxlength="20" name="xfoy_feed_assignment" id="xfoy_feed_assignment" value="<?php echo $xfoy_feed_assignment; ?>" placeholder="<?php _e('Для Яндекс.Объявления', 'xfoy');?>" /><br />
				<span class="description"><?php _e('Не используется в фиде. Внутренняя заметка для вашего удобства', 'xfoy'); ?>.</span>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_whot_export"><?php _e('Что экспортировать', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_whot_export" id="xfoy_whot_export">
					<option value="all" <?php selected($xfoy_whot_export, 'all'); ?>><?php _e('Вариативные и обычные товары'); ?></option>
					<option value="simple" <?php selected($xfoy_whot_export, 'simple'); ?>><?php _e('Только обычные товары', 'xfoy'); ?></option>
					<?php do_action('xfoy_after_whot_export_option', $xfoy_whot_export, $numFeed); ?>
				</select><br />
				<span class="description"><?php _e('Что экспортировать', 'xfoy'); ?></span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_desc"><?php _e('Описание товара', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_desc" id="xfoy_desc">
				<option value="excerpt" <?php selected($xfoy_desc, 'excerpt'); ?>><?php _e('Только Краткое описание', 'xfoy'); ?></option>
				<option value="full" <?php selected($xfoy_desc, 'full'); ?>><?php _e('Только Полное описание', 'xfoy'); ?></option>
				<option value="excerptfull" <?php selected($xfoy_desc, 'excerptfull'); ?>><?php _e('Краткое или Полное описание', 'xfoy'); ?></option>
				<option value="fullexcerpt" <?php selected($xfoy_desc, 'fullexcerpt'); ?>><?php _e('Полное или Краткое описание', 'xfoy'); ?></option>
				<option value="excerptplusfull" <?php selected($xfoy_desc, 'excerptplusfull'); ?>><?php _e('Краткое плюс Полное описание', 'xfoy'); ?></option>
				<option value="fullplusexcerpt" <?php selected($xfoy_desc, 'fullplusexcerpt'); ?>><?php _e('Полное плюс Краткое описание', 'xfoy'); ?></option>
				<?php do_action('xfoy_append_select_xfoy_desc', $xfoy_desc, $numFeed); ?>
				</select><br />
				<?php do_action('xfoy_after_select_xfoy_desc', $xfoy_desc, $numFeed); ?>
				<span class="description"><?php _e('Источник описания товара', 'xfoy'); ?>
				</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_the_content"><?php _e('Задействовать фильтр', 'xfoy'); ?> the_content</label></th>
			<td class="overalldesc">
				<select name="xfoy_the_content" id="xfoy_the_content">
				<option value="disabled" <?php selected($xfoy_the_content, 'disabled'); ?>><?php _e('Отключено', 'xfoy'); ?></option>
				<option value="enabled" <?php selected($xfoy_the_content, 'enabled'); ?>><?php _e('Включено', 'xfoy'); ?></option>
				</select><br />
				<span class="description"><?php _e('По умолчанию', 'xfoy'); ?>: <?php _e('Включено', 'xfoy'); ?></span>
			</td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="xfoy_var_desc_priority"><?php _e('Описание вариации имеет приоритет над другими', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_var_desc_priority" id="xfoy_var_desc_priority" <?php checked($xfoy_var_desc_priority, 'on'); ?>/>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_phone"><?php _e('Телефон', 'xfoy'); ?></label></th>
			<td class="overalldesc">
			 <input maxlength="20" type="text" name="xfoy_phone" id="xfoy_phone" placeholder="+79091231212" value="<?php echo $xfoy_phone; ?>" /><br />
			 <span class="description"><?php _e('Обязательный элемент', 'xfoy'); ?> <strong>phone</strong>.</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_address"><?php _e('Адрес', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input maxlength="256" type="text" name="xfoy_address" id="xfoy_address" placeholder="Россия, Москва, Тверская улица" value="<?php echo $xfoy_address; ?>" /><br />
				<span class="description"><?php _e('Обязательный элемент', 'xfoy'); ?> <strong>address</strong>. <?php _e('Полный адрес объекта — строка до 256 символов', 'xfoy'); ?>.</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_contact_method"><?php _e('Удобный способ связи', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_contact_method" id="xfoy_contact_method">
					<option value="any" <?php selected($xfoy_contact_method, 'any'); ?>><?php _e('Звонки и сообщения', 'xfoy'); ?></option>
					<option value="only-phone" <?php selected($xfoy_contact_method, 'only-phone'); ?>><?php _e('Только звонки', 'xfoy'); ?></option>
					<option value="only-chat" <?php selected($xfoy_contact_method, 'only-chat'); ?>><?php _e('Только сообщения', 'xfoy'); ?></option>
				</select><br />
			 <span class="description"><?php _e('Элемент', 'xfoy'); ?> <strong>contact-method</strong>. <?php _e('Удобный способ связи', 'xfoy'); ?>.</span>
			</td>
		 </tr>		 
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_main_product"><?php _e('Какие товары вы продаёте', 'xfoy'); ?>?</label></th>
			<td class="overalldesc">
					<select name="xfoy_main_product" id="xfoy_main_product">
					<option value="electronics" <?php selected($xfoy_main_product, 'electronics'); ?>><?php _e('Электроника', 'xfoy'); ?></option>
					<option value="computer" <?php selected($xfoy_main_product, 'computer'); ?>><?php _e('Компьютеры', 'xfoy'); ?></option>
					<option value="clothes_and_shoes" <?php selected($xfoy_main_product, 'clothes_and_shoes'); ?>><?php _e('Одежда и обувь', 'xfoy'); ?></option>
					<option value="auto_parts" <?php selected($xfoy_main_product, 'auto_parts'); ?>><?php _e('Автозапчасти', 'xfoy'); ?></option>
					<option value="products_for_children" <?php selected($xfoy_main_product, 'products_for_children'); ?>><?php _e('Детские товары', 'xfoy'); ?></option>
					<option value="sporting_goods" <?php selected($xfoy_main_product, 'sporting_goods'); ?>><?php _e('Спортивные товары', 'xfoy'); ?></option>
					<option value="goods_for_pets" <?php selected($xfoy_main_product, 'goods_for_pets'); ?>><?php _e('Товары для домашних животных', 'xfoy'); ?></option>
					<option value="sexshop" <?php selected($xfoy_main_product, 'sexshop'); ?>><?php _e('Секс-шоп (товары для взрослых)', 'xfoy'); ?></option>
					<option value="books" <?php selected($xfoy_main_product, 'books'); ?>><?php _e('Книги', 'xfoy'); ?></option>
					<option value="health" <?php selected($xfoy_main_product, 'health'); ?>><?php _e('Товары для здоровья', 'xfoy'); ?></option>	
					<option value="food" <?php selected($xfoy_main_product, 'food'); ?>><?php _e('Еда', 'xfoy'); ?></option>
					<option value="construction_materials" <?php selected($xfoy_main_product, 'construction_materials'); ?>><?php _e('Строительные материалы', 'xfoy'); ?></option>
					<option value="other" <?php selected($xfoy_main_product, 'other'); ?>><?php _e('Прочее', 'xfoy'); ?></option>					
				</select><br />
				<span class="description"><?php _e('Укажите основную категорию', 'xfoy'); ?></span>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_step_export"><?php _e('Шаг экспорта', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_step_export" id="xfoy_step_export">
				<option value="80" <?php selected($xfoy_step_export, '80'); ?>>80</option>
				<option value="200" <?php selected($xfoy_step_export, '200'); ?>>200</option>
				<option value="300" <?php selected($xfoy_step_export, '300'); ?>>300</option>
				<option value="450" <?php selected($xfoy_step_export, '450'); ?>>450</option>
				<option value="500" <?php selected($xfoy_step_export, '500'); ?>>500</option>
				<option value="800" <?php selected($xfoy_step_export, '800'); ?>>800</option>
				<option value="1000" <?php selected($xfoy_step_export, '1000'); ?>>1000</option>
				<?php do_action('xfoy_step_export_option', $numFeed); ?>
				</select><br />
				<span class="description"><?php _e('Значение влияет на скорость создания XML фида', 'xfoy'); ?>. <?php _e('Если у вас возникли проблемы с генерацией файла - попробуйте уменьшить значение в данном поле', 'xfoy'); ?>. <?php _e('Более 500 можно устанавливать только на мощных серверах', 'xfoy'); ?>.</span>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_condition"><?php _e('Состояние товара', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<select name="xfoy_condition" id="xfoy_condition">
				<option value="new" <?php selected($xfoy_condition, 'new'); ?>><?php _e('Новый', 'xfoy'); ?></option>
				<option value="used" <?php selected($xfoy_condition, 'used'); ?>><?php _e('Б/у', 'xfoy'); ?></option>
				<option value="inapplicable" <?php selected($xfoy_condition, 'inapplicable'); ?>><?php _e('Неприменимо', 'xfoy'); ?></option>
				<?php do_action('xfoy_condition_option', $numFeed); ?>
				</select><br />
				<span class="description"><?php _e('Обязательный элемент', 'xfoy'); ?> <strong>Condition</strong>. <?php _e('Задайте значение по умолчанию', 'xfoy'); ?></span>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_no_default_png_products"><?php _e('Удалить default.png из XML', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_no_default_png_products" id="xfoy_no_default_png_products" <?php checked($xfoy_no_default_png_products, 'on' ); ?>/>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_group_id">group_id</label></th>
			<td class="overalldesc">
				<select name="xfoy_group_id" id="xfoy_group_id">
				<option value="disabled" <?php selected($xfoy_group_id, 'disabled'); ?>><?php _e('Отключено', 'xfoy'); ?></option>
				<option value="enabled" <?php selected($xfoy_group_id, 'enabled'); ?>><?php _e('Включено', 'xfoy'); ?></option>
				</select><br />
				<span class="description"></span>
			</td>
		 </tr>
		 <tr class="xfoy_tr">
			<th scope="row"><label for="xfoy_skip_missing_products"><?php _e('Исключать товары которых нет в наличии', 'xfoy'); ?> (<?php _e('за исключением товаров, для которых разрешен предварительный заказ', 'xfoy'); ?>)</label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_skip_missing_products" id="xfoy_skip_missing_products" <?php checked($xfoy_skip_missing_products, 'on'); ?>/>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_skip_backorders_products"><?php _e('Исключать из фида товары для предзаказа', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_skip_backorders_products" id="xfoy_skip_backorders_products" <?php checked($xfoy_skip_backorders_products, 'on'); ?>/>
			</td>
		 </tr>
		 <?php do_action('xfoy_before_no_default_png', $numFeed); ?>
		</tbody></table>
	   </div>
	 </div>	
	 <?php do_action('xfoy_before_button_primary_submit', $numFeed); ?>	 
	 <div class="postbox">
	  <div class="inside">
		<table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="button-primary"></label></th>
			<td class="overalldesc"><?php wp_nonce_field('xfoy_nonce_action','xfoy_nonce_field'); ?><input id="button-primary" class="button-primary" type="submit" name="xfoy_submit_action" value="<?php _e('Сохранить', 'xfoy'); ?>" /><br />
			<span class="description"><?php _e('Нажмите, чтобы сохранить настройки', 'xfoy'); ?></span></td>
		 </tr>
		</tbody></table>
	  </div>
	 </div>	 
	 <?php do_action('xfoy_append_form_container_2', $numFeed); ?>
	</form>
	<?php do_action('xfoy_append_container_2', $numFeed); ?>
  </div></div>
 </div><!-- /post-body --><br class="clear"></div><!-- /poststuff -->
 <?php do_action('xfoy_after_poststuff', $numFeed); ?>

 <div id="icp_slides" class="clear">
  <div class="icp_wrap">
	<input type="radio" name="icp_slides" id="icp_point1">
	<input type="radio" name="icp_slides" id="icp_point2">
	<input type="radio" name="icp_slides" id="icp_point3" checked>
	<input type="radio" name="icp_slides" id="icp_point4">
	<input type="radio" name="icp_slides" id="icp_point5">
	<input type="radio" name="icp_slides" id="icp_point6">
	<input type="radio" name="icp_slides" id="icp_point7">
	<div class="icp_slider">
		<div class="icp_slides icp_img1"><a href="//wordpress.org/plugins/yml-for-yandex-market/" target="_blank"></a></div>
		<div class="icp_slides icp_img2"><a href="//wordpress.org/plugins/import-products-to-ok-ru/" target="_blank"></a></div>
		<div class="icp_slides icp_img3"><a href="//wordpress.org/plugins/xml-for-google-merchant-center/" target="_blank"></a></div>
		<div class="icp_slides icp_img4"><a href="//wordpress.org/plugins/gift-upon-purchase-for-woocommerce/" target="_blank"></a></div>
		<div class="icp_slides icp_img5"><a href="//wordpress.org/plugins/xml-for-avito/" target="_blank"></a></div>
		<div class="icp_slides icp_img6"><a href="//wordpress.org/plugins/xml-for-o-yandex/" target="_blank"></a></div>
		<div class="icp_slides icp_img7"><a href="//wordpress.org/plugins/import-from-yml/" target="_blank"></a></div>
	</div>
	<div class="icp_control">
		<label for="icp_point1"></label>
		<label for="icp_point2"></label>
		<label for="icp_point3"></label>
		<label for="icp_point4"></label>
		<label for="icp_point5"></label>
		<label for="icp_point6"></label>
		<label for="icp_point7"></label>	
	</div>
  </div> 
 </div>
 <?php do_action('xfoy_after_icp_slides', $numFeed); ?>

 <div class="metabox-holder">
  <div class="postbox">
  	<h2 class="hndle"><?php _e('Мои плагины, которые могут вас заинтересовать', 'xfoy'); ?></h2>
	<div class="inside">
		<p><span class="xfoy_bold">XML for Google Merchant Center</span> - <?php _e('Создает XML-фид для загрузки в Google Merchant Center', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/xml-for-google-merchant-center/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p> 
		<p><span class="xfoy_bold">YML for Yandex Market</span> - <?php _e('Создает YML-фид для импорта ваших товаров на Яндекс Маркет', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/yml-for-yandex-market/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">Import from YML</span> - <?php _e('Импортирует товары из YML в ваш магазин', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/import-from-yml/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">XML for Hotline</span> - <?php _e('Создает XML-фид для импорта ваших товаров на Hotline', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/xml-for-hotline/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">Gift upon purchase for WooCommerce</span> - <?php _e('Этот плагин добавит маркетинговый инструмент, который позволит вам дарить подарки покупателю при покупке', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/gift-upon-purchase-for-woocommerce/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">Import products to ok.ru</span> - <?php _e('С помощью этого плагина вы можете импортировать товары в свою группу на ok.ru', 'xfoy'); ?>. <a href="https://wordpress.org/plugins/import-products-to-ok-ru/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">XML for Avito</span> - <?php _e('Создает XML-фид для импорта ваших товаров на', 'xfoy'); ?> Avito. <a href="https://wordpress.org/plugins/xml-for-avito/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
		<p><span class="xfoy_bold">XML for O.Yandex (Яндекс Объявления)</span> - <?php _e('Создает XML-фид для импорта ваших товаров на', 'xfoy'); ?> Яндекс.Объявления. <a href="https://wordpress.org/plugins/xml-for-o-yandex/" target="_blank"><?php _e('Подробнее', 'xfoy'); ?></a>.</p>
	</div>
  </div>
 </div>
 <?php do_action('xfoy_append_wrap', $numFeed); ?>
</div><!-- /wrap -->
<?php
} /* end функция настроек xfoy_export_page */ ?>