<section class="hero is-primary">
  <div class="hero-body">
    <p class="title">Ecosystem</p>
    <p class="subtitle">Mimey</p>
  </div>
</section>

<!---{? set title = "Mimey @ Elephox" }-->

[toc]

---

[elephox/mimey](https://github.com/elephox-dev/mimey) is a package for parsing and handling mime types.

It keeps track of the [mime types defined by Apache for httpd](https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types)
and provides several ways to intact with them in PHP:

- convert an extension (`json`) to a corresponding mime type (`application/json`)
- convert a mime type to a commonly used extension (`application/json -> json`)
- get all mime types associated with an extension (`wmz -> application/x-ms-wmz, application/x-msmetafile`)
- get all extension associated with a mime type (`image/jpeg -> jpg, jpeg, jpe`)
- a builder to create your own mappings and export them
- a PHP enum with all mime types as enum cases with convenience methods

The original package was developed at [ralouphie/mimey](https://github.com/ralouphie/mimey).
