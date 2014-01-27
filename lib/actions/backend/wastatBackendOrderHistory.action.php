<?php

class wastatBackendOrderHistoryAction extends wastatViewAction {

    public function execute() {
        $transaction_model = new wastatTransactionModel();
        $transactions = $transaction_model->order('date DESC, after DESC')->fetchAll();
        /* $model = new waModel();
          $sql = "SELECT `t`.*, `dp`.`name` FROM `wastat_transaction` as `t` "
          . "LEFT JOIN `wastat_developer_products` as `dp` "
          . "ON `t`.`wa_product_id`=`dp`.`wa_product_id`";
          $rows = $model->query($sql)->fetchAll();
          print_r($rows); */
        $this->view->assign('transactions', $transactions);
    }

}
