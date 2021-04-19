<?php
/**
 * Description actions
 * @copyright 2021 vshapovalov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package PhpStorm
 */

// File contains a Mail templates

if (!function_exists('compile_mail_template')) {

    /**
     * @param $template
     * @param $infomap
     * @param $module
     * @param string $lang
     * @return array|string|string[]
     */
    function helpdesk_compile_mail_template($template, $infomap, $module, $lang = '')
    {
        global $USER;

        if (empty($lang)) $lang = $USER -> lang;
        $lang = substr($lang, 0, 2);

        $notification = implode("", helpdesk_get_mail_template($template, $module, $lang));
        foreach ($infomap as $aKey => $aValue) {
            $notification = str_replace("<%%$aKey%%>", $aValue, $notification);
        }
        return $notification;
    }
}

if (!function_exists('get_mail_template')) {
    /**
     * @param $virtual
     * @param $modulename
     * @param string $lang
     * @return array|false
     */
    function helpdesk_get_mail_template($virtual, $modulename, $lang = "")
    {
        global $CFG;

        if ($lang == "") {
            $lang = $CGF -> lang;
        }

        if (preg_match("/^auth_/", $modulename)) {
            $location = 'auth';
            $modulename = str_replace("auth_", "", $modulename);
        } elseif (preg_match("/^enrol_/", $modulename)) {
            $location = 'enrol';
            $modulename = str_replace("block_", "", $modulename);
        } elseif (preg_match("/^block_/", $modulename)) {
            $location = "blocks";
            $modulename = str_replace("block_", "", $modulename);
        } else {
            $location = "mod";
        }

        $templateName = "{$CFG->dirroot}/{$location}/{$modulename}/mails/{$lang}/{$virtual}.tpl";

        if (file_exists($templateName)) {
            return file($templateName);
        }

        debugging("template $templateName not found");
        return array();
    }
}