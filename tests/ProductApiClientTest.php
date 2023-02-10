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
    public function it_can_search_items_by_keywords() {

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


    /** @test */
    public function it_can_get_item_by_asin() {

        $asin     = 'B0B8JTS9XR'; 
        $result = '';

        try{ 
            $result = $this->client->getItem( $asin );
        
        } catch( Exception $e ){

            var_dump( $e->getMessage() );
        }

    
        $this->assertIsObject( $result );
    }

}
