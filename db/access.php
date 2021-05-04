<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

$capabilities = array(

    'local/helpdesk:viewallissues' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:manage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:managepriority' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:viewpriority' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:resolve' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetype' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:report' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetype' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'local/helpdesk:configure' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetype' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        )
    )
);