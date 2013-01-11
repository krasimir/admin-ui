<?php

    class Resource extends Controller {

        private $resource;
        private $form;
        private $params;

        public function __construct($params) {

            parent::__construct();
            $matchedRouterRule = $params["ROUTER_RULE_MATCH"];
            $resources = new Resources();
            $this->params = $params;
            $this->resource = $resources->getByName($params["name"])->content;
            $this->defineContext();

            if($matchedRouterRule->pattern == "/resources/@name/add") {
                $this->add();
            } else if($matchedRouterRule->pattern == "/resources/@name/edit/@id") {
               $this->edit();
            } else if($matchedRouterRule->pattern == "/resources/@name/delete/@id") {
                $this->delete();
            } else {
                $this->showList();
            }
            
        }
        // pages
        private function showList() {
            $records = $this->mysql->{$this->resource->name}->order("position")->asc()->get();  
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
                                "value" => $item->presenter == "File" ? $this->formatFileLink($record->{$item->name}) : $record->{$item->name}
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
                    "records" => $recordsMarkup
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
        // defining database context and resource form
        private function defineContext() {
            $fields = array();
            foreach($this->resource->data as $item) {
                $fields[$item->name] = $item->type; 
            }
            $this->mysql->defineContext($this->resource->name, $fields);
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
                    "validation" => $validation
                ));
                // We should add a hidden input, which will keep the current value of the file item
                // Otherwise, after submit an empty value will be writen
                if($item->presenter == "File") {
                   $this->form->addHiddenField(array(
                        "name" => $item->name."_hidden"
                    )); 
                }
            }
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
    }

?>