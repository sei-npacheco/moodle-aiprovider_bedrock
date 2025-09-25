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

use core_ai\hook\after_ai_provider_form_hook;

/**
 * Hook listener for Azure AI provider.
 *
 * @package    aiprovider_azureai
 * @copyright  2024 Matt Porritt <matt.porritt@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {

    /**
     * Hook listener for the Azure AI instance setup form.
     *
     * @param after_ai_provider_form_hook $hook The hook to add to the AI instance setup.
     */
    public static function set_form_definition_for_aiprovider_bedrock(after_ai_provider_form_hook $hook): void {
        if ($hook->plugin !== 'aiprovider_bedrock') {
            return;
        }

        $mform = $hook->mform;

        // // --- Credentials ---
        $mform->addElement('passwordunmask', 'accesskeyid', get_string('accesskeyid', 'aiprovider_bedrock'), ['size' => 75]);
        $mform->setType('accesskeyid', PARAM_TEXT);
        $mform->addHelpButton('accesskeyid', 'accesskeyid', 'aiprovider_bedrock');

        $mform->addElement('passwordunmask', 'secretaccesskey', get_string('secretaccesskey', 'aiprovider_bedrock'), ['size' => 75]);
        $mform->setType('secretaccesskey', PARAM_TEXT);
        $mform->addHelpButton('secretaccesskey', 'secretaccesskey', 'aiprovider_bedrock');

        // --- Region ---
        $mform->addElement('text', 'region', get_string('region', 'aiprovider_bedrock'), ['size' => 20]);
        $mform->setType('region', PARAM_TEXT);
        $mform->addHelpButton('region', 'region', 'aiprovider_bedrock');

        // If the site-wide setting exists and *looks* like an AWS region, use it;
        // otherwise fall back to eu-west-1.
        $sitecfg = get_config('aiprovider_bedrock', 'region');
        $validaws = is_string($sitecfg) && preg_match('/^[a-z]{2}-[a-z]+-\d$/', $sitecfg);
        $mform->setDefault('region', $validaws ? $sitecfg : 'eu-west-1');

    }
}