<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Getting Started</p>
  </div>
</section>

<!---{? set title = "Getting Started @ Elephox" }-->

[toc]

---

# Prerequisites

To be able to develop an Elephox application, you will need at least:

- A working PHP 8.1 (or 8.2) installation
- A working Composer 2 installation
- A text editor (like [VS Code](https://code.visualstudio.com/)) or PHP IDE (like [PhpStorm](https://www.jetbrains.com/phpstorm/))
- Access to a shell/terminal

Optional prerequisites:

- [`git`](https://git-scm.com/)

# Creating a new project

To create a new project, use the following command:

```shell
composer create-project elephox/elephox myApp
```

Composer will then clone the latest version of [elephox/elephox](https://github.com/elephox-dev/elephox) into a new folder named `myApp`.

In case you want to use `git` for version control, now is a good moment for the initial commit.

Edit the `composer.json` to change the name to your projects name and optionally adjust license, type, etc.
