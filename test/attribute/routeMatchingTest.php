<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestHelper.php';

use sinri\ark\web\ArkRouter;
use sinri\ark\web\ArkRouterRule;
use sinri\ark\web\exception\ArkRouteNoMatchedException;
use sinri\ark\web\test\attribute\TestHelper;

$controllerDir = __DIR__ . '/controller';
$namespaceBase = 'sinri\ark\web\test\attribute\controller\\';

$router = new ArkRouter();
$router->loadRoutesWithAttributeInDirectory($controllerDir, $namespaceBase);

// ============================================================
echo "=== FreeTail Route Matching ===\n";
// ============================================================

// FreeTailController::show -> GET /^\/free\/class\/show\/?(.*)$/
$rule = TestHelper::seekRouteSafe($router, 'GET', '/free/class/show');
TestHelper::assert($rule !== null, "/free/class/show matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'FreeTailController::show'),
    "/free/class/show resolves to FreeTailController::show"
);

$rule = TestHelper::seekRouteSafe($router, 'GET', '/free/class/show/extra/tail');
TestHelper::assert($rule !== null, "/free/class/show/extra/tail matches a free-tail route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'FreeTailController::show'),
    "/free/class/show/extra/tail resolves to FreeTailController::show"
);

$rule = TestHelper::seekRouteSafe($router, 'POST', '/free/class/show');
TestHelper::assert($rule === null, "/free/class/show does NOT match on POST");

// FreeTailController::store -> POST /^\/free\/method\/([^\/]+)\/?(.*)$/
$rule = TestHelper::seekRouteSafe($router, 'POST', '/free/method/42');
TestHelper::assert($rule !== null, "/free/method/42 matches a route on POST");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'FreeTailController::store'),
    "/free/method/42 resolves to FreeTailController::store"
);

$rule = TestHelper::seekRouteSafe($router, 'GET', '/free/method/42');
TestHelper::assert($rule === null, "/free/method/42 does NOT match on GET");

$rule = TestHelper::seekRouteSafe($router, 'POST', '/free/method/42/extra');
TestHelper::assert($rule !== null, "/free/method/42/extra matches free-tail route on POST");

// ============================================================
echo "\n=== Filtered Route Matching ===\n";
// ============================================================

// WithFiltersController::list -> GET /^\/filtered\/class\/list$/
$rule = TestHelper::seekRouteSafe($router, 'GET', '/filtered/class/list');
TestHelper::assert($rule !== null, "/filtered/class/list matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'WithFiltersController::list'),
    "/filtered/class/list resolves to WithFiltersController::list"
);

$rule = TestHelper::seekRouteSafe($router, 'POST', '/filtered/class/list');
TestHelper::assert($rule === null, "/filtered/class/list does NOT match on POST");

// WithFiltersController::create -> POST /^\/filtered\/method$/
$rule = TestHelper::seekRouteSafe($router, 'POST', '/filtered/method');
TestHelper::assert($rule !== null, "/filtered/method matches a route on POST");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'WithFiltersController::create'),
    "/filtered/method resolves to WithFiltersController::create"
);

// ============================================================
echo "\n=== Private Method Route Matching ===\n";
// ============================================================

// PrivateMethodController::visible -> GET /^\/private\/visible$/
$rule = TestHelper::seekRouteSafe($router, 'GET', '/private/visible');
TestHelper::assert($rule !== null, "/private/visible matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'PrivateMethodController::visible'),
    "/private/visible resolves to PrivateMethodController::visible"
);

$hidden = TestHelper::seekRouteSafe($router, 'GET', '/private/hidden');
TestHelper::assert($hidden === null, "/private/hidden does NOT match any route (private method)");

$hiddenAttr = TestHelper::seekRouteSafe($router, 'GET', '/private/hidden-attr');
TestHelper::assert($hiddenAttr === null, "/private/hidden-attr does NOT match any route (private method with attr)");

// ============================================================
echo "\n=== Method-Only Controller Matching ===\n";
// ============================================================

// MethodOnlyController::explicit -> GET /^\/method-only\/explicit$/
$rule = TestHelper::seekRouteSafe($router, 'GET', '/method-only/explicit');
TestHelper::assert($rule !== null, "/method-only/explicit matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'MethodOnlyController::explicit'),
    "/method-only/explicit resolves to MethodOnlyController::explicit"
);

$rule = TestHelper::seekRouteSafe($router, 'POST', '/method-only/explicit');
TestHelper::assert($rule === null, "/method-only/explicit does NOT match on POST");

// MethodOnlyController::multiMethod -> GET|POST /^\/method-only\/multi$/
$rule = TestHelper::seekRouteSafe($router, 'GET', '/method-only/multi');
TestHelper::assert($rule !== null, "/method-only/multi matches on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'MethodOnlyController::multiMethod'),
    "/method-only/multi resolves to MethodOnlyController::multiMethod on GET"
);

$rule = TestHelper::seekRouteSafe($router, 'POST', '/method-only/multi');
TestHelper::assert($rule !== null, "/method-only/multi matches on POST");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'MethodOnlyController::multiMethod'),
    "/method-only/multi resolves to MethodOnlyController::multiMethod on POST"
);

$rule = TestHelper::seekRouteSafe($router, 'DELETE', '/method-only/multi');
TestHelper::assert($rule === null, "/method-only/multi does NOT match on DELETE");

// MethodOnlyController::ignored was never registered
$ignored = TestHelper::seekRouteSafe($router, 'GET', '/method-only/ignored');
TestHelper::assert($ignored === null, "/method-only/ignored does NOT match any route");

// ============================================================
echo "\n=== Parameterized Route Matching (WorkerAlpha) ===\n";
// ============================================================

$routerWorker = new ArkRouter();
$routerWorker->loadRoutesWithAttributeInDirectory(
    __DIR__ . '/../web/controller/annotation',
    'sinri\ark\web\test\web\controller\annotation\\'
);

// go() -> GET|POST /^\/annotation\/alpha\/go\/([^\/]+)$/
$rule = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/go/xyz');
TestHelper::assert($rule !== null, "/annotation/alpha/go/xyz matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'WorkerAlpha::go'),
    "/annotation/alpha/go/xyz resolves to WorkerAlpha::go"
);
TestHelper::assert(
    $rule !== null && $rule->getParsed() === ['xyz'],
    "/annotation/alpha/go/xyz extracts parameter 'xyz'"
);

$rule = TestHelper::seekRouteSafe($routerWorker, 'POST', '/annotation/alpha/go/abc');
TestHelper::assert($rule !== null, "/annotation/alpha/go/abc matches a route on POST");

// come0() -> GET /^\/annotation\/alpha\/come0$/
$rule = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come0');
TestHelper::assert($rule !== null, "/annotation/alpha/come0 matches a route on GET");
TestHelper::assert(
    $rule !== null && str_contains($rule->getRulePattern(), 'WorkerAlpha::come0'),
    "/annotation/alpha/come0 resolves to WorkerAlpha::come0"
);

// come1($p1) -> GET /^\/annotation\/alpha\/come1\/([^\/]+)$/
$rule = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come1/hello');
TestHelper::assert($rule !== null, "/annotation/alpha/come1/hello matches a route on GET");
TestHelper::assert(
    $rule !== null && $rule->getParsed() === ['hello'],
    "/annotation/alpha/come1/hello extracts parameter 'hello'"
);

// come2a($p1, $p2='default2') -> two rules
$rule1Param = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come2a/x');
TestHelper::assert($rule1Param !== null, "/annotation/alpha/come2a/x matches (1 param variant)");
TestHelper::assert(
    $rule1Param !== null && $rule1Param->getParsed() === ['x'],
    "/annotation/alpha/come2a/x extracts ['x']"
);

$rule2Param = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come2a/x/y');
TestHelper::assert($rule2Param !== null, "/annotation/alpha/come2a/x/y matches (2 param variant)");
TestHelper::assert(
    $rule2Param !== null && $rule2Param->getParsed() === ['x', 'y'],
    "/annotation/alpha/come2a/x/y extracts ['x', 'y']"
);

// come2b($p1='default1', $p2='default2') -> three rules
$rule0 = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come2b');
TestHelper::assert($rule0 !== null, "/annotation/alpha/come2b matches (0 param variant)");
TestHelper::assert(
    $rule0 !== null && $rule0->getParsed() === [],
    "/annotation/alpha/come2b extracts no params"
);

$rule1 = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come2b/a');
TestHelper::assert($rule1 !== null, "/annotation/alpha/come2b/a matches (1 param variant)");

$rule2 = TestHelper::seekRouteSafe($routerWorker, 'GET', '/annotation/alpha/come2b/a/b');
TestHelper::assert($rule2 !== null, "/annotation/alpha/come2b/a/b matches (2 param variant)");
TestHelper::assert(
    $rule2 !== null && $rule2->getParsed() === ['a', 'b'],
    "/annotation/alpha/come2b/a/b extracts ['a', 'b']"
);

// ============================================================
echo "\n=== seekRoute Exception for Non-Matched Routes ===\n";
// ============================================================

$thrown = false;
try {
    $router->seekRoute('/nonexistent/path', 'GET');
} catch (ArkRouteNoMatchedException $e) {
    $thrown = true;
}
TestHelper::assertTrue($thrown, "seekRoute throws ArkRouteNoMatchedException for non-existent path");

$thrown = false;
try {
    $router->seekRoute('/filtered/class/list', 'DELETE');
} catch (ArkRouteNoMatchedException $e) {
    $thrown = true;
}
TestHelper::assertTrue($thrown, "seekRoute throws ArkRouteNoMatchedException for wrong HTTP method");

$thrown = false;
try {
    $routerWorker->seekRoute('/annotation/alpha/go', 'GET');
} catch (ArkRouteNoMatchedException $e) {
    $thrown = true;
}
TestHelper::assertTrue($thrown, "seekRoute throws ArkRouteNoMatchedException when required param is missing");

$thrown = false;
try {
    $routerWorker->seekRoute('/annotation/alpha/come2/x/y/z', 'GET');
} catch (ArkRouteNoMatchedException $e) {
    $thrown = true;
}
TestHelper::assertTrue($thrown, "seekRoute throws ArkRouteNoMatchedException for too many params on exact-match route");

// ============================================================
exit(TestHelper::printSummary());