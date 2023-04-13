<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Getting Started</p>
  </div>
</section>

<!---{? set title = "Getting Started @ Elephox" }-->

[toc]

---

# Prerequisites

Before you can start developing an Elephox application, ensure that you meet the following requirements:

- PHP version 8.2 or higher is installed and configured correctly.
- Composer version 2 or higher is installed.
- You have a text editor, such as [VS Code](https://code.visualstudio.com/) or a PHP IDE, like [PhpStorm](https://www.jetbrains.com/phpstorm/).
- Access to a shell or terminal is required.

Optional prerequisites:

- [`git`](https://git-scm.com/)

# Creating a new project

You have two options for starting a new Elephox project: you can either use the Elephox application template or start from scratch.

## Using the Elephox application template

To create a new Elephox project using the application template, invoke the composer create-project command:

```shell
composer create-project elephox/elephox my-app
```

Composer will clone the latest version of [elephox/elephox](https://github.com/elephox-dev/elephox) into a new folder named `my-app`.

You can now edit the composer.json file to change the name of your project and optionally adjust the license, type, etc.

The application template includes the following:

- The `phox` binary, which provides a CLI for your application.
- A `public` folder with an `index.php` file for providing a web application.
- Some boilerplate services and routes in the `src` folder.

Using the application template can help you get up and running faster.

## Starting from scratch

You can also start a new Elephox project from scratch.

To do so, create a new folder, navigate to it, and run composer init:

```bash
mkdir my-project
cd my-project
composer init
```

Composer will prompt you to enter information about your project, such as its name, type, and license.
You can also add `elephox/framework` as a dependency to your project.

After composer has finished, you need to decide which entry point your project should have.

### CLI Entry Point

The CLI entry point is used for command-line applications that need to parse commands, arguments, and options.
You can build your own commands or load commands from vendors.

### Web Entrypoint

The web entry point is used for APIs and web applications that require a request pipeline.
You can use middlewares and routing to build your application.
