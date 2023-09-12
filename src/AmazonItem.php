<?php

namespace Blazemedia\AmazonProductApi;

use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\GetItemsResponse;

class AmazonItem {

    private $item;

    public string $title     = '';
    public float  $price     = 0.0;
    public float  $fullprice = 0.0;

    public int    $saving = 0;
    public string $link   = '';
    public string $asin   = '';
    public string $image  = '';
    
    private $partnerTag;
    private $trackingPlaceholder;
    
    
    function __construct( $item, $partnerTag = 'blazemedia-21', $trackingPlaceholder = 'booBLZTRKood' ) {    

        $this->item = $item;
        $this->partnerTag          = $partnerTag;
        $this->trackingPlaceholder = $trackingPlaceholder;
    
        $this->asin      = $this->item->getASIN() != null  ? $this->item->getASIN() : null;
        $this->title     = $this->hasTitle()  ? $this->item->getItemInfo()->getTitle()->getDisplayValue() : null;
        $this->price     = $this->getPrice();
        $this->saving    = $this->getSaving();
        $this->fullprice = $this->getFullPrice();
        $this->link      = $this->addTrackingPlaceholder( $item->getDetailPageURL() );
        $this->image     = $this->getImage();
        $this->fullprice = $this->getFullPrice();

    }


    public function toArray() {

        return [
            'title'      => $this->title,
            'asin'       => $this->asin,
            'price'      => $this->price,
            'fullprice'  => $this->fullprice,
            'saving'     => $this->saving,
            'link'       => $this->link,
            'images'     => $this->image,
        ];  
    }
    
    private function hasOffer() : bool {

        return $this->item->getOffers() != null && 
               $this->item->getOffers()->getListings() != null && 
               $this->item->getOffers()->getListings()[0]->getPrice() != null &&
               $this->item->getOffers()->getListings()[0]->getPrice()->getDisplayAmount() != null;
    }


    private function hasSaving() : bool {

        return $this->item->getOffers() != null && 
               $this->item->getOffers()->getListings() != null && 
               $this->item->getOffers()->getListings()[0]->getPrice() != null &&
               $this->item->getOffers()->getListings()[0]->getPrice()->getSavings() != null &&
               $this->item->getOffers()->getListings()[0]->getPrice()->getSavings()->getPercentage() != null;
    }


    private function hasTitle() : bool {

        return $this->item->getItemInfo() != null &&
               $this->item->getItemInfo()->getTitle() != null && 
               $this->item->getItemInfo()->getTitle()->getDisplayValue() != null;
    }


    private function addTrackingPlaceholder( string $link) : string {

        return str_replace( $this->partnerTag, $this->trackingPlaceholder, $link);
    }

    private function getImage() : string {
        
        return $this->item->getImages()->getPrimary()->getLarge()->getUrl();
    }

    private function getPrice() : float {

        return $this->hasOffer( $this->item )  ? $this->item->getOffers()->getListings()[0]->getPrice()->getAmount() : 0; //getAmount

    }

    private function getSaving() : int {

        return $this->hasSaving() ? $this->item->getOffers()->getListings()[0]->getPrice()->getSavings()->getPercentage() : 0;

    }

    private function getFullPrice() : float {
        
        $savingAmount = $this->hasSaving() ? $this->item->getOffers()->getListings()[0]->getPrice()->getSavings()->getAmount() : 0;

        return $this->getPrice() + $savingAmount;
        
    }

}
