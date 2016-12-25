# PHP-Resource-Bundler
### Helper class for bundling multiple files into one

## Setup

```php
/**
 * @var string Server-side root directory (C:/var/www/Project)
 */
private static $server_root = ROOT;
/**
 * @var string Client-side root directory (http:127.0.0.1:8080/Project)
 */
private static $web_root = WEB_ROOT;
/**
 * @var string Sub-directory to store compiled bundles
 */
private static $bundle_directory = BUNDLES_DIRECTORY;
```

Change the above settings to match your environment

## Extention

```php
public static function file($files) {
    $p = self::bundler($files, "bundle_prefix", ".file_ext");
    echo "<link rel='stylesheet' crossorigin='anonymous' integrity='sha256-$p[SHA256]' href='$p[SRC]'>";
}
```

Extend the class using the above syntax. The self::bundler function returns the web-relative path to the bundled file along with a base64 encoded raw SHA256 checksum of the file for use with client-side integrity checks.

## Usage

```php
<?php Bundle::CSS(array("Content/css/normalize.css", "Content/css/main.css")); ?>
```
Call the bundling function you need, and pass an array containing the relative paths from the server root (127.0.0.1:8080/Project/**_Path/To/File__**)
Files will be bundled in the order they are specified
