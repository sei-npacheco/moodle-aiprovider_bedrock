<?php
defined('MOODLE_INTERNAL') || die();

use core_ai\admin\admin_settingspage_provider;

if ($hassiteconfig) {
    $settings = new admin_settingspage_provider(
        'aiprovider_bedrock',
        new lang_string('pluginname', 'aiprovider_bedrock'),
        'moodle/site:config',
        true,
    );

    // Optional: a short notice. No global credentials in 5.0.
    $settings->add(new admin_setting_heading(
        'aiprovider_bedrock/general',
        new lang_string('settings', 'core'),
        get_string('settings_instance_note', 'aiprovider_bedrock')
    ));
}
