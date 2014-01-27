<?php

class wastat {

    public function __construct() {
        
    }

    public static function totalSum($date_from = null, $date_to = null) {
        $model = new waModel();
        $where = '';
        if ($date_from) {
            $where .=" AND DATE(`date`) >= '" . $model->escape($date_from) . "'";
        }
        if ($date_to) {
            $where .=" AND DATE(`date`) <= '" . $model->escape($date_to) . "'";
        }
        $sql = "SELECT SUM(`amount`) as `sum` FROM `wastat_transaction` WHERE 1 " . $where;

        $res = $model->query($sql)->fetch();
        return $res['sum'];
    }

    public static function totalCount($date_from = null, $date_to = null) {
        $model = new waModel();
        $where = '';
        if ($date_from) {
            $where .=" AND DATE(`date`) >= '" . $model->escape($date_from) . "'";
        }
        if ($date_to) {
            $where .=" AND DATE(`date`) <= '" . $model->escape($date_to) . "'";
        }
        $sql = "SELECT COUNT(*) as `count` FROM `wastat_transaction` WHERE 1 " . $where;

        $res = $model->query($sql)->fetch();
        return $res['count'];
    }

    public function getProductStatistic($wa_product_id) {
        $model = new waModel();
        $sql = "SELECT SUM(amount) as `sum` FROM `wastat_transaction` "
                . "WHERE `wa_product_id` = '" . (int) $wa_product_id . "'";
        $res = $model->query($sql)->fetch();

        return array(
            'sum' => $res['sum'],
        );
    }

}
