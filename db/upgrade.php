<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_autogroup_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016062201) {

        // Convert "Strict enforcement" settings to new toggles
        $pluginconfig = get_config('local_autogroup');
        if($pluginconfig->strict){
            set_config('listenforgroupchanges', true, 'local_autogroup');
            set_config('listenforgroupmembership', true, 'local_autogroup');
        }

        // savepoint reached.
        upgrade_plugin_savepoint(true, 2016062201, 'local', 'autogroup');
    }
    return true;
}