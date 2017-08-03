<?php
/**
* Scheduler 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 11:11:28 [Nov 26, 2016])
*/
//
//
class scheduler extends module {
/**
* scheduler
*
* Module class constructor
*
* @access private
*/
function scheduler() {
  $this->name="scheduler";
  $this->title="Scheduler";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='scheduler_tasks' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_scheduler_tasks') {
   $this->search_scheduler_tasks($out);
  }
  if ($this->view_mode=='edit_scheduler_tasks') {
   $this->edit_scheduler_tasks($out, $this->id);
  }
  if ($this->view_mode=='delete_scheduler_tasks') {
   $this->delete_scheduler_tasks($this->id);
   $this->redirect("?data_source=scheduler_tasks");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='scheduler_points') {
  if ($this->view_mode=='' || $this->view_mode=='search_scheduler_points') {
   $this->search_scheduler_points($out);
  }
  if ($this->view_mode=='edit_scheduler_points') {
   $this->edit_scheduler_points($out, $this->id);
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
    if ($this->ajax) {
        global $id;
        if ($id) {
            $this->runSchedulerPoint((int)$id);
        } else {
            $this->processCycle();
        }
    }
 $this->admin($out);
}
/**
* scheduler_tasks search
*
* @access public
*/
 function search_scheduler_tasks(&$out) {
  require(DIR_MODULES.$this->name.'/scheduler_tasks_search.inc.php');
 }
/**
* scheduler_tasks edit/add
*
* @access public
*/
 function edit_scheduler_tasks(&$out, $id) {
  require(DIR_MODULES.$this->name.'/scheduler_tasks_edit.inc.php');
 }
/**
* scheduler_tasks delete record
*
* @access public
*/
 function delete_scheduler_tasks($id) {
  $rec=SQLSelectOne("SELECT * FROM scheduler_tasks WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM scheduler_points WHERE TASK_ID=".$rec['ID']);
  SQLExec("DELETE FROM scheduler_tasks WHERE ID='".$rec['ID']."'");
 }
/**
* scheduler_points search
*
* @access public
*/
 function search_scheduler_points(&$out) {
  require(DIR_MODULES.$this->name.'/scheduler_points_search.inc.php');
 }
/**
* scheduler_points edit/add
*
* @access public
*/
 function edit_scheduler_points(&$out, $id) {
  require(DIR_MODULES.$this->name.'/scheduler_points_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
   $table='scheduler_tasks';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }

function runSchedulerPoint($id) {
    $point=SQLSelectOne("SELECT scheduler_points.VALUE, scheduler_tasks.* FROM scheduler_points LEFT JOIN scheduler_tasks ON scheduler_points.TASK_ID=scheduler_tasks.ID WHERE scheduler_points.ID=".(int)$id);
    if (!$point['ID']) return;

    echo "Running point ".$point['ID'];

    $value=$point['VALUE'];
    if ($point['VALUE_TYPE']==0 || $point['VALUE_TYPE']==1) {
        //set value
        if ($point['LINKED_OBJECT'] && $point['LINKED_PROPERTY']) {
            echo $point['LINKED_OBJECT'].'.'.$point['LINKED_PROPERTY']." setting to ".$value;
          setGlobal($point['LINKED_OBJECT'].'.'.$point['LINKED_PROPERTY'],$value);
        }
    } elseif ($point['VALUE_TYPE']==2) {
        //run action
    }

    $params=array('VALUE'=>$value);
    if ($point['LINKED_OBJECT'] && $point['LINKED_METHOD']) {
      callMethod($point['LINKED_OBJECT'] .'.'. $point['LINKED_METHOD'],$params);
    }

    $code=$point['CODE'];
    if ($code!='') {

        try {
            $success = eval($code);
            if ($success === false) {
                registerError('scheduler', sprintf('Error in scheduler point "%s". Code: %s', $point['ID'], $code));
            }
            return $success;
        } catch (Exception $e) {
            registerError('scheduler', sprintf('Error in scheduler point "%s": '.$e->getMessage(),  $point['ID']));
        }

    }

}

 function processCycle() {
  //to-do
     $tm=date('H:i');
     $weekday=date('w');
     $tasks=SQLSelect("SELECT ID, SET_DAYS FROM scheduler_points WHERE SET_TIME='".$tm."' AND ENABLED=1");
     $total = count($tasks);
     $started=0;
     for ($i = 0; $i < $total; $i++) {
         $days=explode(',',$tasks[$i]['SET_DAYS']);
         if (in_array($weekday,$days)) {
             $started++;
             $this->runSchedulerPoint($tasks[$i]['ID']);
         } else {
             //echo "Found but another day: ".$tasks[$i]['SET_DAYS'];
         }
     }
     if (!$started) {
         //echo "Ponits not found for $tm ($weekday).";
     }
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  if (file_exists(ROOT.'scripts/cycle_schedulerapp.php')) {
   unlink(ROOT.'scripts/cycle_schedulerapp.php');
  }
  setGlobal('cycle_schedappControl', 'restart');
  setGlobal('cycle_schedappAutoRestart', '1');
  parent::install();

  @include_once(ROOT.'languages/'.$this->name.'_'.SETTINGS_SITE_LANGUAGE.'.php');
  @include_once(ROOT.'languages/'.$this->name.'_default'.'.php');

  SQLExec("UPDATE project_modules SET TITLE='".LANG_SCHEDULER_MODULE_TITLE."' WHERE NAME='".$this->name."'");
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS scheduler_tasks');
  SQLExec('DROP TABLE IF EXISTS scheduler_points');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data = '') {
/*
scheduler_tasks - 
scheduler_points - 
*/
  $data = <<<EOD
 scheduler_tasks: ID int(10) unsigned NOT NULL auto_increment
 scheduler_tasks: TITLE varchar(100) NOT NULL DEFAULT ''
 scheduler_tasks: VALUE_TYPE int(3) NOT NULL DEFAULT '0'
 scheduler_tasks: VALUE_TITLE varchar(255) NOT NULL DEFAULT ''
 scheduler_tasks: CODE text
 scheduler_tasks: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 scheduler_tasks: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 scheduler_tasks: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 
 scheduler_points: ID int(10) unsigned NOT NULL auto_increment
 scheduler_points: TITLE varchar(100) NOT NULL DEFAULT ''
 scheduler_points: ENABLED int(3) NOT NULL DEFAULT '1'
 scheduler_points: VALUE varchar(255) NOT NULL DEFAULT ''
 scheduler_points: SET_TIME varchar(50) NOT NULL DEFAULT ''
 scheduler_points: SET_DAYS varchar(50) NOT NULL DEFAULT '' 
 scheduler_points: TASK_ID int(10) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDI2LCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
