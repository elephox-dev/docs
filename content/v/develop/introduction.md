<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Introduction</p>
  </div>
</section>

<!---{? set title = "Introduction @ Elephox" }-->

# Welcome to Elephox!

Elephox is a general-purpose framework that can be used for a wide range of applications.
However, it is particularly well-suited for web APIs and scripting applications.
It provides a set of core modules that form the foundation for all other modules.

One such module is the `elephox/collections` module, which provides common implementations for different use cases like "ArrayMap", "ArrayList", "ObjectSet", "ObjectMap", "ArraySet".
These implementations are optimized for different use cases, but they all implement `GenericEnumerable` and `GenericKeyedEnumerable`, respectively.

Another important module is the `elephox/di` (`di` = dependency injection) module, which provides a container for registering services and factories for services.
The container can automatically determine the required parameters for a constructor or other method and provide them, as long as they have been registered first.
It also manages the lifetimes of services, which can be "singleton", "scoped", or "transient".

# Web

Elephox provides some basic middlewares to handle static files and exceptions.
Middlewares are added to the request pipeline when building the application using the `PipelineBuilder` class.
Routing is done by calling `addRouting()` on the web application builder and then subsequently calling `addRoutesFromClass(MyProjectController::class)`, which will look for routing attributes.

A basic controller might look like this:

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use Elephox\Http\Contract\ResponseBuilder;
use Elephox\Http\Response;
use Elephox\Web\Routing\Attribute\Http\Delete;
use Elephox\Web\Routing\Attribute\Http\Get;

#[Controller('blog')]
readonly class BlogController
{
    public function __construct(
        private Db $myDb;
    ) {}

    #[Get('posts')]
    public function getPosts(): ResponseBuilder
    {
        $posts = $this->myDb->get('posts')->toArray();

        return Response::build()->ok()->jsonBody($posts);
    }

    #[Delete('posts/{id:int}')]
    public function deletePost(int $id): ResponseBuilder
    {
        // TODO: authorization

        $this->myDb->delete('posts', $id);

        return Response::build()->ok();
    }
}
```

It demonstrates how a controller can be used to handle HTTP requests and access a database through a dependency injected object.

It also uses Elephox's attribute routing system, which is a convenient way to map routes to controller actions.
The `#[Get]` and `#[Delete]` attributes define the HTTP method and the URL pattern for each method, respectively.

Finally, the `#[Controller]` attribute is used to specify the base URL path for all the routes in the controller.

# CLI

To create a custom CLI command, implement the `CommandHandler` interface, which requires two methods to be implemented: `configure(CommandTemplateBuilder $builder): void` and `handle(CommandInvocation $command): int|null`.
The configure method is used to set up the command, while the handle method is used to execute it.

Here's an example of a simple command that echoes back a message:

```php
<?php
declare(strict_types=1);

namespace App\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;

use function ctype_digit;

class EchoCommand implements CommandHandler
{
	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->setName('echo');
		$builder->setDescription('Echo a message');
		$builder->addArgument('message', description: 'The message to echo');
		$builder->addOption('repeat', 'r', '1', description: 'Repeat the message this many times', validator: static fn (mixed $v) => !is_array($v) && ctype_digit((string) $v));
	}

	public function handle(CommandInvocation $command): int|null
	{
		for ($i = 0; $i < $command->options->get('repeat')->value; $i++) {
			echo $command->arguments->get('message')->value . PHP_EOL;
		}

		return 0;
	}
}
```

This example shows how to use the `CommandTemplateBuilder` to define the command's name, description, arguments, and options, as well as how to access these values in the `handle` method using the `CommandInvocation` object.

# Deployment

As Elephox is still in development, there are no established best practices for building and deploying Elephox applications.
However, common practices for PHP applications can be followed for deployment.
