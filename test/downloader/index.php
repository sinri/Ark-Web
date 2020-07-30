<?php
/**
 * Created by PhpStorm.
 * User: sinri
 * Date: 2019-01-29
 * Time: 21:50
 */

use sinri\ark\io\ArkWebOutput;

require_once __DIR__ . '/../../vendor/autoload.php';

try {
    ArkWebOutput::getSharedInstance()->downloadFileIndirectly(__FILE__);
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}