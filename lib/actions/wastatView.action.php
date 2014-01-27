<?php

class wastatViewAction extends waViewAction
{
    public function __construct($params = null)
    {
        parent::__construct($params);
        $app = wa()->getConfig()->getPrefix();
        preg_match("/$app(.+)Action/",get_class($this),$match);
        $template = 'templates/actions/' . wa()->getEnv() . '/' . $match[1] . $this->view->getPostfix();
        $this->setTemplate($template);
    }

}
