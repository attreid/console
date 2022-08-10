<?php

declare(strict_types=1);

namespace Attreid\Console\Routing;

use Nette\Application\Routers\RouteList;
use Tracy\Debugger;

final class RouterFactory
{
	public function __construct(private readonly bool $consoleMode, private readonly string $prefix)
	{
	}

	public function createRoutes(RouteList $router): void
	{
		if ($this->consoleMode) {
			$router->add(new CliRouter());
		}

		if (!Debugger::$productionMode) {
			$router->addRoute($this->prefix . '[/<collection>][/<command>]', 'Console:Console:default');
		}
	}
}