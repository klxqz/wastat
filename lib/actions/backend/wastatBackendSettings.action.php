<?php

class wastatBackendSettingsAction extends wastatViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $login = $app_settings_model->get(array('wastat',''), 'login');
        $password = $app_settings_model->get(array('wastat',''), 'password');
        $this->view->assign('login', $login);
        $this->view->assign('password', $password);
    }

}
