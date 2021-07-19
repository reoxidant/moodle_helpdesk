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
 * $assignmanagers file description here.
 *
 * @package    $assignmanagers
 * @copyright  2021 SysBind Ltd. <service@sysbind.co.il>
 * @auther     vshapovalov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

$action = helpdesk_categories_param_action();

echo '<form id="categoryeditform" action="index.php" method="post">
        <div>
            <table style="padding: 6px" class="generaltable generalbox categorymanagementtable boxaligncenter">
                <tr>
                    <td>
                        <p>
                            <label for="categories">
                                <span id="categorieslabel">' . get_string('categories', 'local_helpdesk') . '</span>
                                <span id="thecategorizing">&nbsp;</span>
                            </label>
                        </p>
                        <select name="categories[]" multiple="multiple" id="categories" size="15" class="select" onchange="M.core_group.membersCombo.refreshMembers()"></select>
                    </td>
                </tr>
            </table>
        </div>
      </form>';

