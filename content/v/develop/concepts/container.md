<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Concepts</p>
    <p class="subtitle">Container</p>
  </div>
</section>

<!---{? set title = "Container @ Elephox" }-->

[toc]

---

<article class="message is-info">
  <div class="message-body">
    The service collection is also available as its own independent package: <a href="https://packagist.org/packages/elephox/di" target="_blank">elephox/di</a>
  </div>
</article>

# The Problem

Imagine you want to create a simple application that can be used to manage todos.

You create several classes, each implementing a part of the application.
Say you created the following classes:

- `TodoItem`: represents a single todo item
- `TodoItemRepository`: manages all todo items in the database (CRUD operations)
- `TodoItemController`: handles API requests for todo items
- `TodoItemValidator`: validates incoming data and checks if they can be used for creating a new todo item

Each class has its own responsibilities as well as their own dependencies.
For example:

- The `TodoItemController` needs access to a `TodoItemRepository` instance to be able to create, delete and modify todos.
  - the controllers' responsibility is to check incoming requests (checking a given API token for example)
- The `TodoItemRepository` might need to validate data for creating a new todo item, hence it will need access to a `TodoItemValidator` instance.
- The repository can also make use of an external ORM implementation, to decouple itself from a specific database implementation.
- Let's imagine the `TodoItemValidator` also has an external dependency for parsing time.
- Let's also assume you want to follow best-practices:
  - don't use any static methods/members if possible
  - decouple classes from each other (don't assume a class has a specific external dependency)

If we look at these constraints, we can see a dependency graph forming:

```
┌──────────────────────┬──────────────┐
│                      │              │
│     Todo App         │   external   │
│                      │              │
├──────────────────────┼──────────────┤
│                      │              │
│  TodoItemController  │              │
│         │            │              │
│         │            │              │
│         ▼            │              │
│  TodoItemRepository──┼──►ORM        │
│         │            │              │
│         │            │              │
│         ▼            │              │
│  TodoItemValidator───┼──►TimeLib    │
│                      │              │
└──────────────────────┴──────────────┘
```

Creating this kind of overview is helpful for understanding an applications structure and discover hidden dependencies.
One such hidden dependency is the path from `TodoItemController` to `TimeLib`:

- `TodoItemController` needs an instance of `TodoItemRepository`
- `TodoItemRepository` needs an instance of `TodoItemValidator`
- `TodoItemValidator` needs an instance of `TimeLib`

Now, if you want to create an instance of your controller class, you will need to create a `TimeLib` instance first and hand it down the graph through every class depending on it!
This can become quite cumbersome in larger applications:

```php
// imagine creating a controller instance like this:
$controller = new TodoItemController(
    new TodoItemRepository(
        new ORM(),
        new TodoItemValidator(
            new TimeLib()
        )
    )
);
```

Now imagine the dependency graph changing and new external dependencies being added.
Or your external dependencies' dependencies being changed.
You'd have to adjust every instantiation of each class having hidden/direct dependencies.
Ludicrous!

# The Solution

To solve this problem, let's introduce the concept of a `Dependency Injection Container`, or `Container` for short.

A `Container` is like a builder collecting manuals:

- you can give them a new manual
- you can ask the builder if they have a specific manual
- you can ask them to build something according to a specific manual
- you can give them a few materials you already have and ask them to build something new with them
- if they need to build something complex and need to build something else first, they can use a manual they already have

In this analogy, the manuals are classes and what is being built are objects.

- you can tell the service collection to "register" a specific class, so the service collection knows how to build it
- if the service collection needs an instance of another class to build what you requested, it can look up the "manual" to build that class first
- in case the service collection doesn't know how to build a class, you can provide them with an instance you already built

This works by using `Reflection`.
The code can basically look at itself and analyse things like parameter type hints, return types and object properties.
When you ask the service collection to build a class instance, the service collection looks at the constructor arguments and tries to build an instance of each parameter.

---

Applying all this to our example app, we can use the service collection like this:

```php
use Elephox\DI\ServiceCollection;

$services = new ServiceCollection();
$services->register(TimeLib::class);
$services->register(ORM::class);
$services->register(TodoItemValidator::class);
$services->register(TodoItemRepository::class);

$controller = $services->getOrInstantiate(TodoItemController::class);
```

Now you only have to have the service collection instance to care about and it will take care of the rest.

# Registering a callback

To influence how the service collection builds an object, you can pass a callback to the register method, which gets invoked when an instance of the registered class is requested:

```php
use Elephox\DI\ServiceCollection;

$services = new ServiceCollection();
$services->register(TimeLib::class, function (Container $c) {
    $timezoneProvider = $c->get(TimeZonesLib::class);
    $timezoneProvider->setDefault('Europe/Berlin');

    return new TimeLib($timezoneProvider);
});
```

# Service Lifetime

The service collection keeps a reference to each object it created and returns it when the same class is requested another time.

You can of course influence this behaviour when registering a class:

```php
use Elephox\DI\ServiceCollection;
use Elephox\DI\ServiceLifetime;

$services = new ServiceCollection();
$services->register(TimeLib::class, lifetime: ServiceLifetime::Transient);
```

Currently, you can only choose between `ServiceLifetime::Singleton` and `ServiceLifetime::Transient`.
Singleton of course means there should only ever be one instance of the class within the service collection and that same instance is always returned when the class is requested.
Transient means a new instance will be created every time a class is requested.

# Aliases

While developing, you might want to change a concrete implementation of a class and haven't used an interface to request it from the service collection.
Now you have to update every `->get()` call to request the new implementation.

To prevent this, you can add an `alias` for classes.
An alias doesn't need to be a valid class name.
It can be any string you want (except the empty string):

```php
use Elephox\DI\ServiceCollection;
use Elephox\DI\ServiceLifetime;

$services = new ServiceCollection();
$services->register(TimeLib::class, aliases: 'time-parser');

// then request it like you would normally:
$services->get('time-parser');
```

<div class="message is-warning">
  <div class="message-header">
    <p>Attention</p>
  </div>
<div class="message-body" markdown="1">

The alias takes precedence if the service collection has multiple options for injecting a parameter. Aliases are resolved by the
parameter name.

</div>
</div>

# Parameter Injection

The service collection implements functions allowing you to call any callback, method or constructor by analyzing the required parameters and trying to provide them.

## Class Instantiation

You can use the service collection to instantiate objects for you.
This can be helpful if you don't want to or can't provide constructor parameters for a given class:

```php
use Elephox\DI\ServiceCollection;
use Elephox\DI\ServiceLifetime;

// somewhere in your code...
$services = new ServiceCollection();
$services->register(TimeLib::class);

// TestClass.php
class TestClass {
    public function __construct(private TimeLib $timeLib) {}
}

// somewhere else in your code...
$testClassInstance = $services->instantiate(TestClass::class);
```

## Callbacks & Function Invocation

```php
use Elephox\DI\ServiceCollection;
use Elephox\DI\ServiceLifetime;

// somewhere in your code...
$services = new ServiceCollection();
$services->register(TimeLib::class);

// somewhere else....
$callback = function (TimeLib $timeLib) {
    // do something with the TimeLib instance
}

$services->callback($callback);

// or use the service collection to call methods for you, injecting the required parameters

class TestClass {
    public function needsTimeLib(TimeLib $timeLib) {
        // do something with the TimeLib instance
    }
}

// use your own instance...
$testClass = new TestClass();
$services->call($testClass, 'needsTimeLib');

// ...or let the service collection create one for you and call the method
$services->call(TestClass::class, 'needsTimeLib');
```
