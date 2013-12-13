<?php
// ini_set('xdebug.var_display_max_depth', '-1');
// ini_set('xdebug.var_display_max_children', '-1');

require 'vendor/autoload.php';
require 'goutte.phar';

use Goutte\Client;

class Crawl {
    
    protected $_client;
    const MAGENTO_DEV_DIR_URL = 'http://www.magentocommerce.com/certification/directory/?q=&in=&country_id=US&region_id=&region=&certificate_type=&p=';
    const OUTPUT_FILENAME     = 'output.csv';

    public function __construct()
    {
        $this->_client  = new Client();
        $this->dom      = new DOMDocument();
        $this->run();       
    }

    public function run()
    {
        $results = array();
        $preg    = array();

        $initCrawler = $this->_client->request('GET', self::MAGENTO_DEV_DIR_URL);
        $numPages = $initCrawler->filter('a.last')->text();

        $limit = (int)$numPages;

        for($i=1;$i<=$limit;$i++){
            
            $crawler = $this->_client->request('GET', self::MAGENTO_DEV_DIR_URL . $i);
            $numDevelopers = $crawler->filter('.results tr')->count();
            // var_dump($numDevelopers);

            for($j=1;$j<$numDevelopers;$j++){
                $developer = $crawler->filter('.results tr')->eq($j);

                $results[] = array(
                    'name'          =>trim($developer->filter('.tb-col-00 b')->text()),
                    'company'       =>trim($developer->filter('.tb-col-01')->text()),
                    'location'      =>trim($developer->filter('.tb-col-02')->text()),
                    'certification' =>trim($developer->filter('.tb-col-03')->text()),
                    'date'          =>trim($developer->filter('.tb-col-04')->text())
                );

                echo "Processed record $j of page $i" . PHP_EOL;
            }

        }

        $output = array();
        foreach($results as $line){
            $output[] = join(',', $line);
        }

        file_put_contents(self::OUTPUT_FILENAME, join("\n",$output));
    }
}

new Crawl();
