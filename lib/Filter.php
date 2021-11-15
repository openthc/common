<?php
/**
 * Class for common filters
 */
namespace OpenTHC;

class Filter
{
       /**
        * @param $x the email address data
        * @param $d default if not valid
        * @return normalized/validated email, false on failure
        */
       static function email($x, $d = false)
       {
               $x = strtolower(trim($x));

               return filter_var($x, FILTER_VALIDATE_EMAIL);
       }

       static function phone() {}

       static function float() {}
}

