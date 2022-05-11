<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Console</p>
    <p class="subtitle">Syntax</p>
  </div>
</section>

<!---{? set title = "Command Line Syntax @ Elephox" }-->

[toc]

---

# Quick Reference

```
phox echo "Hello World!" --repeat=12 -?
│    │    │                │          │
│    │    │                │          └─► short option (-<short>)
│    │    │                │
│    │    │                └────────────► long option with value (--<name>=<value>)
│    │    │
│    │    └─────────────────────────────► argument (<value>, "<value>", or '<value>')
│    │
│    └──────────────────────────────────► command
│
└───────────────────────────────────────► binary
```

- short options can be compounded: `-a -b -c` = `-abc`
- the last short option can have a value: `-abc=1` = `-a -b -c=1`
- long options can function as flags if they have no value: `--foo` sets the value to `true`
