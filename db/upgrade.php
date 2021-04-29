<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

function xmldb_local_helpdesk_upgrade($oldversion = 0)
{
    global $CFG, $DB;

    $dbman = $DB -> get_manager();

    $result = true;

    if ($oldversion < 2021042904) {

        // Define table helpdesk_issue to be created.
        $table = new xmldb_table('helpdesk_issue');

        // Adding fields to table helpdesk_issue.
        $table -> add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table -> add_field('summary', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table -> add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table -> add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table -> add_field('datereported', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table -> add_field('reportedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table -> add_field('status', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table -> add_field('assignedto', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0');
        $table -> add_field('bywhomid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table -> add_field('priority', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table helpdesk_issue.
        $table -> add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for helpdesk_issue.
        if (!$dbman -> table_exists($table)) {
            $dbman -> create_table($table);
        }

        // Helpdesk savepoint reached.
        upgrade_plugin_savepoint(true, 2021042904, 'local', 'helpdesk');
    }

    return $result;
}