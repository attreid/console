# Nette Cli console with web interface

Settings in **config.neon**
```neon
extensions:
    console: Atrreid\Console\DI\ConsoleExtension
```

available settings
```neon
console:
    prefix: cli
    collections:
        - ClassWithCommands
```

Add route
```php
class RouterFactory
    public function __construct(private readonly ConsoleRouterFactory $consoleRouterFactory)
	{
	}

	public function createRouter(): RouteList
	{
		$router = new RouteList;
		$this->consoleRouterFactory->createRoutes($router);
		// other routes
}
```

## Commands
```php
class ClassWithCommands extends CommandCollection {

    /**
     * Comment, show in help
     * @param string $variable comment 
     */
    public function command(string $variable): void {
        $this->printLine('Some info');
        // php code
    }
}
```

## Run
Run in console
```bash
php index.php ClassWithCommands:command /variable=value
```

or in browser with Tracy on
```
http://domain/cli/ClassWithCommands/command?variable=value
```