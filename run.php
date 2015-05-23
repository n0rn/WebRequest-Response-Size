<?php
use App\Component\AutoLoader\Loader;
use App\Component\Exception\UserException;
use App\Component\Response;
use App\Classes\RestClient;
use App\Classes\Crawler;

require_once 'App/Component/AutoLoader/Loader.php';

error_reporting(E_ERROR | E_PARSE);

$autoloader = new Loader(__DIR__);
$autoloader->register();

// Initialzing the response array
$response = array(
    'errorCode' => - 1,
    'errorMessage' => "not attempted"
);

try {
    $options = getopt("", array(
        "url:"
    ));
    if (empty($options['url'])) {
        throw new UserException('Provide URL as input', "1001");
    }
    $crawler = new Crawler($options['url']);
    $responseObj = new Response($crawler);
    $response = $responseObj->getOutput();
} catch (\Exception $e) {
    $response['errorCode'] = 1001;
    if ($e instanceof UserException) {
        $response['errorMessage'] = $e->getMessage();
    } else {
        $response['errorMessage'] = 'Unexpected Error. Please try later';
    }
}

renderOutput($response);

function renderOutput($response)
{
    $greenColor = "[42m";
    $redColor = "[41m";
    if ($response['errorCode'] == 0) {
        echo "\n" . chr(27) . $greenColor . "Success" . chr(27) . "[0m\n";
        echo "\n" . "Total No of HTTP Request:" .chr(27).$greenColor.$response['totalCount']. chr(27) . "[0m\n";
        echo "\n" . "Total Download Size:" .chr(27).$greenColor.$response['totalSize']. chr(27) . "[0m\n";
        echo "\n" . "Total No of Failed Request: " .chr(27).$redColor.$response['failedRequest']. chr(27) . "[0m\n";
        echo "\n" . "Total No of Redirect Request: " .chr(27).$redColor.$response['redirectRequest']. chr(27) . "[0m\n";
    } else {
        echo "\n" . chr(27) . $redColor . "Failed with error - " . $response['errorMessage'] . chr(27) . "[0m\n";
    }
}