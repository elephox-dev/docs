<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Contributing</p>
    <p class="subtitle">Workflow</p>
  </div>
</section>

<!---{? set title = "Workflow @ Elephox" }-->

[toc]

---

# Setting up git

# git workflow

# Version management

| branch        | corresponding series | aliases                       | example tag |
|---------------|:--------------------:|-------------------------------|-------------|
| `main`        |        `1.x`         | `next-major`                  | `1.0.0`     |
| `develop`     |       `0.4.x`        | `next-minor`                  | `0.4.0`     |
| `release-0.3` |       `0.3.x`        | `latest-minor`, `only-bugfix` | `0.3.25`    |
| `release-0.2` |       `0.2.x`        | `last-minor`, `only-security` | `0.2.13`    |

In case of a bugfix commit:

```mermaid
graph LR
  03x(0.3.x) --merge--> 04x
  04x(0.4.x) --merge--> 1x(1.x)
```

In case of a security fix commit:

```mermaid
graph LR
  02x(0.2.x) --merge--> 03x
  03x(0.3.x) --merge--> 04x
  04x(0.4.x) --merge--> 1x(1.x)
```

```mermaid
gantt
  title Example Version Support
  dateFormat YYYY-MM-DD

  section Elephox 0.x
  Development           :        d0-0, 2021-11-01, 2023-02-01

  section Elephox 1.0
  Features              :active, f1-0, after d0-0, 90d
  Bugfixes              :        b1-0, after d0-0, 180d
  Security Fixes        :crit,   s1-0, after d0-0, 270d

  section Elephox 1.1
  Features              :active, f1-1, after f1-0, 90d
  Bugfixes              :        b1-1, after f1-0, 180d
  Security Fixes        :crit,   s1-1, after f1-0, 270d

  section Elephox 1.2
  Features              :active, f1-2, after f1-1, 90d
  Bugfixes              :        b1-2, after f1-1, 180d
  Security Fixes        :crit,   s1-2, after f1-1, 270d

  section Elephox 2.0
  Features              :active, f2-0, after f1-2, 180d
  Bugfixes              :        b2-0, after f1-2, 270d
  Security Fixes        :crit,   s2-0, after f1-2, 365d
```
