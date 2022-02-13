<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Building a To-Do-App</p>
    <p class="subtitle">A step-by-step introduction to Elephox</p>
  </div>
</section>

<!---{? set title = "Building a To-Do-App @ Elephox" }-->

[toc]

---

> Make sure you read [Getting Started]({?qualify:/getting-started}) before you start.

# What we will build

In this guide, we will build a simple to-do-list application.
It will provide some API endpoints to create, read, update and delete to-do items.
Data is exchanged using JSON and stored in an sqlite database.

The point of this project is to show how to build a simple web API application using Elephox, hence the simplicity of the project itself.

# Creating the application

## Creating the project structure

At the beginning, we need to create a directory structure to organize our files.
You can (and should) use `composer create-project elephox/elephox <project name>` to do this.

Composer will ask you a few questions and will then create a directory structure like this:

```
<project name>/
├── public/
│    └── index.php
├── src/
│    └── App.php
├── vendor/
│    └── ...
├── .gitignore
├── composer.json
├── composer.lock
├── .gitignore
├── README.md
└── bootstrap.php
```

Composer stores the project's dependencies in the `vendor` directory.
The `composer.json` file contains the project's dependencies with the Elephox framework already installed.

Your project code will be in the `src` directory, although you are free to organize your project files however you want.

## Creating the database service

The first thing we will take care of is to create a service to communicate with our database.

`TODO: finish guide`

## Creating the endpoints

# Testing the application

# Deploying the application

# Extra: adding console commands
