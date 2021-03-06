<?php

    use flight\net\Response;

    class Action {
        public $response;
        public $mysql;
        public $resource;
        public $id;
        protected $form;
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
        public function defineForm() {
            if(isset($this->id)) {
                $this->form = Former::register("resource-".$this->resource->name, ADMINUI_URL."resources/".$this->resource->name."/edit/".$this->id);
            } else {
                $this->form = Former::register("resource-".$this->resource->name, ADMINUI_URL."resources/".$this->resource->name."/add");
            }
            $fields = array();
            foreach($this->resource->data as $item) {                
                $this->form->{"add".$item->presenter}(array(
                    "name" => $item->name, 
                    "label" => $item->title,
                    "validation" => $this->getValidations($item),
                    "options" => $this->getOptions(isset($item->options) ? $item->options : null)
                ));
                // We should add a hidden input, which will keep the current value of the file item
                // Otherwise, after submit an empty value will be writen
                if($item->presenter == "File" || $item->presenter == "Image") {
                   $this->form->addHiddenField(array(
                        "name" => $item->name."_hidden"
                    )); 
                }
            }
            // We should send the id of the record while editing
            if(isset($this->id)) {
                $this->form->addHiddenField(array(
                    "name" => "id"
                ));
            }
        }
        protected function handleFileUploads($data) {
            foreach ($data as $key => $value) {
                if(is_array($value) && isset($value["name"]) && $value["name"] != "") {
                    $dir = uniqid();
                    $outputDir = dirname(__FILE__)."/../../../".FILES_DIR.$dir."/";
                    mkdir($outputDir);
                    if(move_uploaded_file($value["tmp_name"], $outputDir.$value["name"])) {
                        if($this->getResourceItemByName($key)->presenter == "Image") {   
                            global $IMAGE_SIZES;
                            $IMAGE_SIZES = array_merge($IMAGE_SIZES, array((object) array("prefix" => "list_", "height" => 30)));
                            foreach($IMAGE_SIZES as $size) {
                                $image = new SimpleImage();
                                $image->load($outputDir.$value["name"]);
                                if(isset($size->width) && isset($size->height)) {
                                    $image->resize($size->width, $size->height);
                                } else if(isset($size->width)) {
                                    $image->resizeToWidth($size->width);
                                } else if(isset($size->height)) {
                                    $image->resizeToHeight($size->height);
                                } else if(isset($size->scale)) {
                                    $image->scale($size->scale);
                                }
                                $image->save($outputDir.$size->prefix.$value["name"]);
                            }
                        }
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
        protected function getOptions($optionsStr) {
            if($optionsStr != null) {
                if(is_object($optionsStr)) {
                    return get_object_vars($optionsStr);
                } else if(is_string($optionsStr)) {
                    $options = explode(":", $optionsStr);
                    $resourceFile = $options[0];
                    $itemName = $options[1];
                    $resource = Resources::getByFilename($resourceFile)->content;
                    $action = new Action($resource);
                    $action->defineContext();
                    $records = $this->mysql->{$action->resource->name}->order("position")->asc()->get();
                    $resultOptions = array();
                    if($records && count($records) > 0) {
                        foreach($records as $record) {
                            $resultOptions[$record->id] = $record->{$itemName};
                        }
                    }
                    return $resultOptions;
                } else {
                    return null;
                }
            }
            return $optionsStr;            
        }
        protected function getValidations($item) {
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
            return $validation;
        }
        protected function formatData($data) {
            foreach($data as $key => $value) {
                if(is_array($value)) {
                    $str = '';
                    $numOfItems = count($value);
                    for($i=0; $i<$numOfItems; $i++) {
                        $str .= $value[$i];
                        if($i<$numOfItems-1) {
                            $str .= ',';
                        }
                    }
                    $data->$key = $str;
                }
            }
            return $data;
        }
        protected function getChildResourcesMarkup() {
            $resources = Resources::get();
            $current = Resources::getByName($this->resource->name);
            $markup = '';
            foreach($resources as $r) {
                if(isset($r->content->parent) && $r->content->parent == $current->file) {
                    $markup .= view("resource/child-resource.html", array(
                        "url" => ADMINUI_URL."resources/".$r->content->name,
                        "title" => $r->content->title
                    ));
                }
            }
            return $markup;
        }
        protected function getResourceItemByName($name) {
            foreach($this->resource->data as $item) {
                if($item->name == $name) {
                    return $item;
                }
            }
            return null;
        }
    }

?>