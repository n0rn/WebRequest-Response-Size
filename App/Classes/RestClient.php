<?php
namespace App\Classes;

/**
 * Singleton Class to handle the Curl Calls
 * The class will check if the given URL has beenfetched
 * before.
 * If the URL has been fetched before, the URL
 * will not be fetched again
 *
 * @author vignesh
 */
class RestClient
{

    /**
     * Property to hold the URLs that
     * has been fetched
     *
     * @var array
     */
    protected $fetchedURLS = array();

    /**
     * Property to hold the instance of the class
     *
     * @var RestClient
     */
    protected static $instance = NULL;

    /**
     * Curl Handle
     *
     * @var unknown
     */
    protected $curlHandle = NULL;

    /**
     * Making the constructor as private to
     * create new instance of the class
     */
    private function __construct()
    {}

    /**
     * Method to create singleton object of RestClient
     *
     * @return \App\Classes\RestClient
     */
    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }

    public function Call($url, $returnOnlySize = false)
    {
        if ($this->curlHandle === null) {
            $this->curlHandle = curl_init();
            curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        }
        
        curl_setopt($this->curlHandle, CURLOPT_HEADER, FALSE);
        curl_setopt($this->curlHandle, CURLOPT_NOBODY, FALSE);
        if ($returnOnlySize) {
            curl_setopt($this->curlHandle, CURLOPT_NOBODY, TRUE);
        } else {
            curl_setopt($this->curlHandle, CURLOPT_HEADER, FALSE);
        }
        
        curl_setopt($this->curlHandle, CURLOPT_URL, $url);
        
        $response = curl_exec($this->curlHandle);
        $size = curl_getinfo($this->curlHandle, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        
        $isError = (curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE) >= 400);
        
        return array(
            'data' => ($isError ? NULL : $response),
            'size' => $size,
            'redirect' => curl_getinfo($this->curlHandle, CURLINFO_REDIRECT_URL),
            'isError' => $isError
        );
    }
}