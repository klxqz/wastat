<?php

class wastatBackendProductsAction extends wastatViewAction {

    public function execute() {
        /*
        $url = 'https://www.webasyst.com/my/';
        
        $app_settings_model = new waAppSettingsModel();
        echo $login = $app_settings_model->get(array('wastat',''), 'login');
        echo $password = $app_settings_model->get(array('wastat',''), 'password');

        $parser = new dataParser($login, $password);
        //$parser->download();
        $parser->getDeveloperProductList();
        */

        $developer_products_model = new wastatDeveloperProductsModel();
        $products = $developer_products_model->order('id')->fetchAll();
        $this->view->assign('products', $products);
    }

}
