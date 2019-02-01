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

    if ($oldversion < 2018102300) {
        // Define table local_autogroup_manual to be created.
        $table = new xmldb_table('local_autogroup_manual');

        // Adding fields to table local_autogroup_manual.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_autogroup_manual.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_autogroup_manual.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Autogroup savepoint reached.
        upgrade_plugin_savepoint(true, 2018102300, 'local', 'autogroup');
    }

    if ($oldversion < 2019010300)  {
        require_once(__DIR__ . '/../classes/event_handler.php');

        $roleids = array_keys(get_all_roles());
        list($sql, $params) = $DB->get_in_or_equal($roleids, SQL_PARAMS_QM, 'param', false);
        $invalidroleids = $DB->get_fieldset_select('local_autogroup_roles', 'DISTINCT roleid', 'roleid '.$sql, $params);
        foreach ($invalidroleids as $roleid) {
            $event = \core\event\role_deleted::create(
                [
                    'context' => context_system::instance(),
                    'objectid' => $roleid,
                    'other' => [
                        'shortname' => 'invalidroletoremove'
                    ]
                ]
            );
            local_autogroup\event_handler::role_deleted($event);
        }
        upgrade_plugin_savepoint(true, 2019010300, 'local', 'autogroup');
    }

    return true;
}
