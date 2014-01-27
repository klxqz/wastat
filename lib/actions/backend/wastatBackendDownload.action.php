<?php

class wastatBackendDownloadAction extends wastatViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $login = $app_settings_model->get(array('wastat',''), 'login');
        $password = $app_settings_model->get(array('wastat',''), 'password');

        $parser = new dataParser($login, $password);
        $parser->download();
        $parser->getDeveloperProductList();


        exit();
    }

}
