<?php
namespace App\Component;

use App\Classes\Crawler;

/**
 * Class to process Output from Crawler object
 * @author vignesh
 */
class Response
{

    /**
     * Property to hold crawler object
     * from which resposne data has to be formed
     *
     * @var Crawler
     */
    protected $crawler;

    /**
     * Property to hold the response data
     *
     * @var array
     */
    protected $response = array(
        'totalSize' => 0,
        'totalCount' => 0,
        'failedRequest' => 0,
        'redirectRequest' => 0,
        'errorCode' => 0,
        'errorMessage' => ''
    );

    /**
     * Property hold to have iframe files list
     *
     * @var array
     */
    protected $iFrameList = array();

    /**
     * Property hold to have css files list
     *
     * @var array
     */
    protected $cssList = array();

    /**
     * Property hold to have js files list
     *
     * @var array
     */
    protected $jsList = array();

    /**
     * Property hold to have media files list
     *
     * @var array
     */
    protected $mediaList = array();

    /**
     * Property hold to have failed request list
     *
     * @var array
     */
    protected $failedRequestList = array();

    /**
     * Property hold to have recdirects list
     *
     * @var array
     */
    protected $redirectRequestList = array();

    /**
     * Initializes the response object with Crawler object
     *
     * @param Crawler $crawler            
     */
    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Process the base Crawler object and calculates the output format
     * @return \App\Component\array
     */
    public function getOutput()
    {
        $this->processCrawler($this->crawler)->processFiles();
        
        return $this->response;
    }

    /**
     * Process the Individual Crawler object and calculates the output format
     * @return \App\Component\array
     */
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

    /**
     * Process all the individual files such as medias, css, js
     * @return \App\Component\Response
     */
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

    /**
     * Process all the iframes
     * @return \App\Component\Response
     */
    protected function processIFrames(Crawler $crawler)
    {
        foreach ($crawler->getIFrameList() as $iFrameSrc => $iFrame) {
            $this->processCrawler($iFrame);
        }
        
        return $this;
    }

    /**
     * Process all the redirect request
     * @return \App\Component\Response
     */
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

    /**
     * Process all the failed request
     * @return \App\Component\Response
     */
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
