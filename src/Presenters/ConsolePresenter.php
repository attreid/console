<?php

declare(strict_types=1);

namespace Attreid\Console\Presenters;

use Attreid\Console\Console;
use Attreid\Exceptions\Console\InvalidArgumentException;
use Nette\Application\UI\Presenter;
use Tracy\Debugger;

final class ConsolePresenter extends Presenter
{
	public function __construct(private readonly Console $console)
	{
		parent::__construct();
	}

	public function startup(): void
	{
		parent::startup();
		if (!$this->console->consoleMode() && Debugger::$productionMode) {
			$this->error();
		}
	}

	public function actionDefault(string $collection = null, string $command = null): void
	{
		try {
			$this->console->execute($collection, $command, $this->getParameters());
		} catch (InvalidArgumentException) {
			if ($this->console->consoleMode()) {
				$this->console->printLine("Command '$collection:$command' doesn't exist.");
			} else {
				$this->error();
			}
		}
		$this->terminate();
	}
}
