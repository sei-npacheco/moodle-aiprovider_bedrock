<?php
// This file is part of Moodle - https://moodle.org/
//
// GNU GPL v3 or later.

namespace aiprovider_bedrock;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use core_ai\process_base;

/**
 * Base class for Bedrock processors.
 *
 * Child classes must implement:
 *  - get_model(): string
 *  - create_request_params(string $userid): array
 *  - handle_api_success(array $response): array
 */
abstract class abstract_processor extends process_base {

    /**
     * Build an AWS Bedrock client using instance config (with legacy fallbacks).
     */
    protected function get_bedrock_client(): \Aws\BedrockRuntime\BedrockRuntimeClient {
        // 1) Prefer instance (provider) config
        $pconf = is_array($this->provider->config ?? null) ? $this->provider->config : [];

        $accesskey = $pconf['accesskeyid']      ?? null;
        $secretkey = $pconf['secretaccesskey']  ?? null;
        $region    = $pconf['region']           ?? null;

        // 2) Fallback to site-wide config ONLY if instance value is missing/empty
        if (!is_string($accesskey) || $accesskey === '') {
            $ak = get_config('aiprovider_bedrock', 'accesskeyid');
            $accesskey = (is_string($ak) && $ak !== '') ? $ak : null;
        }
        if (!is_string($secretkey) || $secretkey === '') {
            $sk = get_config('aiprovider_bedrock', 'secretaccesskey');
            $secretkey = (is_string($sk) && $sk !== '') ? $sk : null;
        }
        if (!is_string($region) || $region === '') {
            $rg = get_config('aiprovider_bedrock', 'region');
            $region = (is_string($rg) && $rg !== '') ? $rg : 'eu-west-1';
        }

        // 3) Final sanity: region must be a valid-looking AWS region string
        if (!preg_match('/^(us|eu|ap|sa|ca|me|af)-[a-z]+-\d$/', $region)) {
            // fallback rather than passing a bool/empty to SDK
            $region = 'eu-west-1';
        }

        return new \Aws\BedrockRuntime\BedrockRuntimeClient([
            'version'     => 'latest',
            'region'      => $region,                     // <-- guaranteed string now
            'credentials' => ['key' => (string)$accesskey, 'secret' => (string)$secretkey],
        ]);
    }

    /**
     * Children must return the full Bedrock modelId (e.g. "anthropic.claude-3-5-sonnet-20240620-v1:0").
     */
    abstract protected function get_model(): string;

    /**
     * Default system instruction (can be overridden or used by children).
     */
    protected function get_system_instruction(): string {
        return $this->action::get_system_instruction();
    }

    /**
     * Children must return the JSON body to send to Bedrock.
     */
    abstract protected function create_request_params(string $userid): array;

    /**
     * Children must map Bedrock JSON back to Moodle’s response schema.
     */
    abstract protected function handle_api_success(array $response): array;

    /**
     * Common Bedrock call wrapper (non-streaming).
     */
    protected function query_ai_api(): array {
        try {
            $userid = $this->provider->generate_userid($this->action->get_configuration('userid'));
            $client = $this->get_bedrock_client();
            $model  = $this->get_model();
            $body   = $this->create_request_params($userid);

            $result = $client->invokeModel([
                'modelId'     => $model,
                'contentType' => 'application/json',
                'accept'      => 'application/json',
                'body'        => json_encode($body, JSON_UNESCAPED_SLASHES),
            ]);

            // $result->get('body') is a stream; decode to array.
            $payload = json_decode($result->get('body')->getContents(), true) ?? [];

            return $this->handle_api_success($payload);

        } catch (\Throwable $e) {
            // Normalized error shape for Moodle AI actions.
            return [
                'success'      => false,
                'errorcode'    => (int) ($e->getCode() ?: 500),
                'errormessage' => $e->getMessage(),
            ];
        }
    }

    /**
     * Helper for uniform error responses if a child wants to short-circuit.
     */
    protected function handle_api_error(array $errorinfo): array {
        return [
            'success'      => false,
            'errorcode'    => (int)($errorinfo['code'] ?? 500),
            'errormessage' => (string)($errorinfo['message'] ?? get_string('error:unknownerror', 'aiprovider_bedrock')),
        ];
    }
}
