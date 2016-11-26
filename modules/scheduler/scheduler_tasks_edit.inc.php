<?php
/*
* @version 0.1 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'scheduler_tasks';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($this->mode == 'update') {
    $ok = 1;
    // step: default
    if ($this->tab == '') {
        //updating '<%LANG_TITLE%>' (varchar, required)
        global $title;
        $rec['TITLE'] = $title;
        if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
        }
        //updating 'VALUE_TYPE' (varchar)
        global $value_type;
        $rec['VALUE_TYPE'] = $value_type;
        //updating 'VALUE_TITLE' (varchar)
        global $value_title;
        $rec['VALUE_TITLE'] = $value_title;
        //updating 'CODE' (varchar)
        global $code;
        $rec['CODE'] = $code;
        //updating '<%LANG_LINKED_OBJECT%>' (varchar)
        global $linked_object;
        $rec['LINKED_OBJECT'] = $linked_object;
        //updating '<%LANG_LINKED_PROPERTY%>' (varchar)
        global $linked_property;
        $rec['LINKED_PROPERTY'] = $linked_property;
        //updating '<%LANG_METHOD%>' (varchar)
        global $linked_method;
        $rec['LINKED_METHOD'] = $linked_method;
    }
    // step: data
    if ($this->tab == 'data') {
    }
    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}
// step: default
if ($this->tab == '') {
}
// step: data
if ($this->tab == 'data') {
}
if ($this->tab == 'data') {
    //dataset2
    $new_id = 0;
    if ($this->mode == 'update') {
        global $new_days;
        global $new_value;
        global $new_hour;
        global $new_minute;
        $new_time=$new_hour.':'.$new_minute;
        if (IsSet($new_days[0]) && preg_match('/^\d+:\d+$/', $new_time)) {
            $prop = array('SET_TIME' => $new_time, 'SET_DAYS' => implode(',', $new_days), 'VALUE' => $new_value, 'ENABLED' => 1, 'TASK_ID' => $rec['ID']);
            $new_id = SQLInsert('scheduler_points', $prop);
            $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
        }
    }
    global $delete_id;
    if ($delete_id) {
        SQLExec("DELETE FROM scheduler_points WHERE ID='" . (int)$delete_id . "'");
    }
    $properties = SQLSelect("SELECT * FROM scheduler_points WHERE TASK_ID='" . $rec['ID'] . "' ORDER BY ID");
    $days=array(
        array('VALUE'=>0,'TITLE'=>LANG_WEEK_SUN),
        array('VALUE'=>1,'TITLE'=>LANG_WEEK_MON),
        array('VALUE'=>2,'TITLE'=>LANG_WEEK_TUE),
        array('VALUE'=>3,'TITLE'=>LANG_WEEK_WED),
        array('VALUE'=>4,'TITLE'=>LANG_WEEK_THU),
        array('VALUE'=>5,'TITLE'=>LANG_WEEK_FRI),
        array('VALUE'=>6,'TITLE'=>LANG_WEEK_SAT),
    );

    $hours=array();
    for($i=0;$i<24;$i++) {
        $hours[]=array('VALUE'=>str_pad($i,2,'0',STR_PAD_LEFT));
    }
    $minutes=array();
    for($i=0;$i<60;$i++) {
        $minutes[]=array('VALUE'=>str_pad($i,2,'0',STR_PAD_LEFT));
    }

    $total = count($properties);
    for ($i = 0; $i < $total; $i++) {
        if ($properties[$i]['ID'] == $new_id) continue;
        if ($this->mode == 'update') {
            global ${'value' . $properties[$i]['ID']};
            $properties[$i]['VALUE'] = trim(${'value' . $properties[$i]['ID']});
            global  ${'enabled' . $properties[$i]['ID']};
            $properties[$i]['ENABLED'] = (int)(${'enabled' . $properties[$i]['ID']});
            global  ${'days' . $properties[$i]['ID']};
            @$properties[$i]['SET_DAYS']=implode(',',${'days' . $properties[$i]['ID']});
            global  ${'hour' . $properties[$i]['ID']};
            global  ${'minute' . $properties[$i]['ID']};
            $properties[$i]['SET_TIME']=${'hour' . $properties[$i]['ID']}.':'.${'minute' . $properties[$i]['ID']};
            SQLUpdate('scheduler_points', $properties[$i]);
        }
        $properties[$i]['HOURS']=$hours;
        $properties[$i]['MINUTES']=$minutes;
        $tmp=explode(':',$properties[$i]['SET_TIME']);
        $properties[$i]['SET_HOUR']=$tmp[0];
        $properties[$i]['SET_MINUTE']=$tmp[1];

        $properties[$i]['DAYS']=$days;
        $tmp=explode(',',$properties[$i]['SET_DAYS']);
        $totald = count($days);
        for ($ida = 0; $ida < $totald; $ida++) {
            if (in_array($ida,$tmp)) {
                $properties[$i]['DAYS'][$ida]['SELECTED']=1;
            }
        }
    }
    $out['HOURS']=$hours;
    $out['MINUTES']=$minutes;
    $out['PROPERTIES'] = $properties;
}
if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);
