<?php
namespace aiprovider_bedrock;

use core_ai\hook\after_ai_provider_form_hook;
use core_ai\hook\after_ai_action_settings_form_hook;

class hook_listener {
    public static function set_form_definition_for_aiprovider_bedrock(after_ai_provider_form_hook $hook): void {
        // TEMP: no guard; show a big header so we know this ran.
        $mform = $hook->mform;
        $mform->addElement('header', 'bedrock_debug_header', '*** Bedrock hook is firing ***');

        $mform->addElement('passwordunmask', 'accesskeyid', 'Access key ID');
        $mform->addElement('passwordunmask', 'secretaccesskey', 'Secret access key');
        $mform->addElement('text', 'region', 'Region', ['size' => 20]);
        $mform->setDefault('region', 'eu-west-1');
    }

    public static function set_model_form_definition_for_aiprovider_bedrock(after_ai_action_settings_form_hook $hook): void {
        // no-op
    }
}
