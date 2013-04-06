# pizza tool (fun project, based on [Silex](http://silex.sensiolabs.org))

## login

admin / admin (user.sql)

## webserver configuration

### apache

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ /index.php [L]

### lighttpd

    url.rewrite-if-not-file = (
        "^/(?!index\.php/)[^\?]*(\?.*)?$" => "/index.php$1"
    )