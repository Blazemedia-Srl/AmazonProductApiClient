<?php

namespace Blazemedia\AmazonProductApi\Utility;

class StringHelper {

    /**
     * Replace special characters and spaces in a given string
     * and return the result lowering capital letters
     */
    static function slugify( $text ) {

        $text = str_replace('à', 'a', $text);
        $text = str_replace(array('è','é'), 'e', $text);
        $text = str_replace('ì', 'i', $text);
        $text = str_replace('ò', 'o', $text);
        $text = str_replace('ù', 'u', $text);
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, '-');
        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    static function floatToCurrency( float $price ) : string {

        return number_format( $price, 2, ",", ".");
        // number_format( float $num, int $decimals = 0,string $decimal_separator = ".", string $thousands_separator = "," ): string
    }


}
