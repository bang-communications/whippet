<?php

namespace Dxw\Whippet\Files;

class WhippetJson extends Base
{
    public function getDependencies(/* string */ $type)
    {
        if (isset($this->data[$type])) {
            return $this->data[$type];
        } else {
            return [];
        }
    }

    public function getSources()
    {
        return $this->data['src'];
    }

    public function getApiHost()
    {
        if (array_key_exists('inspections_api', $this->data) && array_key_exists('host', $this->data['inspections_api'])) {
            return $this->data['inspections_api']['host'];
        } else {
            return 'https://security.dxw.com';
        }
    }
}
