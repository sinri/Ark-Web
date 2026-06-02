<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/TestHelper.php';

use sinri\ark\web\ArkRouter;
use sinri\ark\web\attribute\ClassForRoute;
use sinri\ark\web\attribute\MethodForRoute;
use sinri\ark\web\test\attribute\TestHelper;
use sinri\ark\web\test\attribute\controller\EmptyController;
use sinri\ark\web\test\attribute\controller\FreeTailController;
use sinri\ark\web\test\attribute\controller\MethodOnlyController;
use sinri\ark\web\test\attribute\controller\PrivateMethodController;
use sinri\ark\web\test\attribute\controller\WithFiltersController;
use sinri\ark\web\test\web\filter\TestFilter;

$controllerDir = __DIR__ . '/controller';
$namespaceBase = 'sinri\ark\web\test\attribute\controller\\';

// ============================================================
echo "=== ClassForRoute Unit Tests ===\n";
// ============================================================

echo "\n--- Default constructor ---\n";
$attr = new ClassForRoute();
TestHelper::assertEquals(['ANY'], $attr->getMethods(), "default method is ANY");
TestHelper::assertEquals('', $attr->path, "default path is empty");
TestHelper::assertEquals([], $attr->getFilters(), "default filters is empty");
TestHelper::assertFalse($attr->withFreeTail, "default withFreeTail is false");

echo "\n--- String method ---\n";
$attr = new ClassForRoute(method: 'GET');
TestHelper::assertEquals(['GET'], $attr->getMethods(), "string method 'GET'");

echo "\n--- Comma-separated method ---\n";
$attr = new ClassForRoute(method: 'GET,POST');
TestHelper::assertEquals(['GET', 'POST'], $attr->getMethods(), "comma separated methods");

echo "\n--- Array method ---\n";
$attr = new ClassForRoute(method: ['GET', 'POST', 'PUT']);
TestHelper::assertEquals(['GET', 'POST', 'PUT'], $attr->getMethods(), "array methods");

echo "\n--- String filters ---\n";
$attr = new ClassForRoute(filters: 'FilterA,FilterB');
TestHelper::assertEquals(['FilterA', 'FilterB'], $attr->getFilters(), "comma separated filters");

echo "\n--- Array filters ---\n";
$attr = new ClassForRoute(filters: ['FilterA', 'FilterB']);
TestHelper::assertEquals(['FilterA', 'FilterB'], $attr->getFilters(), "array filters");

echo "\n--- Empty string filter ---\n";
$attr = new ClassForRoute(filters: '');
TestHelper::assertEquals([], $attr->getFilters(), "empty filter string yields empty array");

// ============================================================
echo "\n=== MethodForRoute Unit Tests ===\n";
// ============================================================

echo "\n--- Default constructor ---\n";
$attr = new MethodForRoute();
TestHelper::assertEquals(['ANY'], $attr->getMethods(), "default method is ANY");
TestHelper::assertEquals('', $attr->path, "default path is empty");
TestHelper::assertEquals([], $attr->getFilters(), "default filters is empty");
TestHelper::assertFalse($attr->withFreeTail, "default withFreeTail is false");

echo "\n--- Array method ---\n";
$attr = new MethodForRoute(method: ['POST', 'DELETE']);
TestHelper::assertEquals(['POST', 'DELETE'], $attr->getMethods(), "array methods");

echo "\n--- Array filters ---\n";
$attr = new MethodForRoute(filters: [TestFilter::class]);
TestHelper::assertEquals([TestFilter::class], $attr->getFilters(), "array filters with class constant");

// ============================================================
echo "\n=== Route Registration: EmptyController ===\n";
// ============================================================
// #[ClassForRoute] with default path='' method='ANY'
// hello() has 0 params -> 1 route: ANY path='hello'

$router = new ArkRouter();
$router->loadRoutesWithAttributeInDirectory($controllerDir, $namespaceBase);
$allRules = $router->getListOfRouteRules();

$rules = TestHelper::findRulesByPathCallback($router, 'EmptyController::hello');
TestHelper::assertCount(1, $rules, "EmptyController::hello has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'ANY', "EmptyController::hello uses ANY method");
    TestHelper::assertRuleContains($rule, 'EmptyController', "callback references EmptyController");
}

// ============================================================
echo "\n=== Route Registration: FreeTailController ===\n";
// ============================================================
// ClassForRoute(method: 'GET', path: 'free/class', withFreeTail: true)
//   show() -> 1 route: GET 'free/class/show' (FreeTailRule)
// MethodForRoute(method: 'POST', path: 'free/method/{id}', withFreeTail: true)
//   store($id) -> 1 route: POST 'free/method/{id}' (FreeTailRule)

$rules = TestHelper::findRulesByPathCallback($router, 'FreeTailController::show');
TestHelper::assertCount(1, $rules, "FreeTailController::show has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'GET', "FreeTailController::show uses GET");
    TestHelper::assertRuleContains($rule, 'ArkRouterFreeTailRule', "FreeTailController::show is FreeTailRule");
}

$rules = TestHelper::findRulesByPathCallback($router, 'FreeTailController::store');
TestHelper::assertCount(1, $rules, "FreeTailController::store has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'POST', "FreeTailController::store uses POST");
    TestHelper::assertRuleContains($rule, 'ArkRouterFreeTailRule', "FreeTailController::store is FreeTailRule");
}

// ============================================================
echo "\n=== Route Registration: WithFiltersController ===\n";
// ============================================================
// ClassForRoute(method: 'GET', path: 'filtered/class', filters: [TestFilter::class])
//   list() -> 1 route: GET 'filtered/class/list' with TestFilter
// MethodForRoute(method: 'POST', path: 'filtered/method', filters: [TestFilter::class])
//   create() -> 1 route: POST 'filtered/method' with TestFilter

$rules = TestHelper::findRulesByPathCallback($router, 'WithFiltersController::list');
TestHelper::assertCount(1, $rules, "WithFiltersController::list has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'GET', "WithFiltersController::list uses GET");
    TestHelper::assertRuleContains($rule, 'A Test Filter', "WithFiltersController::list has TestFilter applied");
}

$rules = TestHelper::findRulesByPathCallback($router, 'WithFiltersController::create');
TestHelper::assertCount(1, $rules, "WithFiltersController::create has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'POST', "WithFiltersController::create uses POST");
    TestHelper::assertRuleContains($rule, 'A Test Filter', "WithFiltersController::create has TestFilter applied");
}

// ============================================================
echo "\n=== Route Registration: PrivateMethodController ===\n";
// ============================================================
// ClassForRoute(method: 'GET', path: 'private')
// visible() -> public, has MethodForRoute -> 1 route
// hidden() -> private, no MethodForRoute -> SKIPPED
// hiddenWithAttr() -> private, has MethodForRoute -> SKIPPED (not public)

$rules = TestHelper::findRulesByPathCallback($router, 'PrivateMethodController::visible');
TestHelper::assertCount(1, $rules, "PrivateMethodController::visible (public) has 1 route");

$rules = TestHelper::findRulesByPathCallback($router, 'PrivateMethodController::hidden');
TestHelper::assertCount(0, $rules, "PrivateMethodController::hidden (private, no attr) has NO routes");

$rules = TestHelper::findRulesByPathCallback($router, 'PrivateMethodController::hiddenWithAttr');
TestHelper::assertCount(0, $rules, "PrivateMethodController::hiddenWithAttr (private, with attr) has NO routes");

// ============================================================
echo "\n=== Route Registration: MethodOnlyController ===\n";
// ============================================================
// No ClassForRoute
// explicit() -> MethodForRoute -> 1 route
// ignored() -> no attrs -> SKIPPED
// multiMethod() -> MethodForRoute(['GET','POST']) -> 1 route

$rules = TestHelper::findRulesByPathCallback($router, 'MethodOnlyController::explicit');
TestHelper::assertCount(1, $rules, "MethodOnlyController::explicit has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'GET', "MethodOnlyController::explicit uses GET");
}

$rules = TestHelper::findRulesByPathCallback($router, 'MethodOnlyController::ignored');
TestHelper::assertCount(0, $rules, "MethodOnlyController::ignored (no attrs, no class attr) has NO routes");

$rules = TestHelper::findRulesByPathCallback($router, 'MethodOnlyController::multiMethod');
TestHelper::assertCount(1, $rules, "MethodOnlyController::multiMethod has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'GET|POST', "MethodOnlyController::multiMethod uses GET|POST");
}

// ============================================================
echo "\n=== Route Registration: WorkerAlpha (existing fixture) ===\n";
// ============================================================
// ClassForRoute(method: 'GET', path: 'annotation/alpha')
// go($p1='P1') -> has MethodForRoute(method: ['GET','POST'], path: 'annotation/alpha/go/{p1}') -> 1 route
// come0() -> 0 params, 0 required -> 1 route: GET 'annotation/alpha/come0'
// come1($p1) -> 1 param, 1 required -> 1 route: GET 'annotation/alpha/come1/{p1}'
// come2($p1,$p2) -> 2 params, 2 required -> 1 route: GET 'annotation/alpha/come2/{p1}/{p2}'
// come2a($p1,$p2='default2') -> 2 params, 1 required -> 2 routes
// come2b($p1='default1',$p2='default2') -> 2 params, 0 required -> 3 routes
// Total: 1+1+1+1+2+3 = 9

$workerAlphaDir = __DIR__ . '/../web/controller/annotation';
$workerAlphaNs = 'sinri\ark\web\test\web\controller\annotation\\';

$routerForWorker = new ArkRouter();
$routerForWorker->loadRoutesWithAttributeInDirectory($workerAlphaDir, $workerAlphaNs);
$totalWorkerRules = count($routerForWorker->getListOfRouteRules());
TestHelper::assertEquals(9, $totalWorkerRules, "WorkerAlpha generates 9 routes total");

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::go');
TestHelper::assertCount(1, $rules, "WorkerAlpha::go (MethodForRoute) has exactly 1 route");
if (!empty($rules)) {
    $rule = reset($rules);
    TestHelper::assertRuleContains($rule, 'GET|POST', "WorkerAlpha::go uses GET|POST from MethodForRoute");
}

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come0');
TestHelper::assertCount(1, $rules, "WorkerAlpha::come0 (0 params) has exactly 1 route");

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come1');
TestHelper::assertCount(1, $rules, "WorkerAlpha::come1 (1 required param) has exactly 1 route");

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come2');
$exactCome2 = array_filter($rules, fn($p) => str_ends_with($p, '::come2'));
TestHelper::assertCount(1, $exactCome2, "WorkerAlpha::come2 (2 required params) has 1 route");

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come2a');
TestHelper::assertCount(2, $rules, "WorkerAlpha::come2a (1 req, 1 optional) has 2 routes");

$rules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come2b');
TestHelper::assertCount(3, $rules, "WorkerAlpha::come2b (0 req, 2 optional) has 3 routes");

// Verify come2b route path variants (regex uses ([^\/]+) as capture groups)
$come2bRules = TestHelper::findRulesByPathCallback($routerForWorker, 'WorkerAlpha::come2b');
$come2bCaptureCounts = [];
foreach ($come2bRules as $pattern) {
    preg_match('#/\\^\\\\/.*\\$/#', $pattern, $regexMatch);
    $regexPart = $regexMatch[0] ?? '';
    $captureCount = substr_count($regexPart, '([^\\/]+)');
    $come2bCaptureCounts[] = $captureCount;
}
TestHelper::assert(in_array(0, $come2bCaptureCounts), "come2b: route with no params (0 capture groups) exists");
TestHelper::assert(in_array(1, $come2bCaptureCounts), "come2b: route with one param (1 capture group) exists");
TestHelper::assert(in_array(2, $come2bCaptureCounts), "come2b: route with two params (2 capture groups) exists");

// ============================================================
echo "\n=== Deprecated Alias: loadRoutesWithAnnotationInDirectory ===\n";
// ============================================================

$routerDeprecated = new ArkRouter();
$routerDeprecated->loadRoutesWithAnnotationInDirectory($workerAlphaDir, $workerAlphaNs);
$totalDeprecated = count($routerDeprecated->getListOfRouteRules());
TestHelper::assertEquals(9, $totalDeprecated, "Deprecated alias produces same 9 routes as loadRoutesWithAttributeInDirectory");

// ============================================================
echo "\n=== Total Route Count Sanity Check ===\n";
// ============================================================
// All controllers loaded from test/attribute/controller/
// EmptyController: 1
// FreeTailController: 2 (show + store)
// WithFiltersController: 2 (list + create)
// PrivateMethodController: 1 (visible only)
// MethodOnlyController: 2 (explicit + multiMethod)
// Total: 8

$totalRoutes = count($allRules);
TestHelper::assertEquals(8, $totalRoutes, "All controllers produce 8 total routes");

// ============================================================
exit(TestHelper::printSummary());