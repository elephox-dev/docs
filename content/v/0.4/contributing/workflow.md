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

1. Install git from their official website: [git-scm.com/downloads](https://git-scm.com/downloads)
2. If you haven't already, create a new GitHub user: [github.com/signup](https://github.com/signup)
3. Set up your name and email address in git (they will be used for commits and pull requests):

```
$ git config --global user.name "Your Name"
$ git config --global user.email "Your Email"
```

# Issue workflow

If you have been assigned an issue, you have to use the following workflow:

1. Create a branch for your issue. Prefix the branch name with `issue/` and the issue number.  
   For example if you have an issue with number `123`, the branch name will be `issue/123`.  
   The base for your branch depends on the issue type:

3. Do your commits on this branch and push then regularly. Fix any issues that come up in the CI pipeline.
4. Open a pull request on GitHub, from your issue branch into the `develop` branch.
5. Once the PR is reviewed, merge it. The issue should then be closed.
6. Afterwards, a new version can be published

An example of how your development might look like:

```mermaid
graph LR
  subgraph develop
    a((A))-->b((B))-->c((C))-->d((D))-->e((E))
  end
  subgraph issue/123
    x((X))-->y((Y))
  end
  b==new branch==>x
  y==merge==>d
```

# Version management

| branch        | corresponding series | aliases  | example tag |
|---------------|:--------------------:|----------|-------------|
| `develop`     |       `1.4.x`        | `next`   | `1.4-RC1`   |
| `release/1.3` |       `1.3.x`        | `latest` | `1.3.25`    |
| `release/1.2` |       `1.2.x`        | `-`      | `1.2.13`    |

When a pull request is merged into branch `release/1.3`, the tag `1.3.25` must be created.
Subsequently, the branch `release/1.3` must be merged into `develop`.

```mermaid
graph LR
  subgraph develop ["develop (future 1.4.x)"]
    developA((A))-->developB((B))-->developC((C))-->developD((D))-->developE((E))
  end
  subgraph release/1.3 ["release/1.3 (all 1.3.x)"]
    release-13A((H))-->release-13B((I))-->release-13C((J))-->release-13D((K))
  end
  release-13C--merge-->developD
  subgraph bugfix-13 ["bugfix for 1.3"]
    bugfix-13A((L))-->bugfix-13B((M))
  end
  release-13B-->bugfix-13A
  bugfix-13B--merge-->release-13C
  subgraph feature-14 ["feature (future 1.4.x)"]
    feature-14A((F))-->feature-14B((G))
  end
  developB-->feature-14A
  feature-14B--merge-->developC
```

```mermaid
gantt
  title Elephox Version Support
  dateFormat YYYY-MM-DD
  axisFormat %Y-%m

  section Elephox 0.x
  Development           :active, d0-0, 2021-11-01, 2023-02-01

  section Elephox 1.0
  Release 1.0           :milestone, m1-0, after d0-0, 0
  Features              :active,    f1-0, after d0-0, 90d
  Fixes                 :crit,      b1-0, after d0-0, 180d

  section Elephox 1.1
  Release 1.1           :milestone, m1-1, after f1-0, 0
  Features              :active,    f1-1, after f1-0, 90d
  Fixes                 :crit,      b1-1, after f1-0, 180d

  section Elephox 1.2
  Release 1.2           :milestone, m1-2, after f1-1, 0
  Features              :active,    f1-2, after f1-1, 180d
  Fixes                 :crit,      b1-2, after f1-1, 270d

  section Elephox 2.0
  Release 2.0           :milestone, m2-0, after f1-2, 0
  Features              :active,    f2-0, after f1-2, 270d
  Fixes                 :crit,      b2-0, after f1-2, 365d

  section Elephox 2.1
  Release 2.1           :milestone, m2-1, after f2-0, 0
```
