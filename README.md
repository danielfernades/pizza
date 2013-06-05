# pizza tool (fun project, based on [silexskeleton](https://github.com/dominikzogg/silexskeleton))

## installation

### config

    app/config.yml

### terminal

    ./app/console orm:schema-tool:create
    ./app/console pizza:user:create admin --admin

## webserver configuration

### apache

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ /index.php [L]

### lighttpd

    url.rewrite-if-not-file = (
        "^/(?!index\.php/)[^\?]*(\?.*)?$" => "/index.php$1"
    )