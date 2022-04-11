<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Concepts</p>
    <p class="subtitle">Contexts & Handlers</p>
  </div>
</section>

<!---{? set title = "Contexts & Handlers @ Elephox" }-->

[toc]

---

# How data flows in Elephox

To understand what contexts and handlers are, we first need to take a step back and look at how Elephox handles data in general.

```
┌─────────────────────────────────────────┬────────────────────────┐
│                                         │                        │
│               Elephox                   │        Your App        │
│                                         │                        │
├─────────────────────────────────────────┼────────────────────────┤
│                                         │                        │
│                 Core◄───────────────────┼────────index.php       │
│                  │                      │                        │
│         ┌────────┴─────────┐            │                        │
│         │                  │            │                        │
│         ▼                  ▼            │                        │
│  RequestContext     CommandLineContext  │                        │
│         │                  │            │                        │
│         └────────┬─────────┘            │                        │
│                  │                      │                        │
│                  ▼                      │                        │
│ HandlerContainer->findHandler($context) │                        │
│                  │                      │                        │
│                  │                      │                        │
│                  ▼                      │                        │
│          $handler->handle()─────────────┼──►Services/Controllers │
│                                         │                        │
└─────────────────────────────────────────┴────────────────────────┘
```

The entrypoint to your application is always the `index.php`.
It is supposed to register the composer autoloader and Create the Elephox `Core` instance.
Once you hand off handling of whatever invoked the `index.php` via `Core::handleGlobal()`, Elephox tries to gather as much information as possible in a _context_.

Through the `Core` instance, you are able to register your app classes.
Once a class is registered, it is scanned for _handlers_.
A handler can be a method within a class, decorated with a `HandlerAttribute` or an invokable class (implementing `__invoke`).

Within `handleGlobal`, the `HandlerContainer` tries to find possible handlers for a given context.
Once a handler is found, the handler - which usually resides in your application source - is invoked.
Since every service, including the request, is registered in the [`Container`]({?qualify:/concepts/container}), the handlers parameters are injected when it is called.
If the handler is a method in a clas, the class constructor also has access to everything registered in the container before the class was instantiated.

# RequestHandler

When developing an API or a whole web application, you will need to create handlers for the `RequestContext`.
This context holds information about the current request, like the requested URL, uploaded files, server parameters (like `$_ENV`, `$_SESSION`, etc.) and the matched URL template.

Registering a request context handler, all you need to do is decorate a method or class using the `RequestHandler` attribute:

```php
use Elephox\Core\Handler\Attribute\RequestHandler;
use Elephox\Http\Contract\Message;
use Elephox\Http\RequestMethod;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Elephox\Stream\StringStream;

class ApiController {
    #[RequestHandler('/version', methods: [RequestMethod::GET])]
    public function handleVersion(): Message
    {
        return Response::build()
          ->responseCode(ResponseCode::OK)
          ->body(new StringStream('1.0'))
          ->get();
    }
}
```

To make registering request context handlers as easy as possible, there are attributes for all request methods available:

```php
use Elephox\Core\Handler\Attribute\Http;

class ApiController {
    #[Http\Get('/version')]
    public function handleVersion(): Message { ... }
    
    #[Http\Post('/user')]
    public function createUser(Request $request): Message { ... }
    
    #[Http\Delete('/user')]
    public function deleteUser(Request $request): Message { ... }
    
    #[Http\Put('/user')]
    public function updateUser(Request $request): Message { ... }
    
    #[Http\Patch('/user')]
    public function partiallyUpdateUser(Request $request): Message { ... }
    
    #[Http\Head('/auth')]
    public function getHeaderAuthInfo(Request $request): Message { ... }
    
    #[Http\Options('/')]
    public function handleOptions(Request $request): Message { ... }
    
    #[Http\Any('/')]
    public function handleAny(): Message { ... }
}
```

You can also add multiple request handlers to one method:

```php
class ApiController {
    #[Get('/user')]
    #[Post('/user')]
    #[Put('/user')]
    #[Patch('/user')]
    #[Delete('/user')]
    public function handleUser(Request $request): Message { ... }
    
    // ...this is equivalent to...
    
    #[RequestHandler(
        '/user',
        methods: [
            RequestMethod::GET,
            RequestMethod::POST,
            RequestMethod::PUT,
            RequestMethod::PATCH,
            RequestMethod::DELETE
        ]
    )]
    public function handleUser(Request $request): Message { ... }
}
```

# CommandHandler

In case your `index.php` gets invoked from command line, `Core` creates a `CommandLineContext`.
To handle this context, you need to attach a `CommandHandler` to your classes/methods:

```php
use Elephox\Core\Handler\Attribute\CommandHandler;
use Elephox\Core\Context\Contract\CommandLineContext;

class Commands {
    #[CommandHandler('cleanup')]
    public function handleCleanup(CommandLineContext $context): int
    {
        // clean stuff up...

        return 0; // successful exit code
    }
}
```

The value you return from a `CommandHandler` determines the exit code.
If you return an integer between 0 and 255 (inclusive), it will dictate the exact exit code to use.
Otherwise, the exit code will be 0 if no exception occurs.

# EventHandler

`// TODO: complete`

# ExceptionHandler

Applications will inevitably throw exceptions.
When this happens, it would be nice to be able to handle them the way we want.
Registering an `ExceptionHandler` provides you with this ability.
You can register handlers for specific exceptions or a superclass of multiple exceptions.
The handler can also access all values in the container, including of course the `ExceptionContext` which holds the thrown exception object.

```php
use Elephox\Core\Handler\Attribute\ExceptionHandler;
use Elephox\Core\Context\Contract\ExceptionContext;

#[ExceptionHandler]
class GlobalExceptionHandler {
    public function __invoke(ExceptionContext $context): void
    {
        echo "Oh no! An exception occurred: " . $context->getException()->getMessage() . PHP_EOL;
    }
}
```

Since you can access the container, you can also request the original context in which the exception occurred:

```php
use Elephox\Core\Context\Contract\RequestContext;
use Elephox\Core\Context\Contract\CommandLineContext;
use Elephox\Core\Context\Contract\EventContext;

#[ExceptionHandler]
class GlobalExceptionHandler {
    public function __invoke(
        ExceptionContext $context,
        ?RequestContext $requestContext,
        ?CommandLineContext $commandLineContext,
        ?EventContext $eventContext
    ): void
    {
        $previousContext = match (true) {
            $requestContext !== null => $requestContext,
            $commandLineContext !== null => $commandLineContext,
            $eventContext !== null => $eventContext,
        };
    
        echo "Oh no! An exception occurred in the " . $previousContext::class . " context: " . $context->getException()->getMessage() . PHP_EOL;
    }
}
```
