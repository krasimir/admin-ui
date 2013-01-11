<?php

    class Resource extends Controller {

        private $resource;
        private $form;

        public function __construct($params) {

            parent::__construct();
            $matchedRouterRule = $params["ROUTER_RULE_MATCH"];
            $resource = new Resources();
            $this->resource = $resource->getByName($params["name"])->content;
            $this->defineContext();

            if($matchedRouterRule->pattern == "/resources/@name/add") {
                $this->add();
            } if($matchedRouterRule->pattern == "/resources/@name/edit/@id") {

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
            if(count($records) > 0) {
                // table header
                foreach($records[0] as $field => $value) {
                    if(!in_array($field, $skipColumns) && $field != "id" && $field != "position") {
                        $headersMarkup .= view("resource/list-item-column.html", array(
                            "value" => $this->getItemTitle($field)
                        ));
                    }
                }
                $recordsMarkup .= view("resource/list-item-row.html", array(
                    "columns" => $headersMarkup
                ));
                // table body
                foreach($records as $record) {
                    $columnsMarkup = '';
                    foreach($record as $field => $value) {
                        if(!in_array($field, $skipColumns) && $field != "id" && $field != "position") {
                            $columnsMarkup .= view("resource/list-item-column.html", array(
                                "value" => $value
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
                $recordsMarkup = 'There is no data added.';
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
        private function add() {
            $this->defineForm();
            if($this->form->submitted && $this->form->success) {
                // Form is submitted
                $data = $this->form->data;
                $this->mysql->{$this->resource->name}->save($data);
                $this->response->write(view("layout.html", array(
                    "pageTitle" => $this->resource->title,
                    "content" => view("resource/add.html", array(
                        "title" => $this->resource->title,
                        "name" => $this->resource->name,
                        "form" => view("resource/success.html", array(
                            "message" => "The record is added successfully. <a href='".ADMINUI_URL."resource/".$this->resource->name."'>Go back</a>."
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
        // defining database context and resource form
        private function defineContext() {
            $fields = array();
            foreach($this->resource->data as $item) {
                $fields[$item->name] = $item->type; 
            }
            $this->mysql->defineContext($this->resource->name, $fields);
        }
        private function defineForm() {
            $this->form = Former::register("resource-".$this->resource->name, ADMINUI_URL."/resources/".$this->resource->name."/add");
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
            }
            $this->form->update($_POST);
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
        private function getItemTitle($name) {
            foreach($this->resource->data as $item) {
                if($item->name == $name) {
                    return $item->title;
                }
            }
            return "";
        }
    }

?>