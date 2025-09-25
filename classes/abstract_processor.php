<?php
namespace aiprovider_bedrock;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use core_ai\process_base;

/**
 * Abstract class for Bedrock processors (instance-aware).
 *
 * @package    aiprovider_bedrock
 */
abstract class abstract_processor extends process_base {

    /**
     * Build an AWS Bedrock client using instance (action) configuration,
     * with safe fallbacks to legacy plugin config and env vars.
     */
    protected function get_bedrock_client(): BedrockRuntimeClient {
        // Instance config (preferred).
        $accesskey = $this->action->get_configuration('accesskeyid');
        $secretkey = $this->action->get_configuration('secretaccesskey');
        $session   = $this->action->get_configuration('sessiontoken'); // optional
        $region    = $this->action->get_configuration('region');

        // Fallbacks (legacy globals or environment) to ease upgrades.
        $accesskey ??= get_config('aiprovider_bedrock', 'accesskeyid') ?: getenv('AWS_ACCESS_KEY_ID');
        $secretkey ??= get_config('aiprovider_bedrock', 'secretaccesskey') ?: getenv('AWS_SECRET_ACCESS_KEY');
        $session   ??= get_config('aiprovider_bedrock', 'sessiontoken') ?: getenv('AWS_SESSION_TOKEN') ?: null;
        $region    ??= get_config('aiprovider_bedrock', 'region') ?: getenv('AWS_REGION') ?: 'eu-west-1';

        $config = [
            'version'     => 'latest',
            'region'      => $region,
            'credentials' => [
                'key'    => $accesskey,
                'secret' => $secretkey,
            ],
        ];

        if (!empty($session)) {
            $config['credentials']['token'] = $session;
        }

        return new BedrockRuntimeClient($config);
    }

    /**
     * Model ID to invoke (defaults to the text field from the action form).
     * Override in concrete processors if you need a computed model id.
     */
    protected function get_model(): string {
        $model = (string) ($this->action->get_configuration('model') ?? '');
        if ($model === '') {
            // Guardrail: fail fast with a readable error.
            throw new \coding_exception('Bedrock model is not configured for this action instance.');
        }
        return $model;
    }

    /**
     * Optional system instruction (uses the action’s default if not set in instance).
     */
    protected function get_system_instruction(): string {
        return (string) ($this->action->get_configuration('systeminstruction')
            ?? $this->action::get_system_instruction());
    }

    /**
     * If your processors want the extra JSON settings (temperature, top_p, etc.)
     * this helper returns them as an assoc array.
     */
    protected function get_extra_params(): array {
        $raw = $this->action->get_configuration('modelextraparams') ?? '';
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }

    /**
     * Each concrete processor must create the provider-specific request body.
     * Compose it using get_system_instruction(), get_extra_params(), and your inputs.
     *
     * @param string $userid Privacy-safe hashed user id.
     * @return array Request body that Bedrock model expects.
     */
    abstract protected function create_request_params(string $userid): array;

    /**
     * Convert a successful Bedrock response to Moodle’s standard action payload.
     */
    abstract protected function handle_api_success(array $response): array;

    /**
     * Call Bedrock and return a uniform result array.
     */
    protected function query_ai_api(): array {
        try {
            $userid  = $this->provider->generate_userid($this->action->get_configuration('userid'));
            $client  = $this->get_bedrock_client();
            $model   = $this->get_model();
            $payload = $this->create_request_params($userid);

            // Allow instance JSON to override/add knobs.
            $extras  = $this->get_extra_params();
            if ($extras) {
                // Shallow merge; override payload keys with extras if present.
                $payload = array_replace($payload, $extras);
            }

            $result = $client->invokeModel([
                'modelId'     => $model,
                'body'        => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'contentType' => 'application/json',
                'accept'      => 'application/json',
            ]);

            // Bedrock returns a stream body object.
            $body = (string) $result->get('body');
            $data = $body !== '' ? json_decode($body, true) : [];
            if (!is_array($data)) {
                throw new \runtime_exception('Invalid JSON received from Bedrock.');
            }

            return $this->handle_api_success($data);

        } catch (AwsException $e) {
            return [
                'success'      => false,
                'errorcode'    => (int) ($e->getStatusCode() ?: 500),
                'errormessage' => $e->getAwsErrorMessage() ?: $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'success'      => false,
                'errorcode'    => (int) ($e->getCode() ?: 500),
                'errormessage' => $e->getMessage(),
            ];
        }
    }

    /**
     * Standard error wrapper (kept for compatibility with callers).
     */
    protected function handle_api_error(array $errorinfo): array {
        return [
            'success'      => false,
            'errorcode'    => (int) ($errorinfo['code'] ?? 500),
            'errormessage' => (string) ($errorinfo['message'] ?? get_string('error:unknownerror', 'aiprovider_bedrock')),
        ];
    }
}
