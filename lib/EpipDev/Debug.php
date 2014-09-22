<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Debug
 *
 * @author vu cao
 */
class EpipDev_Debug {

    public function __construct($dump_data, $path=null) {
        
        if (!$path) {
            $path = Mage::getBaseDir().'\\dump.html';
        }
        @unlink($path);
        $writer = new Zend_Log_Writer_Stream($path);
        $logger = new Zend_Log($writer);
        ob_start();
        var_dump($dump_data);
        $data = ob_get_clean();
        $logger->info($data);
    }

}

?>
