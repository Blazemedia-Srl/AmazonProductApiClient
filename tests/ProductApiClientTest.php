<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Blazemedia\AmazonProductApi\ProductApiClient;


final class ProductApiClientTest extends TestCase {

    protected $client;

    protected function setUp(): void {

        parent::setUP();

        $config_file = str_replace('tests','', __DIR__) . 'amazon_product_api_config.json';

        $this->client = new ProductApiClient(  $config_file );
    }
    

    /** @test */
    public function it_can_test() {

        $this->assertTrue( true );
    }

    /** @test */    
    public function test_get_item_by_asin() {

        $asin     =   'B0D5YSDDZV'; //'B0C7BV3CYD'; // 
        $result = '';

        try{ 
            $result = $this->client->getItem( $asin );

            var_dump($result->toArray());die;
        
        } catch( Exception $e ){

            var_dump( $e->getMessage() );
        }
    
        $this->assertIsObject( $result );
    }

}
