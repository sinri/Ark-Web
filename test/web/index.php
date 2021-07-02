<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:18
 */

use Psr\Log\LogLevel;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkWebService;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsCallback;
use sinri\ark\web\implement\ArkRouterFreeTailRule;
use sinri\ark\web\test\web\controller\Foo;
use sinri\ark\web\test\web\controller\FreeTailController;
use sinri\ark\web\test\web\controller\PureAutoRestFul\JustSingleController;
use sinri\ark\web\test\web\filter\AnotherFilter;
use sinri\ark\web\test\web\filter\TestFilter;

require_once __DIR__ . '/../../vendor/autoload.php';

//\sinri\ark\web\ArkWebSession::sessionStart(__DIR__.'/sessions');

date_default_timezone_set("Asia/Shanghai");

$logger = new ArkLogger(__DIR__ . '/../log', 'web');
$logger->setIgnoreLevel(LogLevel::DEBUG);
$logger->setGroupByPrefix(true);
$logger->removeCurrentLogFile();

$web_service = ArkWebService::getSharedInstance();//Ark()->webService();
$web_service->setDebug(true);
$web_service->setLogger($logger);
$router = $web_service->getRouter();
$router->setDebug(true);
$router->setLogger($logger);

$router->setErrorHandler(new class extends ArkRouteErrorHandlerAsCallback
{

    /**
     * @param mixed $error
     * @param int $httpCode
     */
    public function requestErrorCallback($error, $httpCode)
    {
        //Ark()->webOutput()
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader('application/json')
            ->json(['message' => $error, 'code' => $httpCode]);
    }
});

$router->get("getDocument/{doc_id}/page/{page_id}", function ($docId, $pageId) {
    echo "GET DOC {$docId} PAGE {$pageId}" . PHP_EOL;
});
$router->loadAllControllersInDirectoryAsCI(
    __DIR__ . '/controller',
    '',
    'sinri\ark\web\test\web\controller\\',
    [
        TestFilter::class,
        AnotherFilter::class,
        //'no_such_filter',//this might cause error
    ]
);


$router->get("", function () use ($logger) {
    $logger->info("Homepage Requested");
    echo "Welcome to Ark!" . PHP_EOL;
    echo "Check static/frontend for url test cases" . PHP_EOL;
});

// Note: if you use http://xxxx.com/static/frontend without tail `/`
// the `frontend` would not be treated as folder but a file,
// so you should rewrite this in Nginx in front of PHP
$router->registerFrontendFolder("static/frontend", __DIR__ . '/frontend', []);

//$autoRoute = new ArkRouterAutoRestfulRule(
//    ArkWebInput::METHOD_ANY,
//    'auto_router/',
//    'sinri\ark\test\web\controller',
//    []
//);
//$router->registerRouteRule($autoRoute);

$router->loadAutoRestfulControllerRoot('auto_router/', 'sinri\ark\web\test\web\controller', []);

// Fix Bug: http://localhost/phpstorm/Ark/test/web/PureAutoRestFulController/api
$router->loadAutoRestfulControllerRoot('', 'sinri\ark\web\test\web\controller\PureAutoRestFul', []);

$router->loadAutoRestfulControllerRoot(
    'single/',
    JustSingleController::class
);

$freeTailRouteRule1 = new ArkRouterFreeTailRule(
    [ArkWebInput::METHOD_ANY],
    "free/tail/{a}/{b}",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(Foo::class, 'tail')
);

$router->registerRouteRule($freeTailRouteRule1);

$freeTailRouteRule2 = new ArkRouterFreeTailRule(
    [ArkWebInput::METHOD_ANY],
    "freeTail",
    ArkRouterFreeTailRule::buildCallbackDescriptionWithClassNameAndMethod(FreeTailController::class, 'handlePath')
);

$router->registerRouteRule($freeTailRouteRule2);

$web_service->setupFileSystemViewer("fs", __DIR__ . '/../', [], function ($file, $components) {
    echo "Target File: " . ($file) . PHP_EOL;
    echo "Path Components: " . json_encode($components) . PHP_EOL;
});

$listOfRouteRules = $router->getListOfRouteRules();
foreach ($listOfRouteRules as $index => $listOfRouteRule) {
    $logger->info("[RULE " . ($index + 1) . "]" . $listOfRouteRule);
}

$router->get('test-another-filter', function () {
    echo __FILE__ . '@' . __LINE__ . PHP_EOL . json_encode((ArkWebService::getSharedInstance())->getSharedData()) . PHP_EOL;
}, [AnotherFilter::class]);

$web_service->handleRequest();

// call http://localhost/phpstorm/Ark/test/web/