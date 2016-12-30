<?php /* $Id$ $URL$ */

if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$holiday_manual = (int)w2PgetParam($_POST, "holiday_manual", 0);
$holiday_auto = (int)w2PgetParam($_POST, "holiday_auto", 0);
$holiday_driver = (int)w2PgetParam($_POST, "holiday_driver", -1);
$holiday_filter = (int)w2PgetParam($_POST, "holiday_filter", -1);
$q = new w2p_Database_Query();
$q->addTable('holiday_settings');
$q->addUpdate('holiday_manual', $holiday_manual);
$q->addUpdate('holiday_auto', $holiday_auto);
$q->addUpdate('holiday_driver', $holiday_driver);
$q->addUpdate('holiday_filter', $holiday_filter);
$q->exec();
$q->clear();

$cal_working_days = w2PgetConfig("cal_working_days");
$newcal_working_days = w2PgetParam($_POST, "cal_working_days", $cal_working_days);
if ($newcal_working_days != $cal_working_days) {
    $q->addTable('config');
    $q->addQuery("config_id");
    $q->addWhere("config_name = 'cal_working_days'");
    $id = $q->loadResult();
    $q->clear();
    $obj = new w2p_Core_Config();
    $obj->load($id);
    $obj->config_value = $newcal_working_days;
    if (($msg = $obj->store($AppUI))) {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
        $AppUI->redirect('m=holiday');
    } 
}
$cal_day_start = w2PgetConfig("cal_day_start");
$newcal_day_start = w2PgetParam($_POST, "cal_day_start", $cal_day_start);
if ($newcal_day_start != $cal_day_start) {
    $q->addTable('config');
    $q->addQuery("config_id");
    $q->addWhere("config_name = 'cal_day_start'");
    $id = $q->loadResult();
    $q->clear();
    $obj = new w2p_Core_Config();
    $obj->load($id);
    $obj->config_value = $newcal_day_start;
    if (($msg = $obj->store($AppUI))) {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
        $AppUI->redirect('m=holiday');
    } 
}
$cal_day_end = w2PgetConfig("cal_day_end");
$newcal_day_end = w2PgetParam($_POST, "cal_day_end", $cal_day_end);
if ($newcal_day_end != $cal_day_end) {
    $q->addTable('config');
    $q->addQuery("config_id");
    $q->addWhere("config_name = 'cal_day_end'");
    $id = $q->loadResult();
    $q->clear();
    $obj = new w2p_Core_Config();
    $obj->load($id);
    $obj->config_value = $newcal_day_end;
    if (($msg = $obj->store($AppUI))) {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
        $AppUI->redirect('m=holiday');
    } 
}
$AppUI->setMsg( "Settings updated" );
$AppUI->redirect('m=holiday');
