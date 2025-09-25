<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiprovider_bedrock;

/**
 * Class process text generation.
 *
 * @package    aiprovider_bedrock
 * @copyright  2025 Davide Ferro <dferro@meeplesrl.it>, Angelo Calò <acalo@meeplesrl.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_generate_text extends abstract_processor {
    /**
     * Get model ID from settings.
     *
     * @return string Amazon Bedrock model ID
     */
    // In process_generate_text.php
    protected function get_model(): string {
        // 1) Provider instance action settings (where the form saves it)
        $model = $this->provider->actionconfig[$this->action::class]['settings']['model'] ?? '';

        // 2) Fallback: some call sites hand settings via the action object
        if ($model === '') {
            $model = (string)($this->action->get_configuration('model') ?? '');
        }

        // 3) Legacy site-wide fallback (if you still keep it)
        if ($model === '') {
            $model = (string)(get_config('aiprovider_bedrock', 'action_generate_text_model') ?: '');
        }

        // Final guard: fail early with a clear message
        if ($model === '') {
            throw new \moodle_exception('Missing Bedrock model. Please set the “Model” in the Generate text action settings.');
        }

        return $model;
    }

    protected function get_system_instruction(): string {
        return (string)($this->action->get_configuration('systeminstruction')
            ?? get_config('aiprovider_bedrock', 'action_generate_text_systeminstruction')
            ?? $this->action::get_system_instruction());
    }

    private function get_prompt(): string {
        return (string)($this->action->get_configuration('prompttext')
            ?? $this->action->get_configuration('prompt')
            ?? '');
    }

    private function merge_extras(array $payload): array {
        $raw = $this->action->get_configuration('modelextraparams') ?? '';
        if (is_string($raw) && $raw !== '') {
            $extras = json_decode($raw, true);
            if (is_array($extras)) {
                $payload = array_replace($payload, $extras); // shallow override
            }
        }
        return $payload;
    }


    /**
     * Determine if the selected model is from Anthropic (Claude).
     *
     * @return bool
     */
    private function is_claude_model(): bool {
        $m = $this->get_model();
        return str_starts_with($m, 'anthropic.claude') || str_contains($m, '.anthropic.claude');
    }


    /**
     * Create the request parameters for Claude models.
     *
     * @param string $userid The user id.
     * @return array The request parameters.
     */
    private function create_claude_request(string $userid): array {
        $systeminstruction = $this->get_system_instruction();
        $prompttext = $this->get_prompt() ?: 'Please generate text based on this prompt.';

        $params = [
            'anthropic_version' => 'bedrock-2023-05-31',
            'max_tokens' => 1024,
            'temperature' => 0.7,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompttext],
                    ],
                ],
            ],
        ];
        if ($systeminstruction !== '') {
            $params['system'] = $systeminstruction;
        }
        return $this->merge_extras($params);
    }

    private function create_other_model_request(string $userid): array {
        $model = $this->get_model();
        $prompttext = $this->get_prompt();
        $systeminstruction = $this->get_system_instruction();

        $params = [
            'prompt' => $prompttext,
            'max_tokens' => 1024,
            'temperature' => 0.7,
        ];

        if ($systeminstruction !== '') {
            if (str_starts_with($model, 'meta.llama')) {
                $params['system'] = $systeminstruction;
            } else if (str_starts_with($model, 'amazon.titan')) {
                $params['systemPrompt'] = $systeminstruction;
            } else {
                $params['system_prompt'] = $systeminstruction;
            }
        }
        return $this->merge_extras($params);
    }


    /**
     * Create request parameters.
     *
     * @param string $userid The user ID.
     * @return array The request parameters.
     */
    protected function create_request_params(string $userid): array {
        if ($this->is_claude_model()) {
            return $this->create_claude_request($userid);
        } else {
            return $this->create_other_model_request($userid);
        }
    }

    /**
     * Handle a successful response from Claude models.
     *
     * @param array $response The response data.
     * @return array The processed response.
     */
    private function handle_claude_success(array $response): array {
        // Map Claude's stop reasons to standard format.
        $stopreason = $response['stop_reason'] ?? 'stop';
        if ($stopreason === 'end_turn') {
            $stopreason = 'stop';
        } else if ($stopreason === 'max_tokens') {
            $stopreason = 'length';
        }

        return [
            'success' => true,
            'id' => $response['id'] ?? uniqid('bedrock_'),
            'fingerprint' => $stopreason,
            'generatedcontent' => $response['content'][0]['text'] ?? '',
            'finishreason' => $stopreason,
            'prompttokens' => $response['usage']['input_tokens'] ?? 0,
            'completiontokens' => $response['usage']['output_tokens'] ?? 0,
        ];
    }

    /**
     * Handle a successful response from other models.
     *
     * @param array $response The response data.
     * @return array The processed response.
     */
    private function handle_other_model_success(array $response): array {
        // Extract content based on model.
        $content = '';
        if (isset($response['generation'])) {
            $content = $response['generation'];
        } else if (isset($response['output'])) {
            $content = $response['output'];
        } else if (isset($response['completions'][0]['data']['text'])) {
            $content = $response['completions'][0]['data']['text'];
        } else if (isset($response['text'])) {
            $content = $response['text'];
        } else if (isset($response['completion'])) {
            $content = $response['completion'];
        } else {
            // Try to find content in the response.
            $content = json_encode($response);
        }

        // Standardize finish reason to values Moodle expects.
        $finishreason = $response['finish_reason'] ?? $response['stopReason'] ?? 'stop';

        // Map various stop/finish reasons to standardized values.
        if (in_array($finishreason, ['end_turn', 'stop_sequence', 'complete', 'finished'])) {
            $finishreason = 'stop';
        } else if (in_array($finishreason, ['max_tokens', 'token_limit', 'length_exceeded'])) {
            $finishreason = 'length';
        }

        return [
            'success' => true,
            'id' => $response['id'] ?? uniqid('bedrock_'),
            'fingerprint' => $response['model'] ?? $this->get_model(),
            'generatedcontent' => $content,
            'finishreason' => $finishreason,
            'prompttokens' => $response['usage']['prompt_tokens'] ?? $response['usage']['inputTokens'] ?? 0,
            'completiontokens' => $response['usage']['completion_tokens'] ?? $response['usage']['outputTokens'] ?? 0,
        ];
    }

    /**
     * Handle API success
     *
     * @param array $response API Response
     * @return array Text generation response
     */
    protected function handle_api_success(array $response): array {
        if ($this->is_claude_model()) {
            return $this->handle_claude_success($response);
        } else {
            return $this->handle_other_model_success($response);
        }
    }
}
