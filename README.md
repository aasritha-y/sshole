# sshole-site

A small reference page summarising the public write-up at sshhole.com about a claimed
OpenSSH password-check weakness on musl-based Linux.

The claims have not been independently verified. Confirm them against the musl, OpenSSH
and Alpine sources before relying on the page.

## Layout

    public/index.php        template (the only page)
    public/assets/style.css styles, light and dark
    src/content.php         all text and links; edit here to change content

Only `public/` should be served. `src/` stays outside the web root.

## Run locally

    cd ~/sshole-site
    php -S localhost:8080 -t public

Then open http://localhost:8080. Needs PHP 7.4 or newer.

## Editing content

Fields in `src/content.php` that hold HTML (timeline body, chain, remediation, intro) are
trusted, authored text and are printed unescaped. Plain-text fields are escaped on output.
Never put user input into the trusted fields.
