<?php /* $Id$ $URL$ */

global $AppUI;

require_once 'PEAR/Holidays.php';
require_once $AppUI->getLibraryClass("PEAR/Date");
require_once "holiday_functions.class.php";

$drivers_alloc = Date_Holidays::getInstalledDrivers();
$drivers_available = array(-1 => $AppUI->_("None"));
for($i=0;$drivers_alloc[$i];$i++){
    $drivers_available[$i]=$drivers_alloc[$i]['title'];
}

$filters_alloc = Date_Holidays::getInstalledFilters();
$filters_available = array(-1 => $AppUI->_("None"));
for($i=0;$filters_alloc[$i];$i++){
    list ($country, $whitelist) = explode("_", $filters_alloc[$i]['title']);
    if ($whitelist) {
        $filters_available[$i]=$AppUI->_($country). " >> " . $AppUI->_($whitelist);
    }
}
// Query database settings
$q = new w2p_Database_Query();
$q->addTable("holiday_settings");
$q->addQuery("holiday_manual, holiday_auto, holiday_driver, holiday_filter");
extract($q->loadHash());
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
$holidaysList = HolidayFunctions::getWhitelistForDatespan($start, $end);
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

$cal_working_days = explode(",",w2PgetConfig("cal_working_days"));
setlocale(LC_ALL, 'C');
$wk = Date_Calc::getCalendarWeek(null, null, null, '%w%A', 0);
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

function getPublicHolidaysOptions(form) {
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
        alert("<?php echo $AppUI->_('Some of these days are already public holidays. Ctrl-click to unselect them and try again !', UI_OUTPUT_JS); ?>");
    }
    else if (nCompanyWorkday == 0 && nCalendarWorkday == 0) {
        alert("<?php echo $AppUI->_('Please, select a day !', UI_OUTPUT_JS); ?>");
    }
    else if (nCalendarWorkday == 0) {
        submitPublicHolidays(form);
    }
    else {
        $('#holiday-choices-help').hide();
        $('#holiday-choices-buttons').hide();
        $('#holiday-choices-options').show();
    }
}

function cancelPublicHolidays() {
    $('#holiday-choices-help').show();
    $('#holiday-choices-buttons').show();
    $('#holiday-choices-options').hide();
}

function submitPublicHolidays(form) {
    $("li.ui-selected").each(function() {
        var date = $(this).attr('id');
        $(this).removeClass("ui-selected");
        if ($(this).hasClass("ui-calendar-workday")) {
            if (!removeFromWorkdays(date)) addToHolidays(date);
            $(this).removeClass("ui-calendar-workday").addClass("ui-calendar-holiday");
        }
        else if ($(this).hasClass("ui-company-workday")) {
            if (!removeFromWorkdays(date)) addToHolidays(date);
            $(this).removeClass("ui-company-workday").addClass("ui-company-holiday");
        }
    });
    $('input:hidden[name=newholidays]').val(holidays.join(","));
    $('input:hidden[name=newworkdays]').val(workdays.join(","));
    $('#loadingMessage').show();
    form.submit();
}

function getWorkingDaysOptions(form) {
    var nCompanyHoliday = 0;
    var nCalendarHoliday = 0;
    var ok = true;
    $("li.ui-selected").each(function() {
        if ($(this).hasClass("ui-company-holiday")) {
            nCompanyHoliday++;
        }
        else if ($(this).hasClass("ui-calendar-holiday")) {
            nCalendarHoliday++;
        }
        else {
            ok = false;
        }
    });
    if (!ok) {
        alert("<?php echo $AppUI->_('Some of these days are already working days. Ctrl-click to unselect them and try again !', UI_OUTPUT_JS); ?>");
    }
    else if (nCompanyHoliday == 0 && nCalendarHoliday == 0) {
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
        if ($(this).hasClass("ui-calendar-holiday")) {
            if (!removeFromHolidays(date)) addToWorkdays(date);
            $(this).removeClass("ui-calendar-holiday").addClass("ui-calendar-workday");
        }
        else if ($(this).hasClass("ui-company-holiday")) {
            if (!removeFromHolidays(date)) addToWorkdays(date);
            $(this).removeClass("ui-company-holiday").addClass("ui-company-workday");
        }
    });
    $('input:hidden[name=newholidays]').val(holidays.join(","));
    $('input:hidden[name=newworkdays]').val(workdays.join(","));
    $('#loadingMessage').show();
    form.submit();
}

function submitOptions(form) {
    var workingdays = [];
    $("input:checked[name=cal_working_day]").each(function() {
        workingdays.push($(this).val());
    });
    $('input:hidden[name=cal_working_days]').val(workingdays.join(","));
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
            <form id="editholidaysettings" name="editholidaysettings" method="post" accept-charset="utf-8">
            <input type="hidden" name="dosql" value="do_holiday_settings_aed" />
            <input type="hidden" name="cal_working_days" value="" />
            <div id="holiday-options">
                <fieldset>
                    <legend><?php echo $AppUI->_('Calendar Options'); ?></legend>
                    <div class="settings">
                        <ul>
                            <li><?php echo $AppUI->_( 'Standard calendar' )."&nbsp;&nbsp".arraySelect( $drivers_available,"holiday_driver",null,$holiday_driver); ?></li>
                            <li><?php echo $AppUI->_( 'Standard calendar filter' )."&nbsp;&nbsp".arraySelect( $filters_available,"holiday_filter",null,$holiday_filter); ?></li>
                            <li><?php echo $AppUI->_( 'Enable standard calendar' );?><input type="checkbox" value="1" name="holiday_auto"<?php echo $holiday_auto ? 'checked="checked"' : ""; ?> /></li>
                            <li><?php echo $AppUI->_( 'Enable manual working days and public holidays' );?><input type="checkbox" value="1" name="holiday_manual"<?php echo $holiday_manual ? 'checked="checked"' : ""; ?> /></li>
                        </ul>
                    </div><br />
                    <div class="settings">
                        <ul>
                            <li><?php echo $AppUI->_( 'cal_working_days_title' ); ?> : </li>
                            <?php foreach ($wk as $day) {
                            $dow = substr($day, 0, 1);
                            $checked = in_array($dow, $cal_working_days) ? ' checked="checked"' : '';
                            ?>
                            <li><input type="checkbox" name="cal_working_day" value="<?php echo $dow; ?>"<?php echo $checked; ?>><?php echo $AppUI->_(substr($day, 1));?></input></li>
                            <?php } ?>
                            <li><?php echo $AppUI->_('Working times');?> : <input type="text" class="cal_day" name="cal_day_start" size="5" value="<?php echo w2PgetConfig("cal_day_start"); ?>" /></li>
                            <li><?php echo $AppUI->_('to');?> <input type="text" class="cal_day" name="cal_day_end" size="5" value="<?php echo w2PgetConfig("cal_day_end"); ?>" /></li>
                            <li><input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('save'); ?>" onClick="submitOptions(this.form);" /></li>
                        </ul>
                    </div>
                </fieldset>
            </div> 
            </form> 
            <form id="editholiday" name="editholiday" method="post" accept-charset="utf-8">
            <input type="hidden" name="dosql" value="do_holiday_aed" />
            <input type="hidden" name="target" value="calendar">
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
                    $cal = Date_Calc::getCalendarMonth($m, $year, '%Y%m%d%w', LOCALE_FIRST_DAY); //TODO : too long ?>
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
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-company-holiday ui-selectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
                                                    <?php break;
                                                case HOLIDAY_TYPE_CALENDAR_HOLIDAY : ?>
                                                    <li id="<?php echo $id.'-'.$day.'-'.$name; ?>" class="ui-calendar-holiday ui-selectable" title="<?php echo $description; ?>"><?php echo $d; ?></li>
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
                        <legend><?php echo $AppUI->_( 'Adding pubic holidays/working days' ); ?></legend>
                        <div id="holiday-choices-help">
                            <?php echo $AppUI->_('Days selection help'); ?>
                            <br /><br />
                        </div>
                        <div id="holiday-choices-options" style="display: none;">
                            <?php echo $AppUI->_('Enter a description for these new holidays'); ?>
                            <input type="text" name="holiday_description" /><br />
                            <?php echo $AppUI->_( 'For all years' );?><input type="checkbox" value="1" name="holiday_annual" checked="checked" /><br />
                            <?php echo $AppUI->_('then click'); ?> 
                            <input class="button" type="button" name="addNewHolidays" onclick="submitPublicHolidays(this.form);" value="<?php echo $AppUI->_( 'Ok' ); ?>" />
                            <?php echo $AppUI->_('or'); ?> 
                            <input class="button" type="button" name="addNewHolidays" onclick="cancelPublicHolidays();" value="<?php echo $AppUI->_( 'Cancel' ); ?>" />
                        </div>
                        <div id="holiday-choices-buttons">
                            <input class="button" type="button" name="addNewHolidays" onclick="getPublicHolidaysOptions(this.form);" value="<?php echo $AppUI->_( 'Add to public holidays' ); ?>" /><br />
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
