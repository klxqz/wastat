<?php

class wastatDeveloperProductsModel extends waModel {

    protected $table = 'wastat_developer_products';

    public function truncate() {
        $sql = 'TRUNCATE TABLE ' . $this->table;
        $this->query($sql);
    }

}
