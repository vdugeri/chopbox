<?php
namespace ChopBox\helpers;
use ShortenUrl;

class ExpandUrl
{



    private $api_version = "v3";
    private $format;
    private $login;
    private $api_key;
    private $shorten;
    //setter methods

    //bitly login for the application
    public function setLogin($login)
    {
        $this->login = $login;
        $this->shorten = new ShortenUrl();
    }

    /* data return format for the api call
      formats include text, json, etc.
    */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /*set the api keu for the application */

    public function setKey($api_key)
    {
        $this->api_key = $api_key;
    }


    // Expand given url and returns expanded url
     public function expandUrl($url) 
     {
        $expanded_url = "";
         
        $hash = $this->url->parseUrl($url);
         
        $expanded_url = $this->expandUrlByHash($hash);
         
        return $expanded_url;
     }
     
     // Expand given hash and returns expanded url
     public function expandUrlByHash($hash) 
     {
         $expanded_url = "";
         $bitly_url = "http://api.bit.ly/expand?" . 
         "version=" . $this->api_version . 
         "&format=" . $this->format . 
         "&hash=" . $hash . 
         "&login=" . $this->login . 
         "&apiKey=" . $this->api_key;
         
         $content = file_get_contents($bitly_url);
     
         try 
         {
            $expanded_url = $this->parseContent($content, $hash);
         }
         catch (Exception $e) 
         {
             echo "Caught exception: " . 
             $e->getMessage() . $this->break;
             exit;
          }
     
     return $expanded_url;
    }
     
     
}

?>