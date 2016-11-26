<?php


$dictionary=array(

'SCHEDULER_MODULE_TITLE'=>'Scheduler',
/* end module names */


);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
