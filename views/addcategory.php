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
 * $addcategory file description here.
 *
 * @package    $addcategory
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     vshapovalov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG -> dirroot . '/local/helpdesk/forms/addcategory_form.php');

$url = new moodle_url('/local/helpdesk/addcategory.php');

$form = new addcategory_form($url);

if ($form -> is_cancelled()) {
    $params = compact('view', 'screen');
    redirect(new moodle_url('/local/helpdesk/view.php', $params));
}

$data = $form -> get_data();

if ($data) {
    $category = new StdClass;
    $category -> name = $data -> name;
    $category -> description = $data -> description;
    if ($data -> categoryid) {
        $category -> id = $data -> categoryid;
        $DB -> update_record('helpdesk_categories', $category);
    } else {
        $category -> id = $DB -> insert_record('helpdesk_categories', $category);
    }

    $params = compact('view', 'screen');
    redirect(new moodle_url('/local/helpdesk/view.php'));
}

$form -> set_data($data);
$form -> display();