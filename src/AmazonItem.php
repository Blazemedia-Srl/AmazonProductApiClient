<?php

namespace Blazemedia\AmazonProductApi;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResponse;

class AmazonItem {

    public string $title  = '';
    public float  $price  = 0.0;
    public int    $saving = 0;
    public string $link   = '';
    public string $asin   = '';
    
    private $partnerTag;
    private $trackingPlaceholder;
    
    
    function __construct( $item, $partnerTag = 'blazemedia-21', $trackingPlaceholder = 'booBLZTRKood' ) {    

        $this->partnerTag          = $partnerTag;
        $this->trackingPlaceholder = $trackingPlaceholder;
    
        $this->asin   = $item->getASIN() != null  ? $item->getASIN() : null;
        $this->title  = $this->hasTitle( $item )  ? $item->getItemInfo()->getTitle()->getDisplayValue() : null;
        $this->price  = $this->hasOffer( $item )  ? $item->getOffers()->getListings()[0]->getPrice()->getAmount() : 0; //getAmount
        $this->saving = $this->hasSaving( $item ) ? $item->getOffers()->getListings()[0]->getPrice()->getSavings()->getPercentage() : 0;
        $this->link   = $this->addTrackingPlaceholder( $item->getDetailPageURL() );

    }


    public function toArray() {

        return [
            'title'  => $this->title,
            'asin'   => $this->asin,
            'price'  => $this->price,
            'saving' => $this->saving,
            'link'   => $this->link,
        ];  
    }
    
    private function hasOffer( $item ) : bool {

        return $item->getOffers() != null && 
               $item->getOffers()->getListings() != null && 
               $item->getOffers()->getListings()[0]->getPrice() != null &&
               $item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() != null;
    }


    private function hasSaving( $item ) : bool {

        return $item->getOffers() != null && 
               $item->getOffers()->getListings() != null && 
               $item->getOffers()->getListings()[0]->getPrice() != null &&
               $item->getOffers()->getListings()[0]->getPrice()->getSavings() != null &&
               $item->getOffers()->getListings()[0]->getPrice()->getSavings()->getPercentage() != null;
    }


    private function hasTitle( $item ) : bool {

        return $item->getItemInfo() != null &&
               $item->getItemInfo()->getTitle() != null && 
               $item->getItemInfo()->getTitle()->getDisplayValue() != null;
    }


    private function addTrackingPlaceholder( string $link) : string {

        return str_replace( $this->partnerTag, $this->trackingPlaceholder, $link);
    }

}
