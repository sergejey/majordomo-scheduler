<?php


$dictionary=array(

'SCHEDULER_MODULE_TITLE'=>'Планировщик',
/* end module names */


);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
