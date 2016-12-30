<?php /* $Id$ $URL$ */

if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

require_once W2P_BASE_DIR."/modules/holiday/holiday_functions.class.php";

/**
 *    Holiday Class
 */
class CHoliday extends w2p_Core_BaseObject
{
    public $holiday_id = null;
    public $holiday_user = null;
    public $holiday_type = null;
    public $holiday_annual = null;
    public $holiday_start_date = null;
    public $holiday_end_date = null;
    public $holiday_description = null;

    public function __construct()
    {
        parent::__construct('holiday', 'holiday_id');
    }

    public function remove(CAppUI $AppUI, w2p_Utilities_Date $date)
    {
        $perms = $AppUI->acl();
        $removed = false;
        if ($this->holiday_id && $perms->checkModuleItem('holiday', 'edit', $this->holiday_id)) {
            $holiday_start_date = new w2p_Utilities_Date($this->holiday_start_date);
            $holiday_end_date = new w2p_Utilities_Date($this->holiday_end_date);
            if ($holiday_start_date->equals($date->duplicate())) {
                if ($holiday_end_date->equals($date->duplicate())) {
                    $removed = $this->delete($AppUI);
                }
                else {
                    $holiday_start_date = new w2p_Utilities_Date($this->holiday_start_date);
                    $this->holiday_start_date = $holiday_start_date->getNextDay()->getDate();
                    $removed = $this->store($AppUI);
                }
            }
            elseif ($holiday_end_date->equals($date->duplicate())) {
                $holiday_end_date = new w2p_Utilities_Date($this->holiday_end_date);
                $this->holiday_end_date = $holiday_end_date->getPrevDay()->getDate();
                $removed = $this->store($AppUI);
            }
            elseif ($holiday_start_date->before($date->duplicate()) && $holiday_end_date->after($date->duplicate())) {
                $holiday_end_date = $this->holiday_end_date;
                $this->holiday_end_date = $date->getPrevDay()->getDate();
                $removed = $this->store($AppUI);
                $this->holiday_id = 0; // create new record
                $this->holiday_start_date = $date->getNextDay()->getDate();
                $this->holiday_end_date = $holiday_end_date;
                $removed = $this->store($AppUI);
            }
        }
        return $removed;
    }

    public function hook_calendar($userId)
    {
        $date = new w2p_Utilities_Date(w2PgetParam($_GET, 'date', null));
        $date->setTime(0, 0, 0);
        $start = $date->duplicate();
        $end = $date->duplicate();
        $view = w2PgetParam($_GET, 'a', 'month_view');
        switch ($view) {
            case 'day_view':
                break;
            case 'week_view':
                $end->addDays(6);
                break;
            case 'index':
            case 'month_view':
                $start->setDay(1);
                $end->setDay($date->getDaysInMonth());
                break;
            case 'year_view':
                $start->setMonth(1);
                $start->setDay(1);
                $end->setMonth(12);
                $end->setDay(31);
                break;
        }
        $end->setTime(23, 59, 59);
        $holidays = HolidayFunctions::getHolidaysForDatespan($start, $end, $userId);
        $itemsList = array();
        foreach ($holidays as $i => $holiday) {
            $date = $holiday['startDate'];
            $end = $holiday['endDate'];
            while (!$date->after(clone $end)) {
                $itemsList[] = array(
                    'id' => $holiday['id'],
                    'name' => $holiday['name'],
                    'startDate' => $date->format(FMT_TIMESTAMP_DATE),
                    'endDate' => $date->format(FMT_TIMESTAMP_DATE),
                    'description' => $holiday['description']
                );
                $date = $date->getNextDay();
            }
        }
        return $itemsList;
    }
    
    public function getCalendarLink(CAppUI $AppUI, $item)
    {
        return array('text'=>$item['description']);
    }
}