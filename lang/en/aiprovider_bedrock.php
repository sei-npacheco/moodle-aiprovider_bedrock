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

/**
 * Strings for component aiprovider_bedrock, language 'en'.
 *
 * @package    aiprovider_bedrock
 * @copyright  2025 Davide Ferro <dferro@meeplesrl.it>, Angelo Calò <acalo@meeplesrl.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accesskeyid'] = 'AWS Access Key ID';
$string['accesskeyid_desc'] = 'The AWS Access Key ID used to authenticate with Amazon Bedrock.';
$string['action:generate_image:model'] = 'AI model';
$string['action:generate_image:model_desc'] = 'The model used to generate images from user prompts.';
$string['action:generate_text:model'] = 'AI model';
$string['action:generate_text:model_desc'] = 'The model used to generate the text response. Cross-region inference models require "us." or "eu." at the begininning.';
$string['action:generate_text:systeminstruction'] = 'System instruction';
$string['action:generate_text:systeminstruction_desc'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['action:summarise_text:model'] = 'AI model';
$string['action:summarise_text:model_desc'] = 'The model used to summarise the provided text.';
$string['action:summarise_text:systeminstruction'] = 'System instruction';
$string['action:summarise_text:systeminstruction_desc'] = 'This instruction is sent to the AI model along with the user\'s prompt. Editing this instruction is not recommended unless absolutely required.';
$string['enableglobalratelimit'] = 'Set site-wide rate limit';
$string['enableglobalratelimit_desc'] = 'Limit the number of requests that the Amazon Bedrock API provider can receive across the entire site every hour.';
$string['enableuserratelimit'] = 'Set user rate limit';
$string['enableuserratelimit_desc'] = 'Limit the number of requests each user can make to the Amazon Bedrock API provider every hour.';
$string['error:failedprocessimage'] = 'Failed to process image: {$a}';
$string['error:globalratelimitexceeded'] = 'Global rate limit exceeded';
$string['error:noimagedata'] = 'No image data found in the response';
$string['error:unknownerror'] = 'Unknown error';
$string['error:userratelimitexceeded'] = 'User rate limit exceeded';
$string['globalratelimit'] = 'Maximum number of site-wide requests';
$string['globalratelimit_desc'] = 'The number of site-wide requests allowed per hour.';
$string['pluginname'] = 'Amazon Bedrock API provider';
$string['privacy:metadata'] = 'The Amazon Bedrock API provider plugin does not store any personal data.';
$string['privacy:metadata:aiprovider_bedrock:externalpurpose'] = 'This information is sent to the Amazon Bedrock API in order for a response to be generated. Your AWS account settings may change how Amazon stores and retains this data. No user data is explicitly sent to Amazon or stored in Moodle LMS by this plugin.';
$string['privacy:metadata:aiprovider_bedrock:model'] = 'The model used to generate the response.';
$string['privacy:metadata:aiprovider_bedrock:numberimages'] = 'When generating images the number of images used in the response.';
$string['privacy:metadata:aiprovider_bedrock:prompttext'] = 'The user entered text prompt used to generate the response.';
$string['privacy:metadata:aiprovider_bedrock:responseformat'] = 'When generating images the format of the response.';
$string['region'] = 'AWS Region';
$string['region_desc'] = 'The AWS Region where Amazon Bedrock service is available (e.g., eu-west-1, us-east-1, us-west-2).';
$string['secretaccesskey'] = 'AWS Secret Access Key';
$string['secretaccesskey_desc'] = 'The AWS Secret Access Key used to authenticate with Amazon Bedrock.';
$string['userratelimit'] = 'Maximum number of requests per user';
$string['userratelimit_desc'] = 'The number of requests allowed per hour, per user.';
// General.
$string['pluginname'] = 'Amazon Bedrock';
$string['settings_instance_note'] =
  'From Moodle 5.0, credentials are configured per provider instance at Site administration → General → AI providers.';

// Instance form.
$string['accesskeyid'] = 'Access key ID';
$string['accesskeyid_desc'] = 'AWS access key ID for this Bedrock instance.';
$string['secretaccesskey'] = 'Secret access key';
$string['secretaccesskey_desc'] = 'AWS secret access key for this Bedrock instance.';
$string['sessiontoken'] = 'Session token';
$string['sessiontoken_help'] = 'Optional session token for temporary credentials.';
$string['region'] = 'Region';
$string['region_desc'] = 'AWS region hosting Bedrock, e.g. eu-west-1.';

// Actions: model + systeminstruction (we reuse same strings for 3 text actions).
$string['action:generate_text:model'] = 'Model';
$string['action:generate_text:model_help'] =
  'Enter the exact Bedrock model ID (e.g. <code>anthropic.claude-3-5-sonnet-20240620-v1:0</code>).';
$string['action:summarise_text:model'] = $string['action:generate_text:model'];
$string['action:summarise_text:model_help'] = $string['action:generate_text:model_help'];
$string['action:explain_text:model'] = $string['action:generate_text:model'];
$string['action:explain_text:model_help'] = $string['action:generate_text:model_help'];

$string['action:generate_text:systeminstruction'] = 'System instruction';
$string['action:generate_text:systeminstruction_help'] =
  'Optional instruction prepended to steer tone and style.';
$string['action:summarise_text:systeminstruction'] = $string['action:generate_text:systeminstruction'];
$string['action:summarise_text:systeminstruction_help'] = $string['action:generate_text:systeminstruction_help'];
$string['action:explain_text:systeminstruction'] = $string['action:generate_text:systeminstruction'];
$string['action:explain_text:systeminstruction_help'] = $string['action:generate_text:systeminstruction_help'];

// Extra params (JSON).
$string['extraparams'] = 'Extra model parameters (JSON)';
$string['extraparams_help'] =
  'Optional JSON for provider-specific parameters (e.g. {"temperature":0.2,"top_p":0.9,"max_tokens":2048}).';
$string['invalidjson'] = 'This is not valid JSON.';

$string['accesskeyid_help'] =
    'Your AWS Access Key ID used to authenticate requests to Amazon Bedrock.';
$string['secretaccesskey_help'] =
    'Your AWS Secret Access Key paired with the Access Key ID.';
$string['sessiontoken_help'] =
    'Optional temporary session token (STS) if you use temporary credentials.';
$string['region_help'] =
    'AWS region where you will call Bedrock (e.g. us-east-1, us-west-2, eu-west-1).';

$string['enableglobalratelimit_help'] =
    'When enabled, limits the total number of requests per hour across all users for this provider instance.';
$string['globalratelimit_help'] =
    'Maximum total requests per hour across all users for this provider instance.';
$string['enableuserratelimit_help'] =
    'When enabled, limits the number of requests per hour per user.';
$string['userratelimit_help'] =
    'Maximum requests per hour per user for this provider instance.';
