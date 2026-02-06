<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Attreid\Console\Routing\RouterFactory;
use Nette\Application\Routers\RouteList;
use Tester\Assert;

Tracy\Debugger::$productionMode = true;

$router = new RouteList();
$factory = new RouterFactory(true, 'cli');
$factory->createRoutes($router);

Assert::type(RouteList::class, $router);

Tracy\Debugger::$productionMode = false;

$router2 = new RouteList();
$factory2 = new RouterFactory(true, 'cli');
$factory2->createRoutes($router2);

Assert::type(RouteList::class, $router2);
