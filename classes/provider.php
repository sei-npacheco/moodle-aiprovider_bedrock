<?php
namespace aiprovider_bedrock;

use core_ai\form\action_settings_form;
use core_ai\rate_limiter;
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
        // Bedrock uses the AWS SDK; don’t add HTTP headers here.
        return $request;
    }

    /**
     * Optional but recommended: enforce per-instance rate limits.
     */
    #[\Override]
    public function is_request_allowed(\core_ai\aiactions\base $action): array|bool {
        /** @var rate_limiter $ratelimiter */
        $ratelimiter = \core\di::get(rate_limiter::class);
        $component   = \core\component::get_component_from_classname(static::class);

        // Values saved on the instance (from your hook form).
        $enableUser   = !empty($this->config['enableuserratelimit']);
        $userLimit    = (int)($this->config['userratelimit'] ?? 0);
        $enableGlobal = !empty($this->config['enableglobalratelimit']);
        $globalLimit  = (int)($this->config['globalratelimit'] ?? 0);

        if ($enableUser && $userLimit > 0) {
            $ok = $ratelimiter->check_user_rate_limit(
                component: $component,
                ratelimit: $userLimit,
                userid: $action->get_configuration('userid')
            );
            if (!$ok) {
                return [
                    'success'     => false,
                    'errorcode'   => 429,
                    'errormessage'=> get_string('error:userratelimitexceeded', 'aiprovider_bedrock'),
                ];
            }
        }

        if ($enableGlobal && $globalLimit > 0) {
            $ok = $ratelimiter->check_global_rate_limit(
                component: $component,
                ratelimit: $globalLimit
            );
            if (!$ok) {
                return [
                    'success'     => false,
                    'errorcode'   => 429,
                    'errormessage'=> get_string('error:globalratelimitexceeded', 'aiprovider_bedrock'),
                ];
            }
        }

        return true;
    }

    /**
     * Mark the instance as configured only when creds + region are present.
     */
    #[\Override]
    public function is_provider_configured(): bool {
        return !empty($this->config['accesskeyid'])
            && !empty($this->config['secretaccesskey'])
            && !empty($this->config['region']);
    }
}