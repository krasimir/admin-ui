<?php

    class Resources {

        private static $resources;
        private static $cache;

        public static function get() {
            if(isset(self::$resources)) {
                return self::$resources;
            }
            self::$resources = array();
            if ($handle = opendir(RESOURCE_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if($entry != "." && $entry != ".." && is_file(RESOURCE_DIR.$entry) && strpos($entry, ".json") > 0) {
                        $content = file_get_contents(RESOURCE_DIR.$entry);
                        try {
                            $content = json_decode($content);
                            if($content === null) {
                                throw new Exception("Wrong json format in ".$entry);
                            } else {
                                self::$resources []= (object) array(
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
            return self::$resources;
        }
        public static function getByName($name) {
            if(isset(self::$cache->$name)) {
                return self::$cache->$name;
            }
            $resources = self::get();
            foreach($resources as $r) {
                if($r->content->name == $name) {
                    if(!self::$cache) self::$cache = (object) array();
                    self::$cache->$name = $r;
                    return $r;
                }
            }
            throw new Exception("Missing resource with name=".$name);die();
        }
        public static function getByFilename($file) {
            if(isset(self::$cache->$file)) {
                return self::$cache->$file;
            }
            $resources = self::get();
            foreach($resources as $r) {
                if($r->file == $file) {
                    if(!self::$cache) self::$cache = (object) array();
                    self::$cache->$file = $r;
                    return $r;
                }
            }
            throw new Exception("Missing resource with filename=".$file);die();
        }
    }

?>