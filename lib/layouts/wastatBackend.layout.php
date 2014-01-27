<?php 

class wastatBackendLayout extends waLayout
{

    public function execute()
    {
        $this->executeAction('sidebar', new wastatSidebarAction());
    }
}