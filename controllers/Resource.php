<?php

    class Resource extends Controller {

        private $resources;
        private $resource;
        private $form;
        private $params;
        private $itemsPerPage = 10;

        public function __construct($params) {

            parent::__construct();
            $matchedRouterRule = $params["ROUTER_RULE_MATCH"];
            $this->params = $params;
            $this->resources = new Resources();
            $this->resource = $this->resources->getByName($params["name"])->content;
            $this->defineContext($this->resource);

            if($matchedRouterRule->pattern == "/resources/@name/add") {
                $this->add();
            } else if($matchedRouterRule->pattern == "/resources/@name/edit/@id") {
               $this->edit();
            } else if($matchedRouterRule->pattern == "/resources/@name/delete/@id") {
                $this->delete();
            } else if($matchedRouterRule->pattern == "/resources/@name/up/@id") {
                $this->changePosition("up");
            } else if($matchedRouterRule->pattern == "/resources/@name/down/@id") {
                $this->changePosition("down");
            } else {
                $this->showList();
            }
            
        }
        // pages
        private function showList() {

            $allRecords = $this->mysql->action("SELECT COUNT(*) as num FROM ".$this->resource->name);
            if($allRecords === false) {
                $allRecords = 0;
            } else {
                $allRecords = $allRecords[0]->num;
            }
            $currentPage = isset($this->params["page"]) ? $this->params["page"] : 0;
            $from = $currentPage * $this->itemsPerPage;
            $to = $this->itemsPerPage;          

            $records = $this->mysql->{$this->resource->name}->order("position")->asc()->limit($from.",".$to)->get();
            $recordsMarkup = '';
            $headersMarkup = '';
            $skipColumns = $this->getColumnsForSkipping();
            if($records != false && count($records) > 0) {
                // table header
                foreach($this->resource->data as $item) {
                    if(!in_array($item->name, $skipColumns)) {
                        $headersMarkup .= view("resource/list-item-column.html", array(
                            "value" => $item->name
                        ));
                    }
                }
                $recordsMarkup .= view("resource/list-item-row-headers.html", array(
                    "columns" => $headersMarkup
                ));
                // table body
                foreach($records as $record) {
                    $columnsMarkup = '';
                    foreach($this->resource->data as $item) {
                        if(!in_array($item->name, $skipColumns) && $item->name != "id" && $item->name != "position") {
                            $columnsMarkup .= view("resource/list-item-column.html", array(
                                "value" => $item->presenter == "File" ? $this->formatFileLink($record->{$item->name}) : $this->formatListText($record->{$item->name})
                            ));
                        }
                    }
                    $recordsMarkup .= view("resource/list-item-row.html", array(
                        "columns" => $columnsMarkup,
                        "id" => $record->id,
                        "resourceName" => $this->resource->name
                    ));
                }
            } else {
                $recordsMarkup = 'There is no added data.';
            }
            $this->response->write(view("layout.html", array(
                "pageTitle" => $this->resource->title,
                "content" => view("resource/index.html", array(
                    "title" => $this->resource->title,
                    "name" => $this->resource->name,
                    "records" => $recordsMarkup,
                    "pagination" => $this->pagination($currentPage, $allRecords)
                )),
                "nav" => view("nav.html")
            )))->send();
        }
        private function add($default = null) {
            $this->defineForm();
            $this->form->update(array_merge($_POST, $_FILES), $default);
            if($this->form->submitted && $this->form->success) {
                // Form is submitted
                $data = $this->handleFileUploads($this->form->data);
                $this->mysql->{$this->resource->name}->save($data);
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => view("resource/success.html", array(
                            "message" => "The record is added successfully. <a href='".ADMINUI_URL."resources/".$this->resource->name."'>Back</a>."
                        ))
                    )),
                    "nav" => view("nav.html")
                )))->send();
            } else {
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => $this->form->markup
                    )),
                    "nav" => view("nav.html")
                )))->send();
            }
        }
        private function edit() {
            $record = $this->mysql->{$this->resource->name}->where("id=".$this->params["id"])->get();
            $record = $record[0];
            foreach($this->resource->data as $item) {
                if($item->presenter == "File") {
                   $record->{$item->name."_hidden"} = $record->{$item->name};
                }
            }
            $this->add($record);
        }
        private function delete() {
            $record = $this->mysql->{$this->resource->name}->where("id=".$this->params["id"])->get();
            $record = $record[0];
            $this->mysql->{$this->resource->name}->trash($record);
            $this->response->write(view("layout.html", array(
                "pageTitle" => $this->resource->title,
                "content" => view("resource/add.html", array(
                    "title" => $this->resource->title,
                    "name" => $this->resource->name,
                    "form" => view("resource/success.html", array(
                        "message" => "The record is deleted successfully. <a href='".ADMINUI_URL."resources/".$this->resource->name."'>Go back</a>."
                    ))
                )),
                "nav" => view("nav.html")
            )))->send();
        }
        private function changePosition($direction) {
            $records = $this->mysql->{$this->resource->name}->order("position")->asc()->get();
            $numOfRecords = count($records);
            for($i=0; $i<$numOfRecords; $i++) {
                $record = $records[$i];
                if($record->id == $this->params["id"]) {
                    if($direction == "up") {
                        if($i > 0) {
                            $tmp = $record->position;
                            $record->position = $records[$i-1]->position;
                            $records[$i-1]->position = $tmp;
                            $this->mysql->{$this->resource->name}->save($records[$i-1]);
                            $this->mysql->{$this->resource->name}->save($record);
                            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
                            die();
                        }
                    } else {
                        if($i < $numOfRecords-1) {
                            $tmp = $record->position;
                            $record->position = $records[$i+1]->position;
                            $records[$i+1]->position = $tmp;
                            $this->mysql->{$this->resource->name}->save($records[$i+1]);
                            $this->mysql->{$this->resource->name}->save($record);
                            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
                            die();
                        }
                    }
                }
            }
            header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
            die();
        }
        // defining database context and resource form
        private function defineContext($resource) {
            $fields = array();
            foreach($resource->data as $item) {
                $fields[$item->name] = $item->type; 
            }
            $this->mysql->defineContext($resource->name, $fields);
        }
        private function defineForm() {
            $editMode = isset($this->params["id"]);
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
        // helper methods
        private function getColumnsForSkipping() {
            if(isset($this->resource->listing) && $this->resource->listing->skip) {
                $skipColumns = explode(",", str_replace(" ", "", $this->resource->listing->skip));
                return $skipColumns;
            } else {
                return array();
            }
        }
        private function handleFileUploads($data) {
            foreach ($data as $key => $value) {
                if(is_array($value) && isset($value["name"]) && $value["name"] != "") {
                    $dir = uniqid();
                    mkdir(__DIR__."/../".FILES_DIR.$dir);
                    if(move_uploaded_file($value["tmp_name"], __DIR__."/../".FILES_DIR.$dir."/".$value["name"])) {
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
        private function formatFileLink($file) {
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
        private function formatListText($str) {
            $str = strip_tags($str);
            if(strlen($str) > 150) {
                $str = substr($str, 0, 100)."...";
            }
            $str = wordwrap($str, 25, '<br />', true);
            return $str;
        }
        private function pagination($currentPage, $allRecords) {
            $markup = '';
            for($i=0; $i<ceil($allRecords / $this->itemsPerPage); $i++) {
                $markup .= view("resource/list-pagination.html", array(
                    "link" => ADMINUI_URL."resources/".$this->resource->name."?page=".$i,
                    "label" => $i+1
                ));
            }
            return $markup;
        }
        private function getOptions($optionsStr) {
            if($optionsStr != null) {
                if(is_object($optionsStr)) {
                    return get_object_vars($optionsStr);
                } else if(is_string($optionsStr)) {
                    $options = explode(":", $optionsStr);
                    $resourceFile = $options[0];
                    $itemName = $options[1];
                    $resource = $this->resources->getByFilename($resourceFile)->content;
                    $this->defineContext($resource);
                    $records = $this->mysql->{$resource->name}->order("position")->asc()->get();
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
    }

?>