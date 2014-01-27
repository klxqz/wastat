<?php

class wastatBackendSaveAuthController extends waJsonController {

    public function execute() {
        $login = waRequest::post('login');
        $password = waRequest::post('password');
        $app_settings_model = new waAppSettingsModel();
        $app_settings_model->set(array('wastat',''), 'login', $login);
        $app_settings_model->set(array('wastat',''), 'password', $password);
    }

}
