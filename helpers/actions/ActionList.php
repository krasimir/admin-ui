<?php

    class ActionList extends Action {

        protected $itemsPerPage = 10;
        protected $pagination;

        public function __construct($resource) {
            parent::__construct($resource);
            $this->initPagination();
            $this->defineContext();
            $this->run();
        }
        public function run() {

            // getting the records and prepare the html markup
            $records = $this->mysql->{$this->resource->name}->order("position")->asc()->limit($this->pagination->from.",".$this->pagination->to)->get();
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
                            $value = $record->{$item->name};
                            if($item->presenter == "Check" || $item->presenter == "Radio" || $item->presenter == "DropDown") {                                
                                $options = $this->getOptions($item->options);
                                if(!is_array($value)) $value = array($value);
                                $valueStr = '';
                                $numOfValues = count($value);
                                for($i=0; $i<$numOfValues; $i++) {
                                    if(isset($options[$value[$i]])) {
                                        $valueStr .= $options[$value[$i]];
                                        if($i < $numOfValues-1) {
                                            $valueStr .= ", ";
                                        }
                                    }
                                }
                                $value = $valueStr;
                            }
                            $columnsMarkup .= view("resource/list-item-column.html", array(
                                "value" => $item->presenter == "File" ? $this->formatFileLink($value) : $this->formatListText($value)
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
                    "pagination" => $this->pagination->markup
                )),
                "nav" => view("nav.html")
            )))->send();

        }
        protected function initPagination() {
            $allRecords = $this->mysql->action("SELECT COUNT(*) as num FROM ".$this->resource->name);
            if($allRecords === false) {
                $allRecords = 0;
            } else {
                $allRecords = $allRecords[0]->num;
            }
            $this->pagination = (object) array(
                "current" => isset($this->params["page"]) ? $this->params["page"] : 0,
                "total" => $allRecords,
                "markup" => "",
                "from" => isset($this->params["page"]) ? $this->params["page"] : 0 * $this->itemsPerPage,
                "to" => $this->itemsPerPage
            );
            for($i=0; $i<ceil($this->pagination->total / $this->itemsPerPage); $i++) {
                $this->pagination->markup .= view("resource/list-pagination.html", array(
                    "link" => ADMINUI_URL."resources/".$this->resource->name."?page=".$i,
                    "label" => $i+1
                ));
            }
        }
        private function getColumnsForSkipping() {
            if(isset($this->resource->listing) && $this->resource->listing->skip) {
                $skipColumns = explode(",", str_replace(" ", "", $this->resource->listing->skip));
                return $skipColumns;
            } else {
                return array();
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
    }

?>