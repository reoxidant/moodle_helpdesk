<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ${PLUGINNAME} file description here.
 *
 * @package    ${PLUGINNAME}
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     vshapovalov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG -> dirroot . '/local/helpdesk/lib.php');
require_once($CFG -> dirroot . '/local/helpdesk/locallib.php');
require_once($CFG -> dirroot . '/local/helpdesk/forms/addcomment_form.php');

$context = context_system ::instance();

require_login();
require_capability('local/helpdesk:comment', $context);

$pluginname = get_string('pluginname', 'local_helpdesk');

$issue = $DB -> get_record('helpdesk_issue', ['id' => $issueid]);

if (!$issue) {
    print_error('errorbadissueid', 'local_helpdesk');
}

// Setting page.

$url = new moodle_url('/local/helpdesk/addcomment.php', ['issueid' => $issueid,]);
$PAGE -> set_url($url);
$PAGE -> set_context($context);
$PAGE -> set_pagelayout('standard');
$PAGE -> navbar -> add($pluginname);
$PAGE -> set_title($pluginname);
$PAGE -> set_heading($pluginname);

$form = new addcomment_form(new moodle_url('/'), ['issueid' => $issueid]);