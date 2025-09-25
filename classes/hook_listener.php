<?php
namespace aiprovider_bedrock;

use core_ai\hook\after_ai_action_settings_form_hook;
use core_ai\hook\after_ai_provider_form_hook;

class hook_listener {

    public static function set_form_definition_for_aiprovider_bedrock(after_ai_provider_form_hook $hook): void {
        $component = $hook->plugin ?? $hook->component ?? '';
        // if ($component !== 'aiprovider_bedrock') {
        //     return;
        // }
        $mform = $hook->mform;

        $mform->addElement('passwordunmask', 'accesskeyid', get_string('accesskeyid', 'aiprovider_bedrock'));
        $mform->setType('accesskeyid', PARAM_TEXT);
        $mform->addHelpButton('accesskeyid', 'accesskeyid', 'aiprovider_bedrock');

        $mform->addElement('passwordunmask', 'secretaccesskey', get_string('secretaccesskey', 'aiprovider_bedrock'));
        $mform->setType('secretaccesskey', PARAM_TEXT);
        $mform->addHelpButton('secretaccesskey', 'secretaccesskey', 'aiprovider_bedrock');

        $mform->addElement('text', 'region', get_string('region', 'aiprovider_bedrock'), ['size' => 20]);
        $mform->setType('region', PARAM_TEXT);
        $mform->addHelpButton('region', 'region', 'aiprovider_bedrock');
        $mform->addRule('region', get_string('required'), 'required', null, 'client');
        $mform->setDefault('region', 'eu-west-1');

        // Optional STS token:
        if (get_config('aiprovider_bedrock', 'enable_session_token_field')) {
            $mform->addElement('passwordunmask', 'sessiontoken', get_string('sessiontoken', 'aiprovider_bedrock'));
            $mform->setType('sessiontoken', PARAM_TEXT);
            $mform->addHelpButton('sessiontoken', 'sessiontoken', 'aiprovider_bedrock');
        }
    }

    public static function set_model_form_definition_for_aiprovider_bedrock(after_ai_action_settings_form_hook $hook): void {
        $component = $hook->plugin ?? $hook->component ?? '';
        if ($component !== 'aiprovider_bedrock') {
            return;
        }
        // no-op (your action form already has a text 'model' field).
    }
}
