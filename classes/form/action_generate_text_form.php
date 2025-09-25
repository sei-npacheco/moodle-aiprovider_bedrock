<?php
namespace aiprovider_bedrock\form;

class action_generate_text_form extends action_form {
    #[\Override]
    protected function definition(): void {
        parent::definition();
        $mform = $this->_form;

        // Bedrock model id (text).
        $mform->addElement(
            'text',
            'model',
            get_string("action:{$this->actionname}:model", 'aiprovider_bedrock'),
            ['size' => 60]
        );
        $mform->setType('model', PARAM_TEXT);
        $mform->addRule('model', null, 'required', null, 'client');
        $mform->setDefault('model', $this->actionconfig['model'] ?? 'anthropic.claude-3-5-sonnet-20240620-v1:0');
        $mform->addHelpButton('model', "action:{$this->actionname}:model", 'aiprovider_bedrock');

        // Optional JSON extra parameters (temperature, top_p, max_tokens, etc.).
        $mform->addElement(
            'textarea',
            'modelextraparams',
            get_string('extraparams', 'aiprovider_bedrock'),
            ['rows' => 5, 'cols' => 60]
        );
        $mform->setType('modelextraparams', PARAM_TEXT);
        $mform->addElement('static', 'modelextraparams_help', null, get_string('extraparams_help', 'aiprovider_bedrock'));
        $mform->setDefault('modelextraparams', $this->actionconfig['modelextraparams'] ?? '');

        // System instruction.
        $mform->addElement(
            'textarea',
            'systeminstruction',
            get_string("action:{$this->actionname}:systeminstruction", 'aiprovider_bedrock'),
            'wrap="virtual" rows="5" cols="60"'
        );
        $mform->setType('systeminstruction', PARAM_TEXT);
        $mform->setDefault('systeminstruction',
            $this->actionconfig['systeminstruction'] ?? $this->action::get_system_instruction()
        );
        $mform->addHelpButton('systeminstruction', "action:{$this->actionname}:systeminstruction", 'aiprovider_bedrock');

        // Hidden meta fields.
        if ($this->returnurl) {
            $mform->addElement('hidden', 'returnurl', $this->returnurl);
            $mform->setType('returnurl', PARAM_LOCALURL);
        }
        $mform->addElement('hidden', 'action', $this->action);
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'provider', $this->providername);
        $mform->setType('provider', PARAM_TEXT);
        $mform->addElement('hidden', 'providerid', $this->providerid);
        $mform->setType('providerid', PARAM_INT);

        $this->set_data($this->actionconfig);
    }
}
