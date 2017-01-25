<?php

namespace Dxw\Whippet\Services;

// Responsible for calling an API and returning information about dxw inspections
// associated with a plugin
class InspectionsApi
{
    public function __construct(
        $host,
        \Dxw\Whippet\Services\JsonApi $json_api
    ) {
        $this->host = $host;
        $this->jsonApi = $json_api;
    }

    public function getInspections($plugin_slug)
    {
        $result = $this->jsonApi->get($this->url($plugin_slug));
        if ($result->isErr()) {
            return $result;
        }
        $inspections_data = $result->unwrap();
        if (!$this->validateInspections($inspections_data)) {
            return \Result\Result::err("Couldn't extract inspections from JSON response");
        }
        $inspections = $this->buildInspections($inspections_data);
        return \Result\Result::ok($inspections);
    }

    private function buildInspections($raw_inspections)
    {
        return array_map(function ($raw_inspection) {
            return new Inspection(
                $raw_inspection['date'],
                $raw_inspection['versions'],
                $raw_inspection['result'],
                $raw_inspection['url']
            );
        }, $raw_inspections);
    }

    private function validateInspections($raw_inspections)
    {
        if (!is_array($raw_inspections)) {
            return false;
        }

        foreach ($raw_inspections as $raw_inspection) {
            if (!$this->validateInspection($raw_inspection)) {
                return false;
            }
        }

        return true;
    }

    private function validateInspection($raw_inspection)
    {
        $keys = [
            'date',
            'versions',
            'result',
            'url',
        ];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $raw_inspection)) {
                return false;
            }
        }
        return true;
    }

    private function url($plugin_slug)
    {
        return $this->host.'/wp-json/v1/inspections/'.$plugin_slug;
    }
}

class Inspection
{
    public $date;
    public $versions;
    public $result;
    public $url;

    public function __construct($date_string, $versions, $result, $url)
    {
        $this->date = date_create($date_string);
        $this->versions = $versions;
        $this->result = $result;
        $this->url = $url;
    }
}
