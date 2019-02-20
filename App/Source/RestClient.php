<?php
namespace App\Source;

class RestClient
{

    protected  $fetchedUrl = [];

    protected static $instance  = NULL;

    protected $curlHandler = NULL;

    private function  __construct(){}

    /**
     * @return RestClient|null
     */

    public static function getInstance()
    {
        if (self::$instance == NUll) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $url
     * @param bool $returnOnlySize
     * @return array
     */

    public function call($url, $returnOnlySize = false)
    {
        if ($this->curlHandler === null) {
            $this->curlHandler = curl_init();
            curl_setopt($this->curlHandler, CURLOPT_RETURNTRANSFER, true);
        }

        curl_setopt($this->curlHandler, CURLOPT_HEADER, FALSE);
        curl_setopt($this->curlHandler, CURLOPT_NOBODY, FALSE);
        if ($returnOnlySize) {
            curl_setopt($this->curlHandler, CURLOPT_NOBODY, TRUE);
        } else {
            curl_setopt($this->curlHandler, CURLOPT_HEADER, FALSE);
        }

        curl_setopt($this->curlHandler, CURLOPT_URL, $url);

        $response = curl_exec($this->curlHandler);
        $size = curl_getinfo($this->curlHandler, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        $isError = (curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE) >= 400);

        return [
            'data' => ($isError ? NULL : $response),
            'size' => $size,
            'redirect' => curl_getinfo($this->curlHandler, CURLINFO_REDIRECT_URL),
            'isError' => $isError
        ];
    }

}