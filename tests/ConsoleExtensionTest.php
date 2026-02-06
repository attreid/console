<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Attreid\Console\Console;
use Attreid\Console\DI\ConsoleExtension;
use Nette\DI\Compiler;
use Nette\DI\ContainerLoader;
use Tester\Assert;

$loader = new ContainerLoader(sys_get_temp_dir() . '/console-nette-tests');
$className = $loader->load(function (Compiler $compiler): void {
    $compiler->addConfig([
        'parameters' => [
            'consoleMode' => true,
        ],
        'services' => [
            'application.presenterFactory' => \Nette\Application\PresenterFactory::class,
        ],
    ]);
    $compiler->addExtension('console', new ConsoleExtension());
    $compiler->addConfig(['console' => ['collections' => []]]);
});

Assert::type('string', $className);
Assert::true(class_exists($className));

$container = new $className();
Assert::type(\Nette\DI\Container::class, $container);
Assert::type(Console::class, $container->getByType(Console::class));
