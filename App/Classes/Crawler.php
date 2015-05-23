<?php
namespace App\Classes;

/**
 * Class to crawl the HTML elements and parse the various elements
 *
 * @author vignesh
 */
class Crawler
{

    /**
     * Holds the HTML Dom element
     *
     * @var \DOMDocument
     */
    protected $htmlDOM;

    /**
     * Propety to hold the Media File's size information
     *
     * @var array
     */
    protected $mediaFiles = array();

    /**
     * Propety to hold the CSS File's size information
     *
     * @var array
     */
    protected $cssFiles = array();

    /**
     * Propety to hold the JS File's size information
     *
     * @var array
     */
    protected $jsFiles = array();

    /**
     * Propety to hold the iFrames size information
     *
     * @var array
     */
    protected $iFrames = array();

    /**
     * Propety to hold the iFrames size information
     *
     * @var array
     */
    protected $reDirects = array();

    /**
     * Property to hold the URL of the page
     *
     * @var string
     */
    protected $url = NULL;

    /**
     * Property to get base domain for the URL
     *
     * @var string
     */
    protected $baseUrl = NULL;

    /**
     * Size of the page
     *
     * @var float
     */
    protected $htmlPageSize = NULL;

    /**
     * Get Failed http request
     *
     * @var array
     */
    protected $failedURLs = array();
    
    /**
     * Initializing the Crawler class with the URL
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $this->getData($this->url, FALSE);
    }

    /**
     * Method to get the data from URL and parse the data
     * 
     * @return array
     */
    protected function getData($url, $sizeOnly = TRUE)
    {
        if ($url == NULL) {
            return;
        }
        
        $response = RestClient::getInstance()->Call($url);
        
        if ($response['isError']) {
            $this->failedURLs[$url] = $response;
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

    /**
     * Parses the media tags in the given HTML document 
     */
    protected function parseMediaFiles()
    {
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('img'));
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('object'));
        $this->proceeMediaFiles($this->htmlDOM->getElementsByTagName('embed'));

    }
    
    /**
     * Process the media data
     * @param \DOMNodeList $mediaNodes
     */
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

    /**
     * Parses the css tags in the given HTML document and
     * process the css data
     */
    protected function parseCSSFiles()
    {
        $cssNodes = $this->htmlDOM->getElementsByTagName('link');
        foreach ($cssNodes as $cssNodes) {
            $cssSrc = $this->appendBaseURL($cssNodes->getAttribute('href'));
            $cssData = $this->getData($cssSrc, TRUE);
            if (empty($cssData)) {
                continue;
            }
            $this->cssFiles[$cssSrc] = $cssData['size'];
        }
    }

    /**
     * Parses the js tags in the given HTML document and
     * process the js data
     */
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
    
    /**
     * Parses the iframes tags in the given HTML document and
     * process the iframe data and again parses the html in the IFrames
     */
    protected function parseIframes()
    {
        $iFrameNodes = $this->htmlDOM->getElementsByTagName('iframe');
        foreach ($iFrameNodes as $iFrameNode) {
            $iFrameSrc = $this->appendBaseURL($iFrameNode->getAttribute('src'));
            $this->iFrames[$iFrameSrc] = new Crawler($iFrameSrc);
        }
    }
    
    /**
     * Get the base URL from the given html page
     */
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

    /**
     * Method to append base URL to the CSS, Media File, JS File.
     * Base URL will not be appended If the File has different base URL
     * or same URL already
     */
    protected function appendBaseURL($fileLocation)
    {
        if (! $this->hasBaseURL($fileLocation)) {
            return $this->getBaseURL() . "/" . ltrim($fileLocation, "/");
        }
        
        return $fileLocation;
    }

    /**
     * Method to check if the file name has base URL appended
     *
     * @param string $fileLocation            
     */
    protected function hasBaseURL($fileLocation)
    {
        $urlFragements = parse_url($fileLocation);
        return ! empty($urlFragements['host']);
    }
    
    /**
     * Returns the IFrame list
     * @return \App\Classes\array
     */
    public function getJSFilesList()
    {
        return $this->jsFiles;
    }
    
    /**
     * Returns the IFrame list
     * @return \App\Classes\array
     */
    public function getCssFilesList()
    {
        return $this->cssFiles;
    }
    
    /**
     * Returns the CSS list
     * @return \App\Classes\array
     */
    public function getMediaFilesList()
    {
        return $this->mediaFiles;
    }
    
    /**
     * Returns the IFrame list
     * @return \App\Classes\array
     */
    public function getIFrameList()
    {
        return $this->iFrames;
    }
    
    /**
     * Returns the failed list
     * @return \App\Classes\array
     */
    public function getFailedRequestList()
    {
        return $this->failedURLs;
    }
    
    /**
     * Returns the failed list
     * @return \App\Classes\array
     */
    public function getRedirectRequestList()
    {
        return $this->reDirects;
    }
    
    /**
     * Returns the HTML page size
     * 
     * @return \App\Classes\float
     */
    public function getPageSize()
    {
        return $this->htmlPageSize;    
    }
}