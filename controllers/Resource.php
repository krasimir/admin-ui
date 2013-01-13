<?php

    class Resource extends Controller {

        public $matchedRouterRule;
        public $resources;
        public $id;
        public $resource;

        public function __construct($params) {

            parent::__construct();
            $this->matchedRouterRule = $params["ROUTER_RULE_MATCH"];            
            $this->resources = new Resources();
            $this->resource = $this->resources->getByName(isset($params["name"]) ? $params["name"] : false)->content;

            switch($this->matchedRouterRule->pattern) {
                case "/resources/@name":
                    new ActionList($this->resource);
                break;
                case "/resources/@name/add":
                    new ActionAdd($this->resource);
                break;
            }

        }

        // public function __construct($params) {

        //     parent::__construct();
        //     $matchedRouterRule = $params["ROUTER_RULE_MATCH"];
        //     $this->params = $params;
        //     $this->resources = new Resources();
        //     $this->resource = $this->resources->getByName($params["name"])->content;
        //     $this->defineContext($this->resource);

        //     if($matchedRouterRule->pattern == "/resources/@name/add") {
        //         $this->add();
        //     } else if($matchedRouterRule->pattern == "/resources/@name/edit/@id") {
        //        $this->edit();
        //     } else if($matchedRouterRule->pattern == "/resources/@name/delete/@id") {
        //         $this->delete();
        //     } else if($matchedRouterRule->pattern == "/resources/@name/up/@id") {
        //         $this->changePosition("up");
        //     } else if($matchedRouterRule->pattern == "/resources/@name/down/@id") {
        //         $this->changePosition("down");
        //     } else {
        //         $this->showList();
        //     }
            
        // }
        // // pages
        // private function edit() {
        //     $record = $this->mysql->{$this->resource->name}->where("id=".$this->params["id"])->get();
        //     $record = $record[0];
        //     $record = $this->prepareObjectForEditOrDisplay($record);
        //     foreach($this->resource->data as $item) {
        //         if($item->presenter == "File") {
        //            $record->{$item->name."_hidden"} = $record->{$item->name};
        //         }
        //     }
        //     $this->add($record);
        // }
        // private function delete() {
        //     $record = $this->mysql->{$this->resource->name}->where("id=".$this->params["id"])->get();
        //     $record = $record[0];
        //     $this->mysql->{$this->resource->name}->trash($record);
        //     $this->response->write(view("layout.html", array(
        //         "pageTitle" => $this->resource->title,
        //         "content" => view("resource/add.html", array(
        //             "title" => $this->resource->title,
        //             "name" => $this->resource->name,
        //             "form" => view("resource/success.html", array(
        //                 "message" => "The record is deleted successfully. <a href='".ADMINUI_URL."resources/".$this->resource->name."'>Go back</a>."
        //             ))
        //         )),
        //         "nav" => view("nav.html")
        //     )))->send();
        // }
        // private function changePosition($direction) {
        //     $records = $this->mysql->{$this->resource->name}->order("position")->asc()->get();
        //     $numOfRecords = count($records);
        //     for($i=0; $i<$numOfRecords; $i++) {
        //         $record = $records[$i];
        //         if($record->id == $this->params["id"]) {
        //             if($direction == "up") {
        //                 if($i > 0) {
        //                     $tmp = $record->position;
        //                     $record->position = $records[$i-1]->position;
        //                     $records[$i-1]->position = $tmp;
        //                     $this->mysql->{$this->resource->name}->save($records[$i-1]);
        //                     $this->mysql->{$this->resource->name}->save($record);
        //                     header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
        //                     die();
        //                 }
        //             } else {
        //                 if($i < $numOfRecords-1) {
        //                     $tmp = $record->position;
        //                     $record->position = $records[$i+1]->position;
        //                     $records[$i+1]->position = $tmp;
        //                     $this->mysql->{$this->resource->name}->save($records[$i+1]);
        //                     $this->mysql->{$this->resource->name}->save($record);
        //                     header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
        //                     die();
        //                 }
        //             }
        //         }
        //     }
        //     header("Location: ".ADMINUI_URL."resources/".$this->resource->name);
        //     die();
        // }
        
        // private function prepareObjectForEditOrDisplay($data) {
        //     foreach($data as $key => $value) {
        //         if (is_serialized($value)) {
        //             $value = unserialize($value);
        //         }
        //         $data->$key = $value;
        //     }
        //     return $data;
        // }
    }

?>