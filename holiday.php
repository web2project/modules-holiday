<?php /* $Id$ $URL$ */

require_once 'PEAR/Holidays.php';
require_once "holiday_functions.class.php";

global $AppUI;

$AppUI = is_object($AppUI) ? $AppUI : new w2p_Core_CAppUI();

$user_id = w2PgetParam($_REQUEST, 'user_id', null);
if (!is_null( $user_id )) {
    $AppUI->setState( 'HolidaySelectedUser', $user_id );
}
else {
    $user_id = $AppUI->getState( 'HolidaySelectedUser' ) ? $AppUI->getState( 'HolidaySelectedUser' ) : $AppUI->user_id;
}

$is_my_holidays = ($user_id == $AppUI->user_id);

// establish the focus 'date'
$date = w2PgetParam($_GET, 'date', null);
if (!$date) {
    $date = new w2p_Utilities_Date();
} else {
    $date = new w2p_Utilities_Date($date);
}
$date->setDay(1);
$date->setMonth(1);
$date->setTime(0, 0, 0);
$year = $date->getYear();
$prev_year = $date->format(FMT_TIMESTAMP_DATE);
$prev_year = (int)($prev_year - 10000);
$next_year = $date->format(FMT_TIMESTAMP_DATE);
$next_year = (int)($next_year + 10000);
$start = $date->duplicate();
$end = $date->duplicate();
$end->setMonth(12);
$end->setDay(31);
$end->setTime(23, 59, 59);
$holidays = array();
$holidaysList = HolidayFunctions::getDefaultCalendarHolidaysForDatespan($start, $end);
foreach($holidaysList as $holiday) {
    $id = 0;
    $type = $holiday['type'];
    $description = $holiday['description'] ? $holiday['description'] : "";
    $name = $holiday['name'] ? $holiday['name'] : "";
    $odate = $holiday['startDate'];
    $oend = $holiday['endDate'];
    $cdate = clone $odate;
    while (!$cdate->after(clone $oend)) {
        $holidays[$odate->format(FMT_TIMESTAMP_DATE)] = array($id, $type, $description, $name);
        $odate = $odate->getNextDay();
        $cdate = clone $odate;
    }
}
$holidaysList = HolidayFunctions::getBlacklistForDatespan($start, $end);
foreach($holidaysList as $holiday) {
    $id = $holiday['id'];
    $type = $holiday['type'];
    $description = $holiday['description'] ? $holiday['description'] : "";
    $odate = $holiday['startDate'];
    $oend = $holiday['endDate'];
    $cdate = clone $odate;
    while (!$cdate->after(clone $oend)) {
        $holidays[$odate->format(FMT_TIMESTAMP_DATE)] = array($id, $type, $description, "");
        $odate = $odate->getNextDay();
        $cdate = clone $odate;
    }
}
$holidaysList = HolidayFunctions::getWhitelistForDatespan($start, $end, $user_id);
foreach($holidaysList as $holiday) {
    $id = $holiday['id'];
    $type = $holiday['type'];
    $description = $holiday['description'] ? $holiday['description'] : "";
    $odate = $holiday['startDate'];
    $oend = $holiday['endDate'];
    $cdate = clone $odate;
    while (!$cdate->after(clone $oend)) {
        $holidays[$odate->format(FMT_TIMESTAMP_DATE)] = array($id, $type, $description, "");
        $odate = $odate->getNextDay();
        $cdate = clone $odate;
    }
}
$cal_working_days = explode(",",w2PgetConfig("cal_working_days"));
$user_holiday_types = w2PgetSysVal('UserHolidayType');
setlocale(LC_ALL, 'C');

?>

<style type="text/css">
    @import "modules/holiday/style/common.css";
    @import "modules/holiday/style/<?php echo $AppUI->getPref('UISTYLE'); ?>/styles.css";
</style>
<script type="text/javascript">
var workdays = [];
var holidays = [];

function addToWorkdays(date) {
    for (i in workdays) {
        if (workdays[i] == date) {
            return false;
        }
    }
    workdays.push(date);
    return true;
}

function removeFromWorkdays(date) {
    for (i in workdays) {
        if (workdays[i] == date) {
            workdays.splice(i, 1);
            return true;
        }
    }
    return false;
}

function addToHolidays(date) {
    for (i in holidays) {
        if (holidays[i] == date) {
            return false;
        }
    }
    holidays.push(date);
    return true;
}

function removeFromHolidays(date) {
    for (i in holidays) {
        if (holidays[i] == date) {
            holidays.splice(i, 1);
            return true;
        }
    }
    return false;
}

function getUserHolidaysOptions(form) {
    var nCompanyWorkday = 0;
    var nCalendarWorkday = 0;
    var ok = true;
    $("li.ui-selected").each(function() {
        if ($(this).hasClass("ui-company-workday")) {
            nCompanyWorkday++;
        }
        else if ($(this).hasClass("ui-calendar-workday")) {
            nCalendarWorkday++;
        }
        else {
            ok = false;
        }
    });
    if (!ok) {
        alert("<?php echo $AppUI->_('Some of these days are already user holidays. Ctrl-click to unselect them and try again !', UI_OUTPUT_JS); ?>");
    }
    else if (nCompanyWorkday == 0 && nCalendarWorkday == 0) {
        alert("<?php echo $AppUI->_('Please, select a day !', UI_OUTPUT_JS); ?>");
    }
    else if (nCalendarWorkday == 0) {
        submitUserHolidays(form);
    }
    else {
        $('#holiday-choices-help').hide();
        $('#holiday-choices-buttons').hide();
        $('#holiday-choices-options').show();
    }
}

function cancelUserHolidays() {
    $('#holiday-choices-help').show();
    $('#holiday-choices-buttons').show();
    $('#holiday-choices-options').hide();
}

function submitUserHolidays(form) {
    $("li.ui-selected").each(function() {
        var date = $(this).attr('id');
        $(this).removeClass("ui-selected");
        if ($(this).hasClass("ui-calendar-workday")) {
            if (!removeFromWorkdays(date)) addToHolidays(date);
            $(this).removeClass("ui-calendar-workday").addClass("ui-user-holiday");
        }
        else if ($(this).hasClass("ui-company-workday")) {
            if (!removeFromWorkdays(date)) addToHolidays(date);
            $(this).removeClass("ui-company-workday").addClass("ui-user-holiday");
        }
    });
    $('input:hidden[name=newholidays]').val(holidays.join(","));
    $('input:hidden[name=newworkdays]').val(workdays.join(","));
    $('#loadingMessage').show();
    form.submit();
}

function getWorkingDaysOptions(form) {
    var nHoliday = 0;
    var ok = true;
    $("li.ui-selected").each(function() {
        if ($(this).hasClass("ui-user-holiday")) {
            nHoliday++;
        }
        else {
            ok = false;
        }
    });
    if (!ok) {
        alert("<?php echo $AppUI->_('Some of these days are not user holidays. Ctrl-click to unselect them and try again !', UI_OUTPUT_JS); ?>");
    }
    else if (nHoliday == 0) {
        alert("<?php echo $AppUI->_('Please, select a day !', UI_OUTPUT_JS); ?>");
    }
    else {
        submitWorkingDays(form);
    }
}

function submitWorkingDays(form) {
    $("li.ui-selected").each(function() {
        var date = $(this).attr('id');
        $(this).removeClass("ui-selected");
        if ($(this).hasClass("ui-user-holiday")) {
            if (!removeFromHolidays(date)) addToWorkdays(date);
            $(this).removeClass("ui-user-holiday").addClass("ui-calendar-workday");
        }
    });
    $('input:hidden[name=newholidays]').val(holidays.join(","));
    $('input:hidden[name=newworkdays]').val(workdays.join(","));
    $('#loadingMessage').show();
    form.submit();
}

$.getScript ("modules/holiday/lib/ui-selectable/jquery.ui.core.js", function () {
    $.getScript ("modules/holiday/lib/ui-selectable/jquery.ui.widget.js", function () {
        $.getScript ("modules/holiday/lib/ui-selectable/jquery.ui.mouse.js", function () {
            $.getScript ("modules/holiday/lib/ui-selectable/jquery-ui-1.8.7.custom.js", function () {
                $.getScript ("modules/holiday/lib/ui-selectable/jquery.ui.selectable.js", function () {
                    $(document).ready(function() {
                        $( "ol.selectable" ).selectable( { filter: '.ui-selectable' });
                    });
                });
            });
        });
    });
});
</script>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <td>
            <?php if (canEdit('admin')) { ?>
            <form id="editholidayuser" name="editholidayuser" method="post" accept-charset="utf-8">
            <div id="holiday-user">
                <select name="user_id" onChange="this.form.submit();" class="text">
                    <option value="0"></option>
                    <?php
                    $users = w2PgetUsersList();
                    foreach ($users as $id => $user) {
                        $selected = $user['user_id'] == $user_id ? ' selected="selected"' : '';
                        echo '<option value="' . $user['user_id'] . '"' . $selected . '>' . $user['contact_first_name'] . ' ' . $user['contact_last_name'] . '</option>';
                    }
                    ?>
                </select>
                <a href="?m=holiday&user_id=<?php echo $AppUI->user_id; ?>"><?php echo '['.$AppUI->_('My vacations/holidays').']';?></a>
            </div> 
            </form>
            <?php } ?>
            <form id="editholiday" name="editholiday" method="post" accept-charset="utf-8">
            <input type="hidden" name="dosql" value="do_holiday_aed" />
            <input type="hidden" name="target" value="user">
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
            <input type="hidden" name="newholidays" value=""/>
            <input type="hidden" name="newworkdays" value=""/>
            <div>
                <div id="holiday-calendar">
                    <table width="100%" cellspacing="0" cellpadding="4">
                        <tr>
                            <td colspan="20" valign="top">
                                <table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
                                    <tr>
                                        <td>
                                            <a href="<?php echo '?m=holiday&date=' . $prev_year; ?>"><img src="<?php echo w2PfindImage('prev.gif'); ?>" width="16" height="16" alt="pre" title="pre" border="0"></a>
                                        </td>
                                        <th width="100%" align="center">
                                            <?php echo $year; ?>
                                        </th>
                                        <td>
                                            <a href="<?php echo '?m=holiday&date=' . $next_year; ?>"><img src="<?php echo w2PfindImage('next.gif'); ?>" width="16" height="16" alt="next" title="next" border="0"></a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                <?php $wk = Date_Calc::getCalendarWeek(null, null, null, '%a', LOCALE_FIRST_DAY); for ($m = 1; $m <= 12; $m++) {
                    $monthname = Date_Calc::getMonthFullname($m);
                    $cal = Date_Calc::getCalendarMonth($m, $year, '%Y%m%d%w', LOCALE_FIRST_DAY); ?> 
                    <div class="holiday-calendar-month">
                        <span class="holiday-calendar-monthname"><?php echo $AppUI->_($monthname, UI_CASE_UPPERFIRST).'&nbsp;'; ?></span>
                        <ol class="selectable">
                        <?php 
                            foreach ($wk as $day) { ?>
                                <li class="ui-dayname"><?php $d = $AppUI->_($day); echo strtoupper($d{0}); ?></li>
                            <?php }
                            $n = 0;
                            foreach ($cal as $week) {
                                foreach ($week as $day) {
                                    $month = intval(substr($day, 4, 2));
                                    $d = intval(substr($day, 6, 2));
                                    $dow = intval(substr($day, 8, 1));
                                    $day = substr($day, 0, 8);
                                    if ($m == $month) {
                                        if (isset($holidays[$day])) {
                                            $id = $holidays[$day][0];
                                            $type = $holidays[$day][1];
                                            $description = $holidays[$day][2];
                                            $name = $holidays[$day][3];
                                            switch ($type) {
                                                case HOLIDAY_TYPE_COMPANY_WORKDAY : ?>
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-company-workday ui-selectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
                                                    <?php break;
                                                case HOLIDAY_TYPE_COMPANY_HOLIDAY : ?>
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-company-holiday ui-unselectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
                                                    <?php break;
                                                case HOLIDAY_TYPE_USER_HOLIDAY : ?>
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-user-holiday ui-selectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
                                                    <?php break;
                                                case HOLIDAY_TYPE_CALENDAR_HOLIDAY : ?>
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-calendar-holiday ui-unselectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
                                                    <?php break;
                                            } } elseif (in_array($dow, $cal_working_days)) { ?>
                                            <li id="<?php echo '0-'.$day.'-'; ?>" class="ui-calendar-workday ui-selectable"><?php echo $d; ?></li>
                                        <?php } else { ?>
                                            <li id="<?php echo '0-'.$day.'-'; ?>" class="ui-weekend ui-unselectable"><?php echo $d; ?></li>
                                    <?php } } else { ?>
                                        <li class="ui-unselectable"></li>
                                    <?php }
                                    $n++;
                                }
                            }
                            while ($n < 42) { ?>
                                <li class="ui-unselectable"></li>
                            <?php $n++; }
                        ?> 
                        </ol>
                    </div> 
                <?php
                } 
                ?>
                </div> 
                <div id="holiday-choices">
                    <fieldset>
                        <legend><?php echo $AppUI->_( 'Adding holidays/working days' ); ?></legend>
                        <div id="holiday-choices-help">
                            <?php echo $AppUI->_('Days selection help'); ?>
                            <br /><br />
                        </div>
                        <div id="holiday-choices-options" style="display: none;">
                            <?php echo $AppUI->_('Select an absence type'); ?><br />
                            <?php foreach ($user_holiday_types as $key => $user_holiday_type) { ?>
                            <input type="radio" name="holiday_description" value="<?php echo $AppUI->_($user_holiday_type)?>"/><?php echo $AppUI->_($user_holiday_type)?><br />
                            <?php } ?> 
                            <?php echo $AppUI->_('then click'); ?> 
                            <input class="button" type="button" name="addNewHolidays" onclick="submitUserHolidays(this.form);" value="<?php echo $AppUI->_( 'Ok' ); ?>" />
                            <?php echo $AppUI->_('or'); ?> 
                            <input class="button" type="button" name="addNewHolidays" onclick="cancelUserHolidays();" value="<?php echo $AppUI->_( 'Cancel' ); ?>" />
                        </div>
                        <div id="holiday-choices-buttons">
                            <input class="button" type="button" name="addNewHolidays" onclick="getUserHolidaysOptions(this.form);" value="<?php echo $AppUI->_( 'Add to user holidays' ); ?>" /><br />
                            <input class="button" type="button" name="addNewWorkdays" onclick="getWorkingDaysOptions(this.form);" value="<?php echo $AppUI->_( 'Add to working days' ); ?>" /><br />
                        </div>
                    </fieldset>
                </div>
                <div id="holiday-key"> 
                    <fieldset>
                        <legend><?php echo $AppUI->_( 'Key' ); ?></legend>
                        <div class="selectable">
                            <?php echo $AppUI->_('Calendar Workday'); ?>
                            <span class="ui-calendar-workday">&nbsp;&nbsp;&nbsp;</span>
                            &nbsp;&nbsp;<?php echo $AppUI->_('Company Workday'); ?>
                            <span class="ui-company-workday">&nbsp;&nbsp;&nbsp;</span>
                            &nbsp;&nbsp;<?php echo $AppUI->_('Calendar holiday'); ?>
                            <span class="ui-calendar-holiday">&nbsp;&nbsp;&nbsp;</span>
                            &nbsp;&nbsp;<?php echo $AppUI->_('Company holiday'); ?>
                            <span class="ui-company-holiday">&nbsp;&nbsp;&nbsp;</span>
                            &nbsp;&nbsp;<?php echo $AppUI->_('User holiday'); ?>
                            <span class="ui-user-holiday">&nbsp;&nbsp;&nbsp;</span>
                            &nbsp;&nbsp;<?php echo $AppUI->_('Selected days'); ?>
                            <span class="ui-selected">&nbsp;&nbsp;&nbsp;</span>
                        </div>
                    </fieldset>
                </div> 
            </div>
            </form> 
        </td>
    </tr>
</table>
<?php setlocale(LC_ALL, $AppUI->user_lang); ?> 

