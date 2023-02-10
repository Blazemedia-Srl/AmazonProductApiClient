<?php

namespace Blazemedia\AmazonProductApi;

use Exception;
use GuzzleHttp\Client;
use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Blazemedia\AmazonProductApi\Utility\StringHelper;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResource;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResponse;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResponse;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetVariationsResource;


define( 'AMAZON_PRODUCT_API_CONFIG_FILE', str_replace('src','',__DIR__). 'amazon_product_api_config.json');


class ProductApiClient {

    private DefaultApi $client;
    
    private $settings;

    private $resources = [
        GetItemsResource::ITEM_INFOTITLE,
        GetItemsResource::OFFERSLISTINGSPRICE,            
        GetItemsResource::PARENT_ASIN
    ];

    private $variations_resources  = [
        GetVariationsResource::ITEM_INFOTITLE,
        GetVariationsResource::OFFERSLISTINGSPRICE
    ];

    // $istance is null or ApiClient (punto interrogativo)
    private static ?ProductApiClient $instance = null; 

    
    private function __construct( string $configJsonFile ) {

        $this->settings = $this->getSettingsFromFile( $configJsonFile );

        $this->client = new DefaultApi(
                            new Client([ 'verify' => false, ]), // Guzzle client that Disable ssl validation entirely
                            $this->getConfiguration( $configJsonFile )
                        ); 
    }


    /**
     * Ritorna l'array di items ottenuti dalla response
     *
     * @param [type] $getItemsResponse
     * @return array
     */
    private function getItemsResponseData( $getItemsResponse ) : array {

        if( empty( $getItemsResponse) ) return [];
        if( $getItemsResponse->getItemsResult() == null ) return [];
        if( $getItemsResponse->getItemsResult()->getItems() == null) return [];

        return $getItemsResponse->getItemsResult()->getItems();
    }


    /**
     * Ritorna l'array di items ottenuti dalla response
     *
     * @param [type] $getItemsResponse
     * @return array
     */
    private function searchItemsResponseData( $getItemsResponse ) : array {

        if( empty( $getItemsResponse) ) return [];
        if( $getItemsResponse->getSearchResult() == null ) return [];
        if( $getItemsResponse->getSearchResult()->getItems() == null) return [];

        return $getItemsResponse->getSearchResult()->getItems();
    }

    
    /**
     * Ritorna l'offerta identificata dall'ASIN
     *
     * @param string $ASIN
     * @return AmazonItem
     */
    public function getItem( string $ASIN ) : AmazonItem|null  {

        $items = $this->getItemsResponseData( $this->getItemResponse( $ASIN ) );

        return empty( $items ) ? null : new AmazonItem( 
                                            array_shift( $items ),
                                            $this->settings->partnerTag,
                                            $this->settings->trackingPlaceholder 
                                        );
        
    }

    
    /**
     * Ritorna un elenco di offerte ricavate con la keyword
     *
     * @param string $keyword
     * @param int    $minPrice
     * @return AmazonItem[]
     */
    public function searchItems( string $keyword = '', $category = '', $maxQuantity = 20, $minPrice = 0, $maxPrice = 0 ) : array {

        $items = $this->searchItemsResponseData( $this->getSearchResponse( $keyword, $category, $maxQuantity, $minPrice, $maxPrice ) );

        return empty( $items ) ? [] : array_map( fn( $item ) => new AmazonItem( 
                                            $item,
                                            $this->settings->partnerTag,
                                            $this->settings->trackingPlaceholder 
                                       ), $items );
    }

    



    /**
     * Ritorna l'offerta identificata dall'ASIN
     *
     * @param string $ASIN
     * @return GetItemsResponse
     */
    private function getItemResponse( string $ASIN ) : GetItemsResponse {

        $getItemsRequest = new GetItemsRequest();

        $getItemsRequest->setItemIds( [ $ASIN ] );
        $getItemsRequest->setPartnerTag( $this->settings->partnerTag );
        $getItemsRequest->setPartnerType( PartnerType::ASSOCIATES );        
        $getItemsRequest->setResources( $this->resources );
      
        try {
            return $this->client->getItems($getItemsRequest);
            
        } catch (ApiException $exception) {

            return null;
        }
    }



    /**
     * Ritorna i risultati di una ricerca
     *
     * @param string $keyword
     * @param string $category
     * @param integer $maxQuantity
     * @param integer $minPrice
     * @param integer $maxPrice
     * @return SearchItemsResponse
     */
    private function getSearchResponse( string $keyword = '', $category = '', $maxQuantity = 20, $minPrice = 0, $maxPrice = 0 ) : SearchItemsResponse {

        $searchItemsRequest = new SearchItemsRequest();

        $searchItemsRequest->setKeywords( StringHelper::slugify( $keyword ) );
        
        $searchItemsRequest->setPartnerTag( $this->settings->partnerTag );
        $searchItemsRequest->setPartnerType( PartnerType::ASSOCIATES );        
        $searchItemsRequest->setResources( $this->resources );

        $searchItemsRequest->setItemCount( $maxQuantity );

        if( $category !== '' ) { $searchItemsRequest->setSearchIndex( $category ); }        
        if( $minPrice )        { $searchItemsRequest->setMinPrice( $minPrice );    }
        if( $maxPrice)         { $searchItemsRequest->setMaxPrice( $maxPrice );    }

        try {

            // $res = $this->client->searchItems($searchItemsRequest);

            // var_dump($res); die;

            return $this->client->searchItems($searchItemsRequest);
            
        } catch (ApiException $exception) {

            return null;
        }
    }
    


    private function getConfiguration( string $configJsonFile ) : Configuration|null {

        if( empty( $this->settings ) ) return null;


        $config = new Configuration();

        $config->setAccessKey( $this->settings->accessKey );
        $config->setSecretKey( $this->settings->secretKey );
        $config->setHost(      $this->settings->host      );
        $config->setRegion(    $this->settings->region    );

        return $config;
    }


    private function getSettingsFromFile( string $configJsonFile ) {

        try {

            return json_decode( file_get_contents( $configJsonFile ) );
        
        } catch( Exception $e ) {

            return null;
        }        
    }


    public static function getInstance() : ProductApiClient {

        if( self::$instance === null ) {

            self::$instance = new self( AMAZON_PRODUCT_API_CONFIG_FILE );
        }

        return self::$instance;
    }
        
}
