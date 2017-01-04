<?php /* $Id$ $URL$ */

// MODULE CONFIGURATION DEFINITION
$config = array();
$config['mod_name'] = 'Holiday';
$config['mod_version'] = '1.0';
$config['mod_directory'] = 'holiday';
$config['mod_setup_class'] = 'CSetupHoliday';
$config['mod_type'] = 'admin';
$config['mod_ui_name'] = 'Working time';
$config['mod_ui_icon'] = 'notepad.gif';
$config['mod_description'] = 'A module for setting working time';
$config['mod_config'] = false;
$config['mod_main_class'] = 'CHoliday'; // the name of the PHP class used by the module
$config['permissions_item_table'] = 'holiday';
$config['permissions_item_label'] = 'holiday_description';
$config['permissions_item_field'] = 'holiday_id';

if (@$a == 'setup') {
    echo w2PshowModuleConfig( $config );
}

class CSetupHoliday
{

    public function install()
    {
        global $AppUI;

        // Create holiday table
        $q = new w2p_Database_Query();
        $q->createTable('holiday');
        $q->createDefinition('(
            `holiday_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `holiday_user` int(10) NOT NULL DEFAULT \'0\',
            `holiday_type` int(10) NOT NULL DEFAULT \'0\',
            `holiday_annual` int(10) NOT NULL DEFAULT \'0\',
            `holiday_start_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
            `holiday_end_date` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
            `holiday_description` text,
            PRIMARY KEY (`holiday_id`),
            KEY `holiday_start_end_date` (`holiday_start_date`,`holiday_end_date`),
            KEY `holiday_type` (`holiday_type`),
            KEY `holiday_user` (`holiday_user`)
            ) ENGINE=MyISAM CHARACTER SET=utf8 COLLATE=utf8_general_ci
        ');
        $q->exec();
        $q->clear();

        // Create settings table
        $q->createTable('holiday_settings');
        $q->createDefinition('(
            `holiday_manual` int(10) NOT NULL default \'0\',
            `holiday_auto` int(10) NOT NULL default \'0\',
            `holiday_driver` int(10) NOT NULL default \'-1\',
            `holiday_filter` int(10) NOT NULL default \'-1\',
            UNIQUE KEY `holiday_manual` (holiday_manual),
            UNIQUE KEY `holiday_auto` (holiday_auto),
            UNIQUE KEY `holiday_driver` (holiday_driver),
            UNIQUE KEY `holiday_filter` (holiday_filter)
            ) ENGINE=MyISAM CHARACTER SET=utf8 COLLATE=utf8_general_ci
        ');
        $q->exec();
        $q->clear();

        // Set default settings
        $q->addTable('holiday_settings');
        $q->addInsert('holiday_manual', 0);
        $q->addInsert('holiday_auto', 0);
        $q->addInsert('holiday_driver', -1);
        $q->addInsert('holiday_filter', -1);
        $q->exec();

        $i = 0;
        $user_holiday_types = array('holidays', 'sick stoppage', 'formation', 'seminar', 'mission', 'strike', 'other holiday');
        foreach ($user_holiday_types as $user_holiday_type) {
            $q->clear();
            $q->addTable('sysvals');
            $q->addInsert('sysval_key_id', 1); // select list
            $q->addInsert('sysval_title', 'UserHolidayType');
            $q->addInsert('sysval_value', $user_holiday_type);
            $q->addInsert('sysval_value_id', $i++);
            $q->exec();
        }

        $perms = $AppUI->acl();
        return $perms->registerModule('Holiday', 'holiday');
    }

    public function remove()
    {
        global $AppUI;
        $q = new w2p_Database_Query();

        $q->dropTable('holiday');
        $q->exec();
        $q->clear();

        $q->dropTable('holiday_settings');
        $q->exec();
        $q->clear();

        $q->setDelete('modules');
        $q->addWhere("mod_directory = 'holiday'");
        $q->exec();
        $q->clear();

        $q->setDelete('sysvals');
        $q->addWhere('sysval_title = \'UserHolidayType\'');
        $q->exec();
        
        $perms = $AppUI->acl();
        return $perms->unregisterModule('holiday');
    }
    
    public function upgrade($old_version)
    {
        switch ($old_version) {
            case '0.1':
                // There is no way to change the name of database field with w2p_Database_Query().
                db_exec("ALTER TABLE holiday CHANGE holiday_white holiday_type int(10) NOT NULL DEFAULT '0'");
                if (db_error()) {
                    return false;
                }

                $q = new w2p_Database_Query();
                $q->alterTable('holiday');
                $q->createDefinition('index holiday_start_end_date (holiday_start_date, holiday_end_date)');
                $q->exec();
                $q->clear();

                $q->alterTable('holiday');
                $q->createDefinition('index holiday_start_end_date (holiday_start_date, holiday_end_date)');
                $q->exec();
                $q->clear();

                $q->alterTable('holiday');
                $q->createDefinition('index holiday_user (holiday_user)');
                $q->exec();
                $q->clear();

                $q->alterTable('holiday');
                $q->createDefinition('index holiday_type (holiday_type)');
                $q->exec();
                $q->clear();

            default:
        }
        return true;
    }
}