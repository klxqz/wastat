<?php

class wastatBackendProductStatisticAction extends wastatViewAction {

    public function execute() {
        $sort = waRequest::get('sort');
        $order = waRequest::get('order');
        $reset_filter = waRequest::get('reset_filter');
        if ($reset_filter) {
            $this->getStorage()->remove('date_from');
            $this->getStorage()->remove('date_to');
        }
        $date_from = waRequest::get('date_from', $this->getStorage()->read('date_from'));
        $date_to = waRequest::get('date_to', $this->getStorage()->read('date_to'));




        if (!in_array($sort, array('sum', 'count', 'avg', 'begin_date', 'end_date', 'avg_date'))) {
            $sort = null;
        }
        if (!in_array($order, array('desc', 'asc'))) {
            $order = null;
        }

        $model = new waModel();

        $where = '';
        if ($date_from) {
            $this->getStorage()->write('date_from', $date_from);
            $where .=" AND DATE(`date`) >= '" . $model->escape($date_from) . "'";
        }
        if ($date_to) {
            $this->getStorage()->write('date_to', $date_to);
            $where .=" AND DATE(`date`) <= '" . $model->escape($date_to) . "'";
        }


        $sql = "SELECT * FROM `wastat_developer_products` as `dp`
                LEFT JOIN (
                        SELECT 
                        SUM( amount ) as `sum`, 
                        COUNT( * ) as `count`,
                        (SUM( amount ) / COUNT( * )) as `avg`,
                        MIN(`date`) as `begin_date`,
                        MAX(`date`) as `end_date`,
                        ( COUNT( * ) / (TO_DAYS(" . ($date_to ? "'$date_to'" : 'NOW()') . ") - TO_DAYS(MIN(`date`))) ) as `avg_date`,
                        `wa_product_id`
                        FROM `wastat_transaction`
                        WHERE 1 " . $where . "
                        GROUP BY `wa_product_id`
                         
                ) as `tmp`
                ON `dp`.`wa_product_id`=`tmp`.`wa_product_id`
                WHERE `status`='Опубликован' OR `sum`>0";

        if ($sort && $order) {
            $sql .= " ORDER BY `" . $sort . "` " . $order . " ";
        }


        $products = $model->query($sql)->fetchAll();
        $this->view->assign('date_from', $date_from);
        $this->view->assign('date_to', $date_to);
        $this->view->assign('products', $products);
    }

}
