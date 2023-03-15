<?php

declare(strict_types=1);

namespace Attreid\Console;

use Nette\Application\Response;

abstract class CommandCollection
{
	private readonly Console $console;

	public final function setConsole(Console $console): void
	{
		$this->console = $console;
	}

	protected final function printLine(string $string): void
	{
		$this->console->printLine($string);
	}

	protected function sendResponse(Response $response): void
	{
		$this->console->sendResponse($response);
	}
}
