---
layout: default
title: Documentation
parent: Contributing
---

# Documentation

## Local Development

### Prerequisites

To preview your changes to the documentation before committing them, you can use jekyll, a static site generator.
You need to have ruby installed in order to use `bundle`:

```bash
# ruby-dev is required to build native headers
$ sudo apt install ruby-dev ruby-bundler

# check out the source
$ git clone git@github.com:elephox-dev/elephox-dev.github.io.git
$ cd elephox-dev.github.io

# install dependencies
$ bundle install
```

to install the dependencies. You only need to run this once to set jekyll up.

### Server

You will want to run this command to start watching for file changes and start a web server:

```bash
# *nix systems:
$ bundle exec jekyll serve --config _config.local.yml

# windows/wsl:
$ bundle exec jekyll serve --config _config.local.yml --force_polling
```

You can then go to [localhost:4000](http://localhost:4000/) to review your changes.

### Updates

You may need to update the bundle dependencies. You can do this by running:

```bash
$ bundle update
```
