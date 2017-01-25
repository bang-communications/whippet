<?php

namespace Dxw\Whippet\Services;

// Thin wrapper around file access functions.
// Responsible for fetching and parsing json files
class FileFetcher
{
    public function __construct($dir)
    {
        $this->directory = $dir;
    }

    public function fetch($path)
    {
        $full_path = $this->directory.$path;
        if (!is_file($full_path)) {
            return \Result\Result::err('file not found '.$full_path);
        }

        $json = file_get_contents($full_path);

        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return \Result\Result::err('invalid JSON');
        }
        return \Result\Result::ok($data);
    }
}

class WhippetJsonFetcher
{
    public function __construct($file_fetcher)
    {
        $this->file_fetcher = $file_fetcher;
        $this->path = '/whippet.json';
    }

    public function fetch()
    {
        $result = $this->file_fetcher->fetch($this->path);
        if (!$result->isErr()) {
            $whippet_json = new \Dxw\Whippet\Files\WhippetJson($result->unwrap());
            return \Result\Result::ok($whippet_json);
        } else {
            return($result);
        }
    }
}
