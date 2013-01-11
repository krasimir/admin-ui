<?php

    class Resources {
        private $resources;
        public function get() {
            if(isset($this->resources)) {
                return $this->resources;
            }
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
                                $this->resources []= (object) array(
                                    "file" => $entry,
                                    "content" => $content
                                );
                            }
                        } catch(Exception $e) {
                            throw new Exception("Wrong json format in ".$entry);
                        }
                        
                    }
                }
                closedir($handle);
            }
            return $this->resources;
        }
        public function getByName($name) {
            $resources = $this->get();
            foreach($resources as $r) {
                if($r->content->name == $name) {
                    return $r;
                }
            }
            throw new Exception("Missing resource with name=".$name);die();
        }
    }

?>