<?php
defined('MOODLE_INTERNAL') || die();

return [
    [
        'hook'     => \core_ai\hook\after_ai_provider_form_hook::class,
        'callback' => [\aiprovider_bedrock\hook_listener::class, 'set_form_definition_for_aiprovider_bedrock'],
    ],
    [
        'hook'     => \core_ai\hook\after_ai_action_settings_form_hook::class,
        'callback' => [\aiprovider_bedrock\hook_listener::class, 'set_model_form_definition_for_aiprovider_bedrock'],
    ],
];
