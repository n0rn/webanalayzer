<?php

namespace App\Source;


class Crawler
{


    protected $htmlDOM;

    protected $url;

    protected $baseUrl = NULL;

    protected $mediaFiles = [];

    protected $cssFiles = [];

    protected $jsFiles = [];

    protected $iFrames = [];

    protected $htmlPageSize = NULL;

    protected $reDirects = [];

    protected $failedUrls = [];

    public function __construct($url)
    {
        $this->url = $url;
        $this->getData($this->url, FALSE);
    }

    protected function getData($url, $sizeOnly = TRUE)
    {
        if ($url == NULL) {
            return;
        }

        $response = RestClient::getInstance()->Call($url);

        if ($response['isError']) {
            $this->failedUrls[$url] = $response;
            return;
        }

        if (! empty($response['redirect'])) {
            if ($sizeOnly === FALSE) {
                $this->reDirects[] = new Crawler($response['redirect']);
                $this->htmlPageSize = $response['size'];
            } else {
                $this->reDirects[] = $response;
            }
        } else {
            if ($sizeOnly === FALSE) {
                $this->htmlDOM = new \DOMDocument();
                $this->htmlDOM->loadHTML($response['data']);
                $this->htmlPageSize = $response['size'];
                $this->parseMediaFiles();
                $this->parseCSSFiles();
                $this->parseJSFiles();
                $this->parseIframes();
            } else {

                return $response;
            }
        }
    }

    protected function parseMediaFiles()
    {
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('img'));
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('object'));
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('embed'));
    }

    protected function proceeMediaFiles($mediaNodes)
    {
        foreach ($mediaNodes as $mediaNode) {
            $mediaSrc = $this->appendBaseURL($mediaNode->getAttribute('src'));
            if($mediaSrc == NULL) {
                $mediaSrc = $this->appendBaseURL($mediaNode->getAttribute('data'));
            }
            $mediaData = $this->getData($mediaSrc, TRUE);
            if (empty($mediaData)) {
                continue;
            }
            $this->mediaFiles[$mediaSrc] = $mediaData['size'];
        }
    }

    protected function parseCSSFiles()
    {
        $cssNodes = $this->htmlDOM->getElementsByTagName('link');
        foreach ($cssNodes as $cssNode) {
            $cssSrc = $this->appendBaseURL($cssNode->getAttribute('href'));
            $cssData = $this->getData($cssSrc, TRUE);
            if (empty($cssData)) {
                continue;
            }
            $this->cssFiles[$cssSrc] = $cssData['size'];
        }
    }

    protected function parseJSFiles()
    {
        $jsNodes = $this->htmlDOM->getElementsByTagName('script');
        foreach ($jsNodes as $jsNode) {
            $jsSrc = $this->appendBaseURL($jsNode->getAttribute('src'));
            $jsData = $this->getData($jsSrc, TRUE);
            if (empty($jsSrc)) {
                continue;
            }
            $this->jsFiles[$jsSrc] = $jsData['size'];
        }
    }

    protected function parseIframes()
    {
        $iFrameNodes = $this->htmlDOM->getElementsByTagName('iframe');
        foreach ($iFrameNodes as $iFrameNode) {
            $iFrameSrc = $this->appendBaseURL($iFrameNode->getAttribute('src'));
            $this->iFrames[$iFrameSrc] = new Crawler($iFrameSrc);
        }
    }


    protected function getBaseURL()
    {
        if ($this->baseUrl === NULL) {
            $urlFragements = parse_url($this->url);
            $urlFormat = "%s://%s";
            if (! (empty($urlFragements['scheme']) || empty($urlFragements['host']))) {
                $this->baseUrl = sprintf($urlFormat, $urlFragements['scheme'], $urlFragements['host']);
            }
            if (! (empty($urlFragements['port']))) {
                $this->baseUrl = $this->baseUrl . ":" . $urlFragements['port'];
            }
        }

        return $this->baseUrl;
    }


    protected function appendBaseURL($fileLocation)
    {
        if (! $this->hasBaseURL($fileLocation)) {
            return $this->getBaseURL() . "/" . ltrim($fileLocation, "/");
        }

        return $fileLocation;
    }

    protected function hasBaseURL($fileLocation)
    {
        $urlFragements = parse_url($fileLocation);
        return ! empty($urlFragements['host']);
    }

    /**
     * @return array
     */
    public function getFailedRequestList()
    {
        return $this->failedUrls;
    }

    /**
     * @return array
     */
    public function getRedirectRequestList()
    {
        return $this->reDirects;
    }


    /**
     * @return null
     */
    public function getPageSize()
    {
        return $this->htmlPageSize;
    }

    /**
     * @return array
     * Returns the js list
     */
    public function getJSFilesList()
    {
        return $this->jsFiles;
    }

    /**
     * @return array
     * Returns the css list
     */
    public function getCssFilesList()
    {
        return $this->cssFiles;
    }

    /**
     * @return array
     * Returns the media list
     */
    public function getMediaFilesList()
    {
        return $this->mediaFiles;
    }


    /**
     * @return array
     * Returns the Iframe list
     */
    public function getIFrameList()
    {
        return $this->iFrames;
    }

}