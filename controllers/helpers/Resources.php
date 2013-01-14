<?php

    class Resources {
        private $resources;
        private $cache;
        public function get() {
            if(isset($this->resources)) {
                return $this->resources;
            }
            $this->resources = array();
            if ($handle = opendir(RESOURCE_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if($entry != "." && $entry != ".." && is_file(RESOURCE_DIR.$entry) && strpos($entry, ".json") > 0) {
                        $content = file_get_contents(RESOURCE_DIR.$entry);
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
            if(isset($this->cache->$name)) {
                return $this->cache->$name;
            }
            $resources = $this->get();
            foreach($resources as $r) {
                if($r->content->name == $name) {
                    if(!$this->cache) $this->cache = (object) array();
                    $this->cache->$name = $r;
                    return $r;
                }
            }
            throw new Exception("Missing resource with name=".$name);die();
        }
        public function getByFilename($file) {
            if(isset($this->cache->$file)) {
                return $this->cache->$file;
            }
            $resources = $this->get();
            foreach($resources as $r) {
                if($r->file == $file) {
                    if(!$this->cache) $this->cache = (object) array();
                    $this->cache->$file = $r;
                    return $r;
                }
            }
            throw new Exception("Missing resource with filename=".$file);die();
        }
    }

?>