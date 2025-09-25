<?php
namespace aiprovider_bedrock;

use core_ai\form\action_settings_form;
use Psr\Http\Message\RequestInterface;

class provider extends \core_ai\provider {

    #[\Override]
    public static function get_action_list(): array {
        return [
            \core_ai\aiactions\generate_text::class,
            \core_ai\aiactions\summarise_text::class,
            \core_ai\aiactions\explain_text::class,
            \core_ai\aiactions\generate_image::class,
        ];
    }

    #[\Override]
    public static function get_action_settings(
        string $action,
        array $customdata = [],
    ): action_settings_form|bool {
        $actionname = substr($action, (strrpos($action, '\\') + 1));
        $customdata['actionname']   = $actionname;
        $customdata['action']       = $action;
        $customdata['providername'] = 'aiprovider_bedrock';

        if (in_array($actionname, ['generate_text', 'summarise_text', 'explain_text'], true)) {
            return new form\action_generate_text_form(customdata: $customdata);
        }

        if ($actionname === 'generate_image' && class_exists(form\action_generate_image_form::class)) {
            return new form\action_generate_image_form(customdata: $customdata);
        }

        return false;
    }

    #[\Override]
    public static function get_action_setting_defaults(string $action): array {
        $actionname = substr($action, (strrpos($action, '\\') + 1));
        $cd = [
            'actionname'   => $actionname,
            'action'       => $action,
            'providername' => 'aiprovider_bedrock',
        ];

        if (in_array($actionname, ['generate_text', 'summarise_text', 'explain_text'], true)) {
            $mform = new form\action_generate_text_form(customdata: $cd);
            return $mform->get_defaults();
        }

        if ($actionname === 'generate_image' && class_exists(form\action_generate_image_form::class)) {
            $mform = new form\action_generate_image_form(customdata: $cd);
            return $mform->get_defaults();
        }

        return [];
    }

    #[\Override]
    public function add_authentication_headers(RequestInterface $request): RequestInterface {
        // Bedrock auth handled by AWS SDK you use in processors.
        return $request;
    }

    #[\Override]
    public function is_provider_configured(): bool {
        // Instance config will be validated in processors; return true here.
        return true;
    }
}
