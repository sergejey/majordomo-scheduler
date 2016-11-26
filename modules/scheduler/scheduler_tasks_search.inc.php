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
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['scheduler_tasks_qry'];
  } else {
   $session->data['scheduler_tasks_qry']=$qry;
  }
  if (!$qry) $qry="1";
  $sortby_scheduler_tasks="ID DESC";
  $out['SORTBY']=$sortby_scheduler_tasks;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM scheduler_tasks WHERE $qry ORDER BY ".$sortby_scheduler_tasks);
  if ($res[0]['ID']) {
   //paging($res, 100, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
