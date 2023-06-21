<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  if (!$qry) $qry="1";
  $sortby_scheduler_tasks="ID DESC";
  $out['SORTBY']=$sortby_scheduler_tasks;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM scheduler_tasks WHERE $qry ORDER BY ".$sortby_scheduler_tasks);
  if (isset($res[0])) {
   $out['RESULT']=$res;
  }
