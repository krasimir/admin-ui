<?php

    use flight\net\Response;

    class Action {
        public $response;
        public $mysql;
        public $resource;
        public function __construct($resource = null) {
            global $mysql;
            $this->response = new Response();
            $this->mysql = $mysql;
            $this->resource = $resource;
        }
        public function defineContext() {
            $fields = array();
            foreach($this->resource->data as $item) {
                $fields[$item->name] = $item->type;
            }
            $this->mysql->defineContext($this->resource->name, $fields);
        }
        public function defineForm($editMode = false) {
            $this->form = Former::register("resource-".$this->resource->name, ADMINUI_URL."resources/".$this->resource->name."/add");
            $fields = array();
            foreach($this->resource->data as $item) {
                $validation = null;
                if(isset($item->validation)) {
                    $validation = Former::validation();
                    $validationStr = str_replace(" ", "", $item->validation);
                    $validationParts = explode(",", $validationStr);
                    foreach($validationParts as $validationMethod) {
                        $validationMethod = explode("/", $validationMethod);
                        if(count($validationMethod) == 2) {
                            $validation->{$validationMethod[0]}($validationMethod[1]);
                        } else {
                            $validation->{$validationMethod[0]}();
                        }
                    }
                }
                $this->form->{"add".$item->presenter}(array(
                    "name" => $item->name, 
                    "label" => $item->title,
                    "validation" => $validation,
                    "options" => $this->getOptions(isset($item->options) ? $item->options : null)
                ));
                // We should add a hidden input, which will keep the current value of the file item
                // Otherwise, after submit an empty value will be writen
                if($item->presenter == "File") {
                   $this->form->addHiddenField(array(
                        "name" => $item->name."_hidden"
                    )); 
                }
            }
            // We should send the id of the record while editing
            if($editMode) {
                $this->form->addHiddenField(array(
                    "name" => "id"
                ));
            }
        }
        protected function handleFileUploads($data) {
            foreach ($data as $key => $value) {
                if(is_array($value) && isset($value["name"]) && $value["name"] != "") {
                    $dir = uniqid();
                    mkdir(__DIR__."/../../".FILES_DIR.$dir);
                    if(move_uploaded_file($value["tmp_name"], __DIR__."/../../".FILES_DIR.$dir."/".$value["name"])) {
                        $data->$key = $dir."/".$value["name"];
                    } else {
                        throw new Exception("Can't upload file.");
                    }
                    unset($data->{$key."_hidden"});
                } else if(is_array($value) && isset($value["name"]) && $value["name"] == "") {
                    $data->$key = $data->{$key."_hidden"};
                    unset($data->{$key."_hidden"});
                }
            }
            return $data;
        }
        protected function prepareObjectForSave($data) {
            foreach($data as $key => $value) {
                if(is_object($value) || is_array($value)) {
                    // $data->$key = serialize($value);
                }
            }
            return $data;
        }
        protected function getOptions($optionsStr) {
            if($optionsStr != null) {
                if(is_object($optionsStr)) {
                    return get_object_vars($optionsStr);
                } else if(is_string($optionsStr)) {
                    $options = explode(":", $optionsStr);
                    $resourceFile = $options[0];
                    $itemName = $options[1];
                    $resources = new Resources();
                    $resource = $resources->getByFilename($resourceFile)->content;
                    $action = new Action($resource);
                    $action->defineContext();
                    $records = $this->mysql->{$action->resource->name}->order("position")->asc()->get();
                    $resultOptions = array();
                    foreach($records as $record) {
                        $resultOptions[$record->id] = $record->{$itemName};
                    }
                    return $resultOptions;
                } else {
                    return null;
                }
            }
            return $optionsStr;            
        }
        protected function formatFileLink($file) {
            $info = pathinfo($file);
            if(!isset($info["extension"])) {
                return "";
            }
            $ext = strtolower($info["extension"]);
            if(in_array($ext, array("jpg", "jpeg", "png", "gif", "bmp"))) {
                return view("resource/list-link-with-image.html", array(
                    "src" => ADMINUI_URL.FILES_DIR.$file,
                    "link" => ADMINUI_URL.FILES_DIR.$file
                ));
            } else {
                return view("resource/list-link.html", array(
                    "label" => $info["basename"],
                    "link" => ADMINUI_URL.FILES_DIR.$file
                ));
            }            
        }
    }

?>