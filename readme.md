# Nette Cli console with web interface

**Requirements:** PHP 8.4+

## Settings in **config.neon**
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
    public function __construct(private readonly \Attreid\Console\Routing\RouterFactory $consoleRouterFactory)
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

## Development / Docker

Build and run the PHP 8.4 CLI container:

```bash
docker compose up -d
docker compose exec php composer install
docker compose exec php composer test
docker compose exec php php index.php ClassWithCommands:command /variable=value
```

One-off run (e.g. install and test without keeping container):

```bash
docker compose run --rm php composer install
docker compose run --rm php composer test
```

## Tests

- **V Dockeru (doporučeno):**

```bash
docker compose run --rm php composer test
docker compose run --rm php composer test:coverage
```

- **Lokálně (pokud máš PHP 8.4 + Composer):**
  - Spuštění testů: `composer test`
  - Spuštění testů s coverage: `composer test:coverage` (vygeneruje `coverage.html`)