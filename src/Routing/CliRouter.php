<?php

declare(strict_types=1);

namespace Attreid\Console\Routing;

use Nette\Application\Helpers;
use Nette\Http\IRequest;
use Nette\Http\UrlScript;
use Nette\Routing\Router;
use Nette\SmartObject;

final class CliRouter implements Router
{
	use SmartObject;

	const
		COMMAND_KEY = 'command',
		COLLECTION_KEY = 'collection';

	public function constructUrl(array $params, UrlScript $refUrl): ?string
	{
		return null;
	}

	public function match(IRequest $httpRequest): ?array
	{
		if (empty($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
			return null;
		}

		$names = [self::COMMAND_KEY];
		$params = ['action' => 'default'];
		$args = $_SERVER['argv'];
		array_shift($args);

		foreach ($args as $arg) {
			$opt = preg_replace('#/|-+#A', '', $arg);
			if ($opt === $arg) {
				if (isset($flag) || $flag = array_shift($names)) {
					$params[$flag] = $arg;
				} else {
					$params[] = $arg;
				}
				$flag = null;
				continue;
			}

			if (isset($flag)) {
				$params[$flag] = true;
				$flag = null;
			}

			if ($opt !== '') {
				$pair = explode('=', $opt, 2);
				if (isset($pair[1])) {
					$params[$pair[0]] = $pair[1];
				} else {
					$flag = $pair[0];
				}
			}
		}

		@list($collection, $command) = Helpers::splitName($params[self::COMMAND_KEY] ?? '');
		if (empty($collection)) {
			$collection = $command;
			$command = null;
		}

		$params[self::COLLECTION_KEY] = $collection;
		$params[self::COMMAND_KEY] = $command;
		$params['presenter'] = 'Console:Console';

		return $params;
	}
}