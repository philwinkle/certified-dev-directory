<?php

const DS = DIRECTORY_SEPARATOR;

require 'vendor/autoload.php';
require 'goutte.phar';

use Goutte\Client;
use Keboola\Csv\CsvFile;

class Crawl
{
    
    protected $_client;
    public    $doCsvOutput;
    public    $outputUpperBound;
    public    $countryId;

    const MAGENTO_DEV_DIR_URL = 'http://www.magentocommerce.com/certification/directory/?q=&in=&country_id=%s&region_id=&region=&certificate_type=&p=';
    const OUTPUT_FILENAME     = 'output_%s.csv';

    /**
     * Constructor to set up class properties and execute init()
     * @param boolean $doCsvOutput
     */
    public function __construct($outputUpperBound = null, $countryId = 'US', $doCsvOutput = true)
    {
        //set output page upper bound
        $this->outputUpperBound = $outputUpperBound;

        //set country id
        $this->countryId = $countryId;

        //set csv output flag
        $this->doCsvOutput = $doCsvOutput;
        
        $this->_client  = new Client();
        $this->dom      = new DOMDocument();
        $this->init();
    }

    /**
     * Init the crawler and conditionally output csv
     * @return void
     */
    public function init()
    {
        $results = array(); //init the results array

        $initCrawler = $this->_client->request( 'GET', $this->getDirectoryUrl() );
        $numPages = $initCrawler->filter('a.last')->text();

        $limit = (int)$numPages;

        for($i=1;$i<=$limit;$i++){
            
            $crawler = $this->_client->request('GET', $this->getDirectoryUrl() . $i);
            $numDevelopers = $crawler->filter('.results tr')->count();

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

            if($this->outputUpperBound>1 && $i>=$this->outputUpperBound) break;
        }

        if($this->doCsvOutput){
            array_unshift($results, array('name','company','location','certification','date')); //prepend headers
            $csvFile = new CsvFile( __DIR__ . DS . $this->getOutputFilename() );

            foreach($results as $result){
                $csvFile->writeRow($result);
            }
        }
    }

    public function getDirectoryUrl()
    {
        return sprintf(self::MAGENTO_DEV_DIR_URL, $this->countryId);
    }

    public function getOutputFilename()
    {
        return sprintf(self::OUTPUT_FILENAME, $this->countryId);
    }
}

new Crawl(null, 'GB');
