<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs this condition or there is error on login page

$ADMIN->add('root', new admin_category('helpdesk', get_string('helpdeskheadertext', 'local_helpdesk', null, true)));
$ADMIN->add('helpdesk', new admin_externalpage('helpdeskmodule', 'helpdesk module', $CFG->wwwroot.'/local/helpdesk/index.php'));

$settings = new admin_settingpage('local_helpdesk', 'helpdesk module');

$ADMIN->add('helpdesk', $settings);

$settings->add(new admin_setting_configtext('local_helpdesk/helpdeskheadertext',
    get_string('helpdeskheadertext', 'local_helpdesk', null, true),
    get_string('helpdeskheadertext', 'local_helpdesk', null, true),
    get_string('helpdeskheadertext', 'local_helpdesk', null, true),
    PARAM_TEXT));

$allRoles = get_all_roles();
foreach($allRoles as $lastRole) {
	$arrRoles[$lastRole->id] = $lastRole->shortname;
}
		
$setting = new admin_setting_configmultiselect('local_helpdesk/role', get_string('rolename', 'local_helpdesk', ''), '', array(), $arrRoles);
// Any setting that should cause the CSS to be recompiled should have this callback.
$setting->set_updatedcallback('theme_reset_all_caches');                                                    
// We are using tabs, so add this to page. If we were not using tabs this would be $settings->add($setting);
$settings->add($setting);


}

?>