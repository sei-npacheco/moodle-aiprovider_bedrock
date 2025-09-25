<?php
namespace aiprovider_bedrock\form;

use core_ai\form\action_settings_form;

class action_form extends action_settings_form {
    protected array $actionconfig;
    protected ?string $returnurl;
    protected string $actionname;
    protected string $action;
    protected int $providerid;
    protected string $providername;

    #[\Override]
    protected function definition(): void {
        $mform = $this->_form;
        $this->actionconfig = $this->_customdata['actionconfig']['settings'] ?? [];
        $this->returnurl    = $this->_customdata['returnurl'] ?? null;
        $this->actionname   = $this->_customdata['actionname'];
        $this->action       = $this->_customdata['action'];
        $this->providerid   = $this->_customdata['providerid'] ?? 0;
        $this->providername = $this->_customdata['providername'] ?? 'aiprovider_bedrock';

        $mform->addElement('header', 'generalsettingsheader', get_string('general', 'core'));
    }

    #[\Override]
    public function set_data($data): void {
        if (!empty($data['modelextraparams'])) {
            $data['modelextraparams'] = json_encode(json_decode($data['modelextraparams']), JSON_PRETTY_PRINT);
        }
        parent::set_data($data);
    }

    #[\Override]
    public function get_data(): ?\stdClass {
        $data = parent::get_data();
        if ($data) {
            $data = (object) array_filter((array) $data); // strip empty/falsey
        }
        return $data;
    }

    #[\Override]
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (array_key_exists('model', $data) && trim((string)($data['model'] ?? '')) === '') {
            $errors['model'] = get_string('required');
        }

        if (!empty($data['modelextraparams'])) {
            json_decode($data['modelextraparams']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['modelextraparams'] = get_string('invalidjson', 'aiprovider_bedrock');
            }
        }

        return $errors;
    }

    #[\Override]
    public function get_defaults(): array {
        $data = parent::get_defaults();
        unset($data['modelextraparams']);
        return $data;
    }
}
