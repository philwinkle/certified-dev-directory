<?php

require_once('goutte.phar');
require 'vendor/autoload.php';

use Goutte\Client;

class Crawl {
    
    protected $_client;
    const MAGENTO_DEV_DIR_URL = 'http://www.magentocommerce.com/certification/directory/?q=&in=&country_id=US&region_id=&region=&certificate_type=&p=';

    public function __construct()
    {
        $this->_client = new Client();
        $this->run();       
    }

    public function run()
    {
        $results = array();
        $preg    = array();

        $crawler = $this->_client->request('GET', self::MAGENTO_DEV_DIR_URL);
        $numPages = $crawler->filter('.pager .f-left');

        preg_match('/of\s+?(.*?)\s+?results/i',$numPages->text(),$preg);

        $limit = (int)$preg[1];

        for($i=1;$i<=$limit;$i++){
            
            $crawler = $this->_client->request('GET', self::MAGENTO_DEV_DIR_URL . $i);

            $results[] = $crawler->filter('.results tr');
            var_dump($results);
            die();
        }
    }
}

new Crawl();
