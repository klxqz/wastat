<?php

class wastatBackendAction extends wastatViewAction
{
    public function execute()
    {
        $this->setLayout(new wastatBackendLayout());
    }

}
