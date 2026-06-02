<?php

namespace sinri\ark\web\test\attribute;

use sinri\ark\web\ArkRouter;
use sinri\ark\web\ArkRouterRule;
use sinri\ark\web\exception\ArkRouteNoMatchedException;

class TestHelper
{
    private static int $passCount = 0;
    private static int $failCount = 0;

    public static function assert(bool $condition, string $message): void
    {
        if ($condition) {
            self::$passCount++;
            echo "  [PASS] {$message}\n";
        } else {
            self::$failCount++;
            echo "  [FAIL] {$message}\n";
        }
    }

    public static function assertContains(string $haystack, string $needle, string $message): void
    {
        self::assert(str_contains($haystack, $needle), $message . " (expected to contain: '{$needle}')");
    }

    public static function assertNotContains(string $haystack, string $needle, string $message): void
    {
        self::assert(!str_contains($haystack, $needle), $message . " (expected NOT to contain: '{$needle}')");
    }

    public static function assertEquals($expected, $actual, string $message): void
    {
        self::assert($expected === $actual, $message . " (expected: " . var_export($expected, true) . ", got: " . var_export($actual, true) . ")");
    }

    public static function assertCount(int $expected, array $array, string $message): void
    {
        $actual = count($array);
        self::assert($expected === $actual, $message . " (expected count: {$expected}, got: {$actual})");
    }

    public static function assertTrue(bool $value, string $message): void
    {
        self::assert($value === true, $message);
    }

    public static function assertFalse(bool $value, string $message): void
    {
        self::assert($value === false, $message);
    }

    public static function findRulesByPathCallback(ArkRouter $router, string $callbackSubstring): array
    {
        $matched = [];
        foreach ($router->getListOfRouteRules() as $index => $pattern) {
            if (str_contains($pattern, $callbackSubstring)) {
                $matched[$index] = $pattern;
            }
        }
        return $matched;
    }

    public static function findRuleByPathRegex(ArkRouter $router, string $pathRegexFragment): ?string
    {
        foreach ($router->getListOfRouteRules() as $pattern) {
            if (str_contains($pattern, $pathRegexFragment)) {
                return $pattern;
            }
        }
        return null;
    }

    public static function assertRuleContains(string $rulePattern, string $substring, string $context): void
    {
        self::assert(
            str_contains($rulePattern, $substring),
            "{$context}: rule contains '{$substring}'\n    rule: {$rulePattern}"
        );
    }

    public static function seekRouteSafe(ArkRouter $router, string $method, string $path): ?ArkRouterRule
    {
        try {
            return $router->seekRoute($path, $method);
        } catch (ArkRouteNoMatchedException) {
            return null;
        }
    }

    public static function printSummary(): int
    {
        $total = self::$passCount + self::$failCount;
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Results: " . self::$passCount . " passed, " . self::$failCount . " failed, {$total} total\n";
        echo str_repeat("=", 60) . "\n";

        if (self::$failCount > 0) {
            echo "SOME TESTS FAILED!\n";
            return 1;
        }
        echo "ALL TESTS PASSED!\n";
        return 0;
    }
}