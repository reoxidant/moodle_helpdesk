<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */


$capabilities = array(
    'local/helpdesk:view_all_issues' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:report' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:develop' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

);