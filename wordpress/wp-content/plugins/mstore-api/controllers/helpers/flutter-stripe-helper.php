<?php
class FlutterStripeHelper {
    public $headers;
    public $url = 'https://api.stripe.com/v1/';
    public $method = null;
    public $fields = array();
    
    function __construct ($apiKey) {
        $this->headers = array('Authorization: Bearer '.$apiKey, 'Content-Type: application/x-www-form-urlencoded');
    }
    
    function call () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

        switch ($this->method){
           case "POST":
              curl_setopt($ch, CURLOPT_POST, 1);
              if ($this->fields)
                 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->fields));
              break;
           case "PUT":
              curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
              if ($this->fields)
                 curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->fields));
              break;
           default:
              if ($this->fields)
                 $this->url = sprintf("%s?%s", $this->url, http_build_query($this->fields));
        }

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);

        return json_decode($output, true); // return php array with api response
    }
}
?>