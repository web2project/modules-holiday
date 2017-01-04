<?php
/**
 * Driver for holidays in France
 *
 * PHP Version 4
 *
 * Copyright (c) 1997-2008 The PHP Group
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available at through the world-wide-web at
 * http://www.php.net/license/3_01.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category Date
 * @package	Date_Holidays
 * @author	 Stephan Schmidt <schst@php-tools.net>
 * @author	 Carsten Lucke <luckec@tool-garage.de>
 * @license	http://www.php.net/license/3_01.txt PHP License 3.0.1
 */

/**
 * Extends Christian driver
 */
require_once 'Christian.php';

/**
 * class that calculates French holidays
 *
 * @category	 Date
 * @package		Date_Holidays
 * @subpackage Driver
 * @author		 Jacques Archimède <eureka@dbmail.com>
 * @license		http://www.php.net/license/3_01.txt PHP License 3.0.1
 * @version		CVS: $Id: France.php,v 1.13 2009/03/14 22:30:14 kguest Exp $
 * @link			 http://pear.php.net/package/Date_Holidays
 */
class Date_Holidays_Driver_France extends Date_Holidays_Driver_Christian
{
	/**
	 * this driver's name
	 *
	 * @access	 protected
	 * @var			string
	 */
	var $_driverName = 'France';

	/**
	 * Constructor
	 *
	 * Use the Date_Holidays::factory() method to construct an object of a
	 * certain driver
	 *
	 * @access	 protected
	 */
	function Date_Holidays_Driver_France()
	{
	}

	/**
	 * Build the internal arrays that contain data about the calculated holidays
	 *
	 * @access	 protected
	 * @return	 boolean true on success, otherwise a PEAR_ErrorStack object
	 * @throws	 object PEAR_ErrorStack
	 */
	function _buildHolidays()
	{
		parent::_buildHolidays();

		/**
		 * New Year's Day
		 */
		$this->_addHoliday('newYearsDay', $this->_year . '-01-01', 'New Year\'s Day');

		/**
		 * Epiphanias
		 */
		$firstSunday = $this->_calcNthWeekDayInMonth(1, 0, 1);
		if ($firstSunday->getDay() == 1) {
			$firstSunday = $this->_addDays($firstSunday, 7);
		}
		$this->_addHoliday('epiphanySunday', $firstSunday, 'Epiphany');

		/**
		 * Valentine's Day
		 */
		$this->_addHoliday('valentinesDay', $this->_year . '-02-14', 'Valentine\'s Day');

		/**
		 * Easter Sunday
		 */
		$easterDate = $this->getHolidayDate('easter');

		/**
		 * Shrove Monday
		 */
		$shroveMondayDate = $this->_addDays($easterDate, -48);
		$this->_addHoliday('shroveMonday', $shroveMondayDate, 'Shrove Monday');

		/**
		 * Shrove Tuesday
		 */
		$shroveTuesdayDate = $this->_addDays($shroveMondayDate , 1);
		$this->_addHoliday('shroveTuesday', $shroveTuesdayDate, 'Shrove Tuesday');

		/**
		 * International day of work
		 */
		$this->_addHoliday('dayOfWork', $this->_year . '-05-01', 'International day of work');

		/**
		 * 1945 victory
		 */
		$this->_addHoliday('victory1945', $this->_year . '-05-08', 'Victory of 1945');

		/**
		 * Mother's Day
		 */
		$mothersDay = $this->_calcLastSunday(5);
		$whitsun = $this->getHolidayDate('whitsun');
		$mothersDay2 = new Date($mothersDay);
		if($mothersDay2->equals(clone $whitsun)) {
			$mothersDay = $this->_addDays($mothersDay, 7);
		}
		$this->_addHoliday('mothersDay', $mothersDay, 'Mothers\' Day');

		/**
		 * Abolition of Slavery Day (Mayotte)
		 */
		$this->_addHoliday('abolitionOfSlaveryDayMayotte', $this->_year.'-04-27', 'Abolition of Slavery Day');

		/**
		 * Saint-Pierre-Chanel Day (Wallis and Futuna)
		 */
		$this->_addHoliday('saintPierreChanelDay', $this->_year.'-04-28', 'Saint-Pierre-Chanel Day');

		/**
		 * Abolition of Slavery Day (Martinique)
		 */
		$this->_addHoliday('abolitionOfSlaveryDayMartinique', $this->_year.'-05-22', 'Abolition of Slavery Day');

		/**
		 * Abolition of Slavery Day (Guadeloupe)
		 */
		$this->_addHoliday('abolitionOfSlaveryDayGuadeloupe', $this->_year.'-05-27', 'Abolition of Slavery Day');

		/**
		 * Abolition of Slavery Day (Guyana)
		 */
		$this->_addHoliday('abolitionOfSlaveryDayGuyana', $this->_year.'-06-10', 'Abolition of Slavery Day');

		/**
		 * Autonomy Day (French Polynesia)
		 */
		$this->_addHoliday('autonomyDay', $this->_year.'-06-29', 'Autonomy Day');

		/**
		 * French National Day
		 */
		$this->_addHoliday('frenchNationalDay', $this->_year.'-07-14', 'French National Day');

		/**
		 * Victor Schoelcher Day
		 */
		$this->_addHoliday('victorSchoelcherDay', $this->_year.'-07-21', 'Victor Schoelcher Day');

		/**
		 * Territory Day (Wallis and Futuna)
		 */
		$this->_addHoliday('territoryDay', $this->_year.'-07-29', 'Territory Day');

		/**
		 * Celebration of citizenship Day (New Caledonia)
		 */
		$this->_addHoliday('celebrationOfCitizenshipDay', $this->_year.'-09-24', 'Celebration of citizenship Day');

		/**
		 * Veteran's	Day
		 */
		$this->_addHoliday('veteransDay', $this->_year.'-11-11', 'Veteran\'s Day');

		/**
		 * Abolition of Slavery Day (Reunion)
		 */
		$this->_addHoliday('abolitionOfSlaveryDayReunion', $this->_year.'-12-20', 'Abolition of Slavery Day');

		/**
		 * Saint Étienne Day(Alsace & Moselle)
		 */
		$this->_addHoliday('saintEtienneDay', $this->_year.'-12-26', 'Saint Étienne Day');

		if (Date_Holidays::errorsOccurred()) {
			return Date_Holidays::getErrorStack();
		}
		return true;
	}

	/**
	 * Sets the driver's locale
	 *
	 * @param string $locale locale
	 *
	 * @access	 public
	 * @return	 void
	 */
	function setLocale($locale)
	{
		$this->_locale = $locale;
		//if possible, load the translation files for this locale
		$this->addTranslation($locale);
		$this->_addTranslationForHoliday('newYearsDay', 'fr', 'Nouvel an');
		$this->_addTranslationForHoliday('newYearsDay', 'fr_FR', 'Nouvel an');
		$this->_addTranslationForHoliday('epiphanySunday', 'fr', 'Epiphanie');
		$this->_addTranslationForHoliday('epiphanySunday', 'fr_FR', 'Epiphanie');
		$this->_addTranslationForHoliday('valentinesDay', 'fr', 'Saint-Valentin');
		$this->_addTranslationForHoliday('valentinesDay', 'fr_FR', 'Saint-Valentin');
		$this->_addTranslationForHoliday('shroveMonday', 'fr', 'Lundi Gras');
		$this->_addTranslationForHoliday('shroveMonday', 'fr_FR', 'Lundi Gras');
		$this->_addTranslationForHoliday('shroveTuesday', 'fr', 'Mardi Gras');
		$this->_addTranslationForHoliday('shroveTuesday', 'fr_FR', 'Mardi Gras');
		$this->_addTranslationForHoliday('whitMonday', 'fr', 'Lundi de Pentecôte');
		$this->_addTranslationForHoliday('whitMonday', 'fr_FR', 'Lundi de Pentecôte');
		$this->_addTranslationForHoliday('dayOfWork', 'fr', 'Fête du travail');
		$this->_addTranslationForHoliday('dayOfWork', 'fr_FR', 'Fête du travail');
		$this->_addTranslationForHoliday('victory1945', 'fr', 'Victoire 1945');
		$this->_addTranslationForHoliday('victory1945', 'fr_FR', 'Victoire 1945');
		$this->_addTranslationForHoliday('mothersDay', 'fr', 'Fête des mères');
		$this->_addTranslationForHoliday('mothersDay', 'fr_FR', 'Fête des mères');
 		$this->_addTranslationForHoliday('abolitionOfSlaveryDayMayotte', 'fr', 'Abolition de l\'esclavage');
 		$this->_addTranslationForHoliday('abolitionOfSlaveryDayMayotte', 'fr_FR', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('saintPierreChanelDay', 'fr', "Saint-Pierre-Chanel");
		$this->_addTranslationForHoliday('saintPierreChanelDay', 'fr_FR', "Saint-Pierre-Chanel");
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayMartinique', 'fr', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayMartinique', 'fr_FR', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayGuadeloupe', 'fr', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayGuadeloupe', 'fr_FR', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayGuyana', 'fr', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayGuyana', 'fr_FR', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('autonomyDay', 'fr', 'Fête de l\'autonomie');
		$this->_addTranslationForHoliday('autonomyDay', 'fr_FR', 'Fête de l\'autonomie');
		$this->_addTranslationForHoliday('frenchNationalDay', 'fr', 'Fête nationale');
		$this->_addTranslationForHoliday('frenchNationalDay', 'fr_FR', 'Fête nationale');
		$this->_addTranslationForHoliday('victorSchoelcherDay', 'fr', 'Commémoration Victor Schoelcher');
		$this->_addTranslationForHoliday('victorSchoelcherDay', 'fr_FR', 'Commémoration Victor Schoelcher');
		$this->_addTranslationForHoliday('territoryDay', 'fr', 'Fête du Territoire');
		$this->_addTranslationForHoliday('territoryDay', 'fr_FR', 'Fête du Territoire');
		$this->_addTranslationForHoliday('celebrationOfCitizenshipDay', 'fr', 'Fête de la citoyenneté');
		$this->_addTranslationForHoliday('celebrationOfCitizenshipDay', 'fr_FR', 'Fête de la citoyenneté');
		$this->_addTranslationForHoliday('veteransDay', 'fr', 'Armistice 1918');
		$this->_addTranslationForHoliday('veteransDay', 'fr_FR', 'Armistice 1918');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayReunion', 'fr', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('abolitionOfSlaveryDayReunion', 'fr_FR', 'Abolition de l\'esclavage');
		$this->_addTranslationForHoliday('saintEtienneDay', 'fr', 'Saint Étienne');
		$this->_addTranslationForHoliday('saintEtienneDay', 'fr_FR', 'Saint Étienne');
	}

	/**
	 * Method that returns an array containing the ISO3166 codes that may possibly
	 * identify a driver.
	 *
	 * @static
	 * @access public
	 * @return array possible ISO3166 codes
	 */
	function getISO3166Codes()
	{
		return array('FR', 'FRA');
	}

	/**
	 * Find the date of the last monday in the specified year of the current year.
	 *
	 * @param integer $month month
	 *
	 * @access	 private
	 * @return	 object Date date of last monday in specified month.
	 */
	function _calcLastSunday($month)
	{
		//work backwards from the first day of the next month.
		$nm = ((int) $month ) + 1;
		$ny = $this->getYear();
		if ($nm > 12) {
			$nm = 1;
			$ny++;
		}
		$date = new Date($ny."-".sprintf("%02d", $nm)."-01");
		$date = $date->getPrevDay();
		while ($date->getDayOfWeek() != 0) {
			$date = $date->getPrevDay();
		}
		return ($date);
	}
}
?>
