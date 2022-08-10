<?php

declare(strict_types=1);

namespace Attreid\Console;

use Attreid\Exceptions\Console\InvalidArgumentException;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

final class Console
{
	/** @var CommandCollection[] */
	private array $collections = [];

	public function __construct(private readonly bool $consoleMode, private readonly string $prefix)
	{
	}

	public function consoleMode(): bool
	{
		return $this->consoleMode;
	}

	public function addCommandCollection(CommandCollection $collection): void
	{
		$collection->setConsole($this);
		$class = new ReflectionClass($collection);
		$this->collections[$class->getShortName()] = $collection;
	}

	public function execute(string $collection = null, string $command = null, array $args = []): void
	{
		if (isset($this->collections[$collection])) {
			$class = new ReflectionClass($this->collections[$collection]);
		} else {
			$class = null;
		}

		if ($command === null) {
			$this->printHelp($class);
			return;
		} elseif ($class !== null && $class->hasMethod($command)) {
			$method = $class->getMethod($command);
			if ($method->isPublic() && !$method->isAbstract() && !$method->isStatic()) {
				try {
					$this->printTime($class->getShortName() . ' => ' . $method->name);

					$parameters = [];
					foreach ($method->getParameters() as $param) {
						if (isset($args[$param->name])) {
							$parameters[$param->name] = $args[$param->name];
						}
					}

					$method->invokeArgs($this->collections[$collection], $parameters);
					$this->printTime($method->name . ' done');
					return;
				} catch (ReflectionException) {
				}
			}
		}
		throw new InvalidArgumentException;
	}

	private function printTime(string $text): void
	{
		$line = '[' . date('d.m.Y H:i:s', time()) . '] ' . $text;
		$this->printLine($line);
	}

	private function printHelp(ReflectionClass $class = null): void
	{
		$printHelp = function (ReflectionClass $class) {
			if ($this->consoleMode) {
				$this->printConsoleHelp($class);
			} else {
				$this->printHtmlHelp($class);
			}
		};

		if ($class === null) {
			foreach ($this->collections as $collection) {
				$printHelp(new ReflectionClass($collection));
			}
		} else {
			$printHelp($class);
		}
	}

	private function printConsoleHelp(ReflectionClass $class): void
	{
		$reflectionComment = function (ReflectionClass|ReflectionMethod $reflection): string {
			$text = $reflection->getDocComment() ?: '';
			$text = Strings::replace($text, '/\s+/', ' ');
			$text = Strings::replace($text, '/^\/\*\* \* /', '');
			$text = Strings::replace($text, '/ \*\/$/', '');

			return "\t\t ($text)";
		};

		$this->printLine($class->getShortName() . $reflectionComment($class));
		$this->printLine();

		foreach ($this->getMethod($class) as $method) {
			$line = $class->getShortName() . ':' . $method->name;

			foreach ($method->getParameters() as $param) {
				$line .= ' /' . $param->getName();
				if ($param->isDefaultValueAvailable()) {
					$line .= '=' . $this->getValue($param->getDefaultValue());
				}
			}

			$this->printLine($line . $reflectionComment($method));
		}
		$this->printLine();
	}

	private function printHtmlHelp(ReflectionClass $class): void
	{
		$reflectionComment = function (ReflectionClass|ReflectionMethod $reflection): string {
			return Strings::replace($reflection->getDocComment() ?: '', '/(\ |\t)+/', ' ');
		};

		$this->printLine(
			Html::el('pre')
				->setAttribute('style', 'margin: 0')
				->setText($reflectionComment($class))
			.
			Html::el('h1')
				->setAttribute('style', 'margin: 0')
				->setText($class->getShortName())
		);

		$link = function (ReflectionClass $class, ReflectionMethod $method): string {
			$args = [];
			$params = [];
			foreach ($method->getParameters() as $param) {
				$p = $param->getName();
				if ($param->isDefaultValueAvailable()) {
					$p .= '=' . $this->getValue($param->getDefaultValue());
				} else {
					$args [] = $param->getName() . '=';
				}
				$params[] = $p;
			}

			$link = str_replace(':', '/', $this->prefix . '/' . $class->getShortName()) . '/' . $method->name;

			return Html::el('a')
					->setHtml($method->name)
					->href('/' . $link . ($args ? '?' . implode('&', $args) : '')) .
				($params ? (' (' . implode(', ', $params) . ')') : '');
		};
		foreach ($this->getMethod($class) as $method) {
			$this->printLine(Html::el('pre')
					->setAttribute('style', 'margin-bottom: 0px')
					->setText(
						$reflectionComment($method)) .
				$link($class, $method)
			);
		}
	}

	private function getValue(mixed $value): string
	{
		return match ($value) {
			default => $value,
			null => 'null',
			true => 'true',
			false => 'false',
		};
	}

	/** @return ReflectionMethod[] */
	private function getMethod(ReflectionClass $class): array
	{
		$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC & ~ReflectionMethod::IS_PROTECTED);
		$result = [];
		foreach ($methods as $method) {
			if (!Strings::startsWith($method->name, '__') && $method->name !== 'setConsole') {
				$result[] = $method;
			}
		}
		return $result;
	}

	public function printLine(bool|string|Html $string = null): void
	{
		if ($string) {
			echo $string;
		}
		if ($this->consoleMode) {
			echo "\n";
		} else {
			echo '<br/>';
		}
	}
}
