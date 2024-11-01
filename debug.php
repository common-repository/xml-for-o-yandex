<?php if (!defined('ABSPATH')) {exit;}
function xfoy_debug_page() { 
 wp_clean_plugins_cache();
 wp_clean_update_cache();
 add_filter('pre_site_transient_update_plugins', '__return_null');
 wp_update_plugins();
 remove_filter('pre_site_transient_update_plugins', '__return_null');
 if (isset($_REQUEST['xfoy_submit_debug_page'])) {
	if (!empty($_POST) && check_admin_referer('xfoy_nonce_action','xfoy_nonce_field')) {
		if (isset($_POST['xfoy_keeplogs'])) {
			xfoy_optionUPD('xfoy_keeplogs', sanitize_text_field($_POST['xfoy_keeplogs']));
			xfoy_error_log('NOTICE: Логи успешно включены; Файл: debug.php; Строка: '.__LINE__, 0);
		} else {
			xfoy_error_log('NOTICE: Логи отключены; Файл: debug.php; Строка: '.__LINE__, 0);
			xfoy_optionUPD('xfoy_keeplogs', '0');
		}
		if (isset($_POST['xfoy_disable_notices'])) {
			xfoy_optionUPD('xfoy_disable_notices', sanitize_text_field($_POST['xfoy_disable_notices']));
		} else {
			xfoy_optionUPD('xfoy_disable_notices', '0');
		}
		if (isset($_POST['xfoy_enable_five_min'])) {
			xfoy_optionUPD('xfoy_enable_five_min', sanitize_text_field($_POST['xfoy_enable_five_min']));
		} else {
			xfoy_optionUPD('xfoy_enable_five_min', '0');
		}		
	}
 }	
 $xfoy_keeplogs = xfoy_optionGET('xfoy_keeplogs');
 $xfoy_disable_notices = xfoy_optionGET('xfoy_disable_notices');
 $xfoy_enable_five_min = xfoy_optionGET('xfoy_enable_five_min');
 ?>
 <div class="wrap"><h1><?php _e('Страница отладки', 'xfoy'); ?> v.<?php echo xfoy_optionGET('xfoy_version'); ?></h1>
  <div id="dashboard-widgets-wrap"><div id="dashboard-widgets" class="metabox-holder">
  	<div id="postbox-container-1" class="postbox-container"><div class="meta-box-sortables">
     <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" enctype="multipart/form-data">	 
	 <div class="postbox">
	   <div class="inside">
		<h1><?php _e('Логи', 'xfoy'); ?></h1>
		<p><?php if ($xfoy_keeplogs === 'on') {echo '<strong>'. __("Логи тут", 'xfoy').':</strong><br />'. xfoy_UPLOAD_DIR .'/xml-for-o-yandex/xml-for-o-yandex.log';	} ?></p>		
		<table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="xfoy_keeplogs"><?php _e('Вести логи', 'xfoy'); ?></label><br />
				<input class="button" id="xfoy_submit_clear_logs" type="submit" name="xfoy_submit_clear_logs" value="<?php _e('Очистить логи', 'xfoy'); ?>" />
			</th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_keeplogs" id="xfoy_keeplogs" <?php checked($xfoy_keeplogs, 'on' ); ?>/><br />
				<span class="description"><?php _e('Не устанавливайте этот флажок, если вы не разработчик', 'xfoy'); ?>!</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_disable_notices"><?php _e('Отключить уведомления', 'xfoy'); ?></label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_disable_notices" id="xfoy_disable_notices" <?php checked($xfoy_disable_notices, 'on' ); ?>/><br />
				<span class="description"><?php _e('Отключить уведомления о XML-сборке', 'xfoy'); ?>!</span>
			</td>
		 </tr>
		 <tr>
			<th scope="row"><label for="xfoy_enable_five_min"><?php _e('Включить', 'xfoy'); ?> five_min</label></th>
			<td class="overalldesc">
				<input type="checkbox" name="xfoy_enable_five_min" id="xfoy_enable_five_min" <?php checked($xfoy_enable_five_min, 'on' ); ?>/><br />
				<span class="description"><?php _e('Включить пятиминутный интервал для CRON', 'xfoy'); ?></span>
			</td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="button-primary"></label></th>
			<td class="overalldesc"></td>
		 </tr>		 
		 <tr>
			<th scope="row"><label for="button-primary"></label></th>
			<td class="overalldesc"><?php wp_nonce_field('xfoy_nonce_action', 'xfoy_nonce_field'); ?><input id="button-primary" class="button-primary" type="submit" name="xfoy_submit_debug_page" value="<?php _e('Сохранить', 'xfoy'); ?>" /><br />
			<span class="description"><?php _e('Нажмите, чтобы сохранить настройки', 'xfoy'); ?></span></td>
		 </tr>         
        </tbody></table>
       </div>
     </div>
     </form>
	</div></div>
  	<div id="postbox-container-2" class="postbox-container"><div class="meta-box-sortables">
  	 <div class="postbox">
	  <div class="inside">
		<h1><?php _e('Сбросить настройки плагина', 'xfoy'); ?></h1>
		<p><?php _e('Сброс настроек плагина может быть полезен в случае возникновения проблем', 'xfoy'); ?>.</p>
		<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" enctype="multipart/form-data">
			<?php wp_nonce_field('xfoy_nonce_action_reset', 'xfoy_nonce_field_reset'); ?><input class="button-primary" type="submit" name="xfoy_submit_reset" value="<?php _e('Сбросить настройки плагина', 'xfoy'); ?>" />	 
		</form>
	  </div>
	 </div>	
	 <div class="postbox">
	  <h2 class="hndle"><?php _e('Симуляция запроса', 'xfoy'); ?></h2>
	  <div class="inside">		
		<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" enctype="multipart/form-data">
		 <?php 	 
		 if (isset($_POST['xfoy_num_feed'])) {$numFeed = sanitize_text_field($_POST['xfoy_num_feed']);} else {$numFeed = '1';} 
		 if (isset($_POST['xfoy_simulated_post_id'])) {$xfoy_simulated_post_id = sanitize_text_field($_POST['xfoy_simulated_post_id']);} else {$xfoy_simulated_post_id = '';}
		 $resust_simulated = '';
		 if (isset($_REQUEST['xfoy_submit_simulated'])) {
			if (!empty($_POST) && check_admin_referer('xfoy_nonce_action_simulated', 'xfoy_nonce_field_simulated')) {		 
				$postId = (int)$xfoy_simulated_post_id;
				$simulated_header = xfoy_feed_header($numFeed);
				$simulated = xfoy_unit($postId, $numFeed);
				if (is_array($simulated)) {
					$resust_simulated = $simulated_header.$simulated[0];
					$resust_simulated = apply_filters('xfoy_after_offers_filter', $resust_simulated, $numFeed);
					$resust_simulated .= "</offers>".PHP_EOL."</feed>";				
				} else {
					$resust_simulated = $simulated_header.$simulated;
					$resust_simulated = apply_filters('xfoy_after_offers_filter', $resust_simulated, $numFeed);
					$resust_simulated .= "</offers>".PHP_EOL."</feed>";
				}
			}
		 } ?>		
		 <table class="form-table"><tbody>
		 <tr>
			<th scope="row"><label for="xfoy_simulated_post_id">postId</label></th>
			<td class="overalldesc">
				<input type="number" min="1" name="xfoy_simulated_post_id" value="<?php echo $xfoy_simulated_post_id; ?>">
			</td>
		 </tr>			
		 <tr>
			<th scope="row"><label for="xfoy_enable_five_min">numFeed</label></th>
			<td class="overalldesc">
				<select style="width: 100%" name="xfoy_num_feed" id="xfoy_num_feed">
					<?php if (is_multisite()) {$cur_blog_id = get_current_blog_id();} else {$cur_blog_id = '0';}		
					$allNumFeed = (int)xfoy_ALLNUMFEED; $ii = '1';
					for ($i = 1; $i<$allNumFeed+1; $i++) : ?>
					<option value="<?php echo $i; ?>" <?php selected($numFeed, $i); ?>><?php _e('Фид', 'xfoy'); ?> <?php echo $i; ?>: feed-o-yandex-<?php echo $cur_blog_id; ?>.xml <?php $assignment = xfoy_optionGET('xfoy_feed_assignment', $ii); if ($assignment === '') {} else {echo '('.$assignment.')';} ?></option>
					<?php $ii++; endfor; ?>
				</select>
			</td>
		 </tr>			
		 <tr>
			<th scope="row" colspan="2"><textarea rows="16" style="width: 100%;"><?php echo htmlspecialchars($resust_simulated); ?></textarea></th>
		 </tr>			       
		 </tbody></table>
		 <?php wp_nonce_field('xfoy_nonce_action_simulated', 'xfoy_nonce_field_simulated'); ?><input class="button-primary" type="submit" name="xfoy_submit_simulated" value="<?php _e('Симуляция', 'xfoy'); ?>" />
		</form>			
	  </div>
	 </div>	 
	</div></div>
	 <div id="postbox-container-3" class="postbox-container"><div class="meta-box-sortables">
	 <div class="postbox">
	  <div class="inside">
	  	<h1><?php _e('Возможные проблемы', 'xfoy'); ?></h1>
		  <?php
			$possible_problems_arr = xfoy_possible_problems_list();
			if ($possible_problems_arr[1] > 0) { // $possibleProblemsCount > 0) {
				echo '<ol>'.$possible_problems_arr[0].'</ol>';
			} else {
				echo '<p>'. __('Функции самодиагностики не выявили потенциальных проблем', 'xfoy').'.</p>';
			}
		  ?>
	  </div>
     </div>	 
	 <div class="postbox">
	  <div class="inside">
	  	<h1><?php _e('Песочница', 'xfoy'); ?></h1>
			<?php
				require_once plugin_dir_path(__FILE__).'/sandbox.php';
				try {
					xfoy_run_sandbox();
				} catch (Exception $e) {
					echo 'Exception: ',  $e->getMessage(), "\n";
				}
			?>
		</div>
     </div>
  	</div></div>	  	 
	<div id="postbox-container-4" class="postbox-container"><div class="meta-box-sortables">
  	 <?php do_action('xfoy_before_support_project'); ?>
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
  </div></div>
  </div></div>
 </div>
<?php
} /* end функция страницы debug-а xfoy_debug_page */
?>