<?php

declare(strict_types=1);

namespace Attreid\Console\DI;

use Attreid\Console\Console;
use Attreid\Console\Routing\RouterFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use ReflectionClass;

class ConsoleExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'consoleMode' => Expect::string()->default('%consoleMode%'),
			'prefix' => Expect::string()->default('cli'),
			'collections' => Expect::array()->default([]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$this->config->consoleMode = Helpers::expand($this->config->consoleMode, $builder->parameters);

		$console = $builder->addDefinition($this->prefix('console'))
			->setType(Console::class)
			->setArguments([$this->config->consoleMode, $this->config->prefix]);

		$builder->addDefinition($this->prefix('router'))
			->setType(RouterFactory::class)
			->setArguments([$this->config->consoleMode, $this->config->prefix]);

		foreach ($this->config->collections as $collection) {
			$commandCollection = $builder->addDefinition($this->prefix('collection' . $this->getShortName($collection)))
				->setType($this->getClass($collection))
				->setFactory($this->getClass($collection), $collection instanceof Statement ? $collection->arguments : []);
			$console->addSetup('addCommandCollection', [$commandCollection]);
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('application.presenterFactory')
			->addSetup('setMapping', [
				['Console' => 'Attreid\Console\Presenters\*Presenter']
			]);
	}

	private function getClass(Statement|string $class): string
	{
		if ($class instanceof Statement) {
			return $class->getEntity();
		} else {
			return $class;
		}
	}

	private function getShortName(Statement|string $class): string
	{
		$classType = new ReflectionClass($this->getClass($class));
		return $classType->getShortName();
	}

}
