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

    
    public function _test_search_items_by_keywords() {

        $query    = 'iphone 14';
        $minPrice = 20;
        $result   = '';

        try{ 
            
            $result = $this->client->searchItems( $query );
        
        } catch( Exception $e ){

            var_dump( $e->getMessage() );
        }


        //var_dump(  $result  );
        //var_dump( array_map( fn( $item ) => [ 'id' => $item->itemId, $item->title => "{$item->price->value} {$item->price->currency}"] , $result->itemSummaries ) );

        $this->assertIsObject( $result );
    }


    
    public function test_get_item_by_asin() {

        $asin     =   'B0B8JTS9XR'; //'B0C7BV3CYD'; // 
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
