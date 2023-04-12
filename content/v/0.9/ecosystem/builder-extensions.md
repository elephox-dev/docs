<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Ecosystem</p>
    <p class="subtitle">Builder Extensions</p>
  </div>
</section>

<!---{? set title = "Builder Extensions @ Elephox" }-->

[toc]

---

> Builder extensions provide traits which add functionality to the application builder.

# Available Builder Extensions

- [Whoops](#whoops)
- [Request Logging](#request-logging)
- [Logtail](#logtail)
- [Doctrine](#doctrine)

## Whoops

For whoops, there exist two builder extensions: one for the `ConsoleApplicationBuilder` and one for the `WebApplicationBuilder`.

### Whoops Handler

> View on packagist: [`elephox/builder-whoops-handler`](https://packagist.org/packages/elephox/builder-whoops-handler)

### Whoops Middleware

> View on packagist: [`elephox/builder-whoops-middleware`](https://packagist.org/packages/elephox/builder-whoops-middleware)

## Request Logging

> View on packagist: [`elephox/builder-request-logging`](https://packagist.org/packages/elephox/builder-request-logging)

## Logtail

> View on packagist: [`elephox/builder-logtail`](https://packagist.org/packages/elephox/builder-logtail)

This package is an extension of the [Request Logging](#request-logging) package and adds a [Logtail](https://betterstack.com/logtail) client as a service.
The client implements the `LoggerInterface` and will thus be used to log requests in Logtail.

## Doctrine

> View on packagist: [`elephox/builder-doctrine`](https://packagist.org/packages/elephox/builder-doctrine)

[Doctrine](https://www.doctrine-project.org/projects/orm.html) is a popular database abstraction layer (DBAL) and Object-Relational-Mapper (ORM) for PHP.
This builder extension provides an implementation of the `EntityManagerProvider` interface, which is used by Doctrine tools to interact with databases.

To create the appropriate provider, Elephox uses your apps `config.json` files.
