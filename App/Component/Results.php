<?php
/**
 * Created by PhpStorm.
 * User: Norn
 * Date: 2/20/2019
 * Time: 10:20 AM
 */

namespace App\Component;


use App\Source\Crawler;

class Results
{

    protected $crawler;

    protected $response = [
        'totalSize' => 0,
        'totalCount' => 0,
        'failedRequest' => 0,
        'redirectRequest' => 0,
        'errorCode' => 0,
        'errorMessage' => ''
    ];

    protected $iFrameList = [];

    protected $cssList = [];

    protected $jsList = [];

    protected $mediaList = [];

    protected $failedRequestList = [];

    protected $redirectRequestList = [];


    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function getOutput()
    {
        $this->processCrawler($this->crawler)->processFiles();

        return $this->response;
    }

    protected function processCrawler($crawler)
    {
        $this->mediaList = array_merge($this->mediaList, $crawler->getMediaFilesList());

        $this->cssList = array_merge($this->cssList, $crawler->getCssFilesList());

        $this->jsList = array_merge($this->jsList, $crawler->getJSFilesList());

        $this->iFrameList = array_merge($this->iFrameList, $crawler->getIFrameList());

        $this->failedRequestList = array_merge($this->failedRequestList, $crawler->getFailedRequestList());

        $this->redirectRequestList = array_merge($this->redirectRequestList, $crawler->getRedirectRequestList());

        $this->response['totalCount'] ++;
        $this->response['totalSize'] += $crawler->getPageSize();

        return $this->processIFrames($crawler)
            ->processRedirectRequestList($crawler)
            ->processFailedRequest($crawler);
    }

    protected function processFiles()
    {
        $processFileNames = array(
            'mediaList',
            'cssList',
            'jsList'
        );

        foreach ($processFileNames as $processFileName) {
            if (isset($this->{$processFileName}) && is_array($this->{$processFileName})) {
                foreach ($this->{$processFileName} as $fileName => $size) {
                    $this->response['totalCount'] ++;
                    $this->response['totalSize'] += $size;
                }
            }
        }

        return $this;
    }

    protected function processIFrames(Crawler $crawler)
    {
        foreach ($crawler->getIFrameList() as $iFrameSrc => $iFrame) {
            $this->processCrawler($iFrame);
        }

        return $this;
    }

    protected function processRedirectRequestList(Crawler $crawler)
    {
        foreach ($crawler->getRedirectRequestList() as $redirectRequest) {
            if ($redirectRequest instanceof Crawler) {
                $this->processCrawler($redirectRequest);
            } else {
                $this->response['totalCount'] ++;
                $this->response['redirectRequest'] ++;
                $this->response['totalSize'] += $redirectRequest['size'];
            }
        }

        return $this;
    }

    protected function processFailedRequest(Crawler $crawler)
    {
        foreach ($this->failedRequestList as $failedRequest) {
            $this->response['totalCount'] ++;
            $this->response['totalSize'] += $failedRequest['size'];
            $this->response['failedRequest'] ++;
        }

        return $this;
    }


}