<?php if (!defined('ABSPATH')) {exit;}
/* функция песочницы xfoy_run_sandbox */
function xfoy_run_sandbox() { 
 $x = 1; // установите 0, чтобы вернуть исключение
 /* вставьте ваш код ниже */

 /* дальше не редактируем */
 if (!$x) {
	throw new Exception('The sandbox is working correctly');
 }
 echo 1/$x;
} /* end функция песочницы xfoy_run_sandbox */
?>