<?php

namespace App\Helpers;

class DropTableSection {
    private $header;
    private $table;

    public function __construct($header = null, $table = []) {
        $this->header = $header;
        $this->table = $table;
    }

    public function setHeader($newHeader) {
        $this->header = $newHeader;
    }

    public function setTable($newTable) {
        $this->table = $newTable;
    }

    public function getHeader() {
        return $this->header;
    }

    public function getTable() {
        return $this->table;
    }
}
