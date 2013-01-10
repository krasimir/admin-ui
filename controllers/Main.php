<?php

    class Main {

        private $resources = array();

        public function __construct() {

            $this->getResources();
            var_dump($this->resources);die();

            die(view("layout.html", array(
                "content" => view("home.html", array(
                    
                )),
                "nav" => view("nav.html")
            )));
        }
        private function getResources() {
            $this->resources = array();
            $resourcesDir = __DIR__."/../resources/";
            if ($handle = opendir($resourcesDir)) {
                while (false !== ($entry = readdir($handle))) {
                    if($entry != "." && $entry != ".." && is_file($resourcesDir.$entry) && strpos($entry, ".json") > 0) {
                        $content = file_get_contents($resourcesDir.$entry);
                        try {
                            $content = json_decode($content);
                            if($content === null) {
                                throw new Exception("Wrong json format in ".$entry);
                            } else {
                                $this->resources []= $content;
                            }
                        } catch(Exception $e) {
                            throw new Exception("Wrong json format in ".$entry);
                        }
                        
                    }
                }
                closedir($handle);
            }
        }
    }

?>