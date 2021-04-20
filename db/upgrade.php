<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

function xmldb_local_helpdesk_upgrade($oldversion=0){
    global $CFG, $DB;

    $dbman = $DB -> get_manager();

    $result = true;

    if ($oldversion < 2021042000) {

        // Define table helpdesk to be created.
        $table = new xmldb_table('helpdesk');

        // Adding fields to table helpdesk.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table helpdesk.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for helpdesk.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Helpdesk savepoint reached.
        upgrade_plugin_savepoint(true, 2021042000, 'local', 'helpdesk');
    }


    return $result;
}