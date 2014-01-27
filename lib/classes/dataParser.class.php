<?php

class dataParser {

    protected $cookie;
    protected $login;
    protected $password;
    protected $products;

    public function __construct($login = '', $password = '') {
        $this->login = $login;
        $this->password = $password;
    }

    public function login() {
        if ($this->cookie) {
            return $this->cookie;
        }
        $url = 'https://www.webasyst.com/login/';
        $postdata = array(
            'login' => $this->login,
            'password' => $this->password,
            'wa_auth_login' => 1
        );
        $curl = new curlRequest();
        $response = $curl->post($url, $postdata);

        $cookie = array();
        if (preg_match_all('/Set-Cookie:(.+)/', $response['html_body'], $matchs)) {
            $cookie = $matchs[1];
        }

        if ($response['http_code'] == 200) {
            return false;
        } elseif ($response['http_code'] == 302 && $response['redirect_url'] == 'https://www.webasyst.com/my/') {
            $this->cookie = $cookie;
            return $cookie;
        }
    }

    public function getPersonalAccountId() {
        $url = 'https://www.webasyst.com/my/?action=developerProductList';
        $cookie = $this->login();
        if (!$cookie) {
            throw new waException('Ошибка авторизации');
        }
        $curl = new curlRequest();
        $response = $curl->get($url, array('cookie' => $cookie));


        require_once wa()->getAppPath('lib/classes/vender/simple_html_dom.php');
        $html = str_get_html($response['html_body']);
        $links = $html->find('div[class=block] p[class=small] a');
        $id = false;
        if ($links) {
            foreach ($links as $link) {
                if (preg_match('/#\/ca\/info\/([0-9]+)/', $link->href, $match)) {
                    $id = $match[1];
                    break;
                }
            }
        }
        $html->__destruct();
        return $id;
    }

    public function getDeveloperProductList() {
        $url = 'https://www.webasyst.com/my/?action=developerProductList';
        $cookie = $this->login();
        if (!$cookie) {
            throw new waException('Ошибка авторизации');
        }
        $curl = new curlRequest();
        $response = $curl->get($url, array('cookie' => $cookie));


        require_once wa()->getAppPath('lib/classes/vender/simple_html_dom.php');
        $html = str_get_html($response['html_body']);
        $trs = $html->find('div[class=block] table[id=vp-table]', 0)->children(1)->find('tr');

        $developer_products_model = new wastatDeveloperProductsModel();
        $developer_products_model->truncate();
        if ($trs) {
            foreach ($trs as $tr) {
                $class_product = $tr->children(0)->children(0)->children(0)->class;
                $type = '';
                switch ($class_product) {
                    case 'icon16 plugins':
                        $type = 'plugins';
                        break;
                    case 'icon16 design':
                        $type = 'design';
                        break;
                    case 'icon16 apps':
                        $type = 'apps';
                        break;
                }
                $wa_product_id = str_replace('vp-', '', $tr->id);
                $link = $tr->children(1)->children(0)->innertext;
                $name = trim(preg_replace('/<span[^>].+>.+<\/span>/', '', $link));
                $path_name = $tr->children(1)->children(0)->children(0)->innertext;
                $version = $tr->children(2)->children(0)->innertext;
                $status = $tr->children(3)->children(0)->children(0)->innertext;
                $updated = $this->prepareDate($tr->children(4)->children(0)->innertext);

                $list = explode('/', $path_name);
                $code_name = array_pop($list);

                $app = '';
                if ($type != 'app') {
                    $app = array_shift($list);
                }

                $data = array(
                    'wa_product_id' => $wa_product_id,
                    'type' => $type,
                    'app' => $app,
                    'name' => $name,
                    'code_name' => $code_name,
                    'path_name' => $path_name,
                    'version' => $version,
                    'status' => $status,
                    'updated' => $updated,
                );
                $developer_products_model->insert($data);
            }
        }
        $html->__destruct();
    }

    protected function preparePrice($price) {
        $price = strip_tags($price);
        $price = str_replace(' ', '', $price);
        $price = str_replace(',', '.', $price);
        return $price;
    }

    protected function prepareDate($date) {
        $time = strtotime($date);
        return date('Y-m-d H:i', $time);
    }

    protected function defineProductByComment($comment) {
        if (!$this->products) {
            $developer_products_model = new wastatDeveloperProductsModel();
            $this->products = $developer_products_model->where("status = 'Опубликован'")->fetchAll();
        }

        foreach ($this->products as $product) {
            if (strpos($comment, $product['name']) !== false) {
                return $product;
            }
        }
    }

    public function download() {
        $url_temp = 'https://www.webasyst.com/my/?action=checkingaccountInfo&id=%d';
        $id = $this->getPersonalAccountId();
        $url = sprintf($url_temp, $id);

        $cookie = $this->login();
        if (!$cookie) {
            throw new waException('Ошибка авторизации');
        }
        $curl = new curlRequest();
        $response = $curl->get($url, array('cookie' => $cookie));
        require_once wa()->getAppPath('lib/classes/vender/simple_html_dom.php');
        $html = str_get_html($response['html_body']);
        $trs = $html->find('div[class=block] table tbody tr[class=log_row]');

        $transaction_model = new wastatTransactionModel();
        if ($trs) {
            foreach ($trs as $tr) {

                $amount = $this->preparePrice($tr->find('td', 2)->innertext);
                $order_id = $tr->find('td', 4)->innertext;
                $comment = $tr->find('td', 5)->innertext;
                $product = $this->defineProductByComment($comment);

                $exist = $transaction_model->where("order_id = '" . $order_id .
                                "' AND amount = '" . $amount . "'")->fetch();
                if (!$exist) {
                    $data = array(
                        'date' => $this->prepareDate($tr->find('td', 0)->innertext),
                        'before' => $this->preparePrice($tr->find('td', 1)->innertext),
                        'amount' => $amount,
                        'after' => $this->preparePrice($tr->find('td', 3)->innertext),
                        'order_id' => $order_id,
                        'comment' => $comment,
                        'wa_product_id' => $product['wa_product_id'],
                    );
                    $transaction_model->insert($data);
                }
            }
        }

        $html->__destruct();
    }

}
