<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Ecosystem</p>
    <p class="subtitle">Plane</p>
  </div>
</section>

<!---{? set title = "Plane @ Elephox" }-->

[toc]

---

> Plane is a wrapper script for `docker-compose`.
> It has some useful commands to aid you during the development of your Elephox applications and enables you to move your Elephox app to a containerized environment.

# Installation

To install Plane, add a requirement using `composer`:

```bash
composer require elephox/plane
```

Afterwards, add the Plane commands to your `phox` binary:

```php
#!/usr/bin/env php
<?php

// ...

$builder->commands->loadFromNamespace("Elephox\\Plane\\Commands");

// ...
```

Then you can run the following command to generate your `docker-compose.yml`:

```bash
php phox plane:install
```

# Usage

To invoke Plane commands, use the script located at `vendor/bin/plane`:

```bash
# list running processes
vendor/bin/plane ps
```

If you execute `plane` without any arguments, you will get a list of all possible commands and a short description for each.
The commands you will use the most often are `plane up -d` and `plane down`.
These commands will start the app and shut it down along with all its services.
