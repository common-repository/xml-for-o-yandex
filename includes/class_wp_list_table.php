<?php // https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html https://wp-kama.ru/function/wp_list_table
class XmlforOYandex_WP_List_Table extends WP_List_Table {
 /*	Метод get_columns() необходим для маркировки столбцов внизу и вверху таблицы. 
 *	Ключи в массиве должны быть теми же, что и в массиве данных, 
 *	иначе соответствующие столбцы не будут отображены.
 */
 function get_columns() {
	$columns = array(
		'cb'					=> '<input type="checkbox" />', // флажок сортировки. см get_bulk_actions и column_cb
		'ID'					=> __('ID фида', 'xfoy'),
		'xfoy_file_url'			=> __('YML файл', 'xfoy'),
		'xfoy_run_cron'			=> __('Автоматическое создание файла', 'xfoy'),
		'xfoy_step_export'		=> __('Шаг экспорта', 'xfoy'),
	);
	return $columns;
 }
 /*	
 *	Метод вытаскивает из БД данные, которые будут лежать в таблице
 *	$this->table_data();
 */
 private function table_data() {
	$xfoy_settings_arr = xfoy_optionGET('xfoy_settings_arr');
	$result_arr = array();
	$xfoy_settings_arr_keys_arr = array_keys($xfoy_settings_arr);
	for ($i = 0; $i < count($xfoy_settings_arr_keys_arr); $i++) {
		$key = (int)$xfoy_settings_arr_keys_arr[$i];
		$xfoy_status_cron = $xfoy_settings_arr[$key]['xfoy_status_cron'];
		switch($xfoy_status_cron) {
			case 'off': $text = __("Отключено", "xfoy"); break;
			case 'five_min':  $text = __('Каждые 5 минут', 'xfoy'); break;
			case 'hourly':  $text = __('Раз в час', 'xfoy'); break;
			case 'six_hours':  $text = __('Каждые 6 часов', 'xfoy'); break;
			case 'twicedaily':  $text = __('2 раза в день', 'xfoy'); break;
			case 'daily':  $text = __('Раз в день ', 'xfoy'); break;
			default: $text = __("Отключено", "xfoy"); 
		}
		$numFeed = (string)$key;
		$xfoy_file_url = urldecode(xfoy_optionGET('xfoy_file_url', $numFeed, 'set_arr'));
		if ($xfoy_file_url == '') {
			$url_yml_file = __('Фид ещё не создавался', 'xfoy');
		} else {
			$url_yml_file = sprintf('<a href="%s" alt="%s">%s</a>', $xfoy_file_url, $xfoy_file_url, $xfoy_file_url);
		}
		$result_arr[$i] = array(
			'ID' => $key,
			'xfoy_file_url' => $url_yml_file,
			'xfoy_run_cron' => $text,
			'xfoy_step_export' => $xfoy_settings_arr[$key]['xfoy_step_export']
		);
	}
	return $result_arr;
 }
 /*
 *	prepare_items определяет два массива, управляющие работой таблицы:
 *	$hidden определяет скрытые столбцы https://2web-master.ru/wp_list_table-%E2%80%93-poshagovoe-rukovodstvo.html#screen-options
 *	$sortable определяет, может ли таблица быть отсортирована по этому столбцу.
 *
 */
 function prepare_items() {
	$columns = $this->get_columns();
	$hidden = array();
	$sortable = $this->get_sortable_columns(); // вызов сортировки
	$this->_column_headers = array($columns, $hidden, $sortable);
	// пагинация 
	$per_page = 5;
	$current_page = $this->get_pagenum();
	$total_items = count($this->table_data());
	$found_data = array_slice($this->table_data(), (($current_page - 1) * $per_page), $per_page);
	$this->set_pagination_args(array(
		'total_items' => $total_items, // Мы должны вычислить общее количество элементов
		'per_page'	  => $per_page // Мы должны определить, сколько элементов отображается на странице
	));
	// end пагинация 
	$this->items = $found_data; // $this->items = $this->table_data() // Получаем данные для формирования таблицы
 }
 /*
 * 	Данные таблицы.
 *	Наконец, метод назначает данные из примера на переменную представления данных класса — items.
 *	Прежде чем отобразить каждый столбец, WordPress ищет методы типа column_{key_name}, например, function column_xfoy_file_url. 
 *	Такой метод должен быть указан для каждого столбца. Но чтобы не создавать эти методы для всех столбцов в отдельности, 
 *	можно использовать column_default. Эта функция обработает все столбцы, для которых не определён специальный метод:
 */ 
 function column_default($item, $column_name) {
	switch( $column_name ) {
		case 'ID':
		case 'xfoy_file_url':
		case 'xfoy_run_cron':
		case 'xfoy_step_export':
			return $item[ $column_name ];
		default:
			return print_r( $item, true ) ; //Мы отображаем целый массив во избежание проблем
	}
 }
 /*
 * 	Функция сортировки.
 *	Второй параметр в массиве значений $sortable_columns отвечает за порядок сортировки столбца. 
 *	Если значение true, столбец будет сортироваться в порядке возрастания, если значение false, столбец сортируется в порядке 
 *	убывания, или не упорядочивается. Это необходимо для маленького треугольника около названия столбца, который указывает порядок
 *	сортировки, чтобы строки отображались в правильном направлении
 */
 function get_sortable_columns() {
	$sortable_columns = array(
		'xfoy_file_url'	=> array('xfoy_file_url', false),
		// 'xfoy_run_cron'		=> array('xfoy_run_cron', false)
	);
	return $sortable_columns;
  }
 /*
 * 	Действия.
 *	Эти действия появятся, если пользователь проведет курсор мыши над таблицей
 *	column_{key_name} - в данном случае для колонки xfoy_file_url - function column_xfoy_file_url
 */
 function column_xfoy_file_url($item) {
	if ($item['ID'] === 1) {
		$actions = array(
			'edit'		=> sprintf('<a href="?page=%s&action=%s&numFeed=%s">Редактировать</a>', $_REQUEST['page'], 'edit', $item['ID'])
		);
	} else {
		$actions = array(
			'edit'		=> sprintf('<a href="?page=%s&action=%s&numFeed=%s">Редактировать</a>', $_REQUEST['page'], 'edit', $item['ID']),
			'delete'	=> sprintf('<a href="?page=%s&action=%s&numFeed=%s">Удалить</a>', $_REQUEST['page'], 'delete', $item['ID']),
		);
	}
	return sprintf('%1$s %2$s', $item['xfoy_file_url'], $this->row_actions($actions) );
 }
 /*
 * 	Массовые действия.
 *	Bulk action осуществляются посредством переписывания метода get_bulk_actions() и возврата связанного массива
 *	Этот код просто помещает выпадающее меню и кнопку «применить» вверху и внизу таблицы
 *	ВАЖНО! Чтобы работало нужно оборачивать вызов класса в form:
 *	<form id="events-filter" method="get"> 
 *	<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" /> 
 *	<?php $wp_list_table->display(); ?> 
 *	</form> 
 */
 function get_bulk_actions() {
	$actions = array(
		'delete'	=> __('Удалить', 'xfoy')
	);
	return $actions;
 }
 // Флажки для строк должны быть определены отдельно. Как упоминалось выше, есть метод column_{column} для отображения столбца. cb-столбец – особый случай:
 function column_cb($item) {
	if ($item['ID'] === 1) {
		return sprintf(
			'<input type="checkbox" name="checkbox_yml_file[]" value="%s" disabled />', $item['ID']
		);
	 } else {
		return sprintf(
			'<input type="checkbox" name="checkbox_yml_file[]" value="%s" />', $item['ID']
		);
	}
 }
 /*
 * Нет элементов.
 * Если в списке нет никаких элементов, отображается стандартное сообщение «No items found.». Если вы хотите изменить это сообщение, вы можете переписать метод no_items():
 */
 function no_items() {
	_e('Нет YML фидов', 'xfoy');
 }
}
?>