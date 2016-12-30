<?php /* $Id$ $URL$ */

if (!defined('W2P_BASE_DIR'))
{
    die('You should not access this file directly.');
}

$tab = $AppUI->processIntState('HolidayTab', $_GET, 'tab', 0);

// Create module header
$titleBlock = new w2p_Theme_TitleBlock('Working time', 'myevo-appointments.png', $m, $m . '.' . $a);
$titleBlock->show();

// tabbed information boxes
$tabBox = new CTabBox( "?m=holiday", W2P_BASE_DIR . '/modules/holiday/', $tab );
if (canEdit('system')) $tabBox->add("holiday_settings", "Company working time");
$tabBox->add("holiday", "User vacations/holidays");
$tabBox->show();