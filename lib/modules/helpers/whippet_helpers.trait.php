<?php

trait whippet_helpers {
  function check_and_create_dir($dir, $force_empty = false) {
    if(!file_exists($dir)) {
      if(!mkdir($dir)) {
        throw new Exception("Unable to create directory: {$dir}");
      }
    } else if($force_empty) {
      if((new \FilesystemIterator($dir))->valid()) {
        throw new Exception("Directory exists but is not empty: {$dir}");
      }
    }
  }

  function whippet_init(){
    if(!$this->plugins_manifest_file = $this->find_file('Plugins')) {
      echo "Unable to find Plugins file";
      exit(1);
    }

    $this->plugins_lock_file = $this->find_file("plugins.lock");

    $this->project_dir = dirname($this->plugins_manifest_file);
    $this->plugin_dir = "{$this->project_dir}/wp-content/plugins";


    $this->load_application_config();
  }

  function load_application_config() {
    $application_config_file = "{$this->project_dir}/config/application.json";

    if(file_exists($application_config_file)) {
      $this->application_config = json_decode(file_get_contents($application_config_file));

      if(!is_object($this->application_config)) {
        echo "Unable to parse application config";
        exit(1);
      }
    }
    else {
      $this->application_config = json_decode('
        {
          "wordpress": {
            "repository": "git@git.dxw.net:wordpress/snapshot",
            "revision": "master"
          }
        }
      ');

      if(file_put_contents($application_config_file, json_encode($this->application_config,  JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        echo "A default application.json was created\n";
      }
      else {
        echo "No config/application.json was found, and no default could be created. Quitting.\n";
        exit(1);
      }
    }
  }

  function find_file($file){
    // Starting in the current dir, walk up until we find a plugins.json
    $path = getcwd();

    do {
      $file_path = $path . '/' . $file;
      if(file_exists($file_path)) {
        return $file_path;
      }
    }
    while($path = dirname($path) != '.');

    return false;
  }
};