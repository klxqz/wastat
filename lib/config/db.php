<?php
return array(
    'shop_affiliate_transaction' => array(
        'id' => array('int', 11, 'unsigned' => 1, 'null' => 0, 'autoincrement' => 1),
        'wa_product_id' => array('int', 11, 'unsigned' => 1, 'null' => 0),
        'type' => array('enum', "'apps','plugins','design'", 'null' => 0),
        'app' => array('varchar', 255, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0),
        'code_name' => array('varchar', 255, 'null' => 0),
        'path_name' => array('varchar', 255, 'null' => 0),
        'version' => array('varchar', 10, 'null' => 0),
        'status' => array('enum', "'Опубликован','Ожидает','Отказано','Черновик'", 'null' => 0, 'default' => 'Черновик'),
        'updated' => array('date', 'null' => 0),
        ':keys' => array(
            'PRIMARY' => 'id',
            'wa_product_id' => 'wa_product_id',
            'type' => array('type', 'app', 'code_name', 'unique' => 1),
        ),
    ),
  
);