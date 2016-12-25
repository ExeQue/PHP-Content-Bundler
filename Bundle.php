<?php

final class Bundle {
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

    /**
     * Main bundler for Cascading StyleSheet files
     *
     * @param string[] $css_files paths to css files relative to the root directory
     */
    public static function CSS($css_files) {
        $p = self::bundler($css_files, "cssbundle", ".css");
        echo "<link rel='stylesheet' crossorigin='anonymous' integrity='sha256-$p[SHA256]' href='$p[SRC]'>";
    }

    /**
     * Main bundler for JavaScript files
     *
     * @param string[] $js_files paths to js files relative to the root directory
     */
    public static function JS($js_files) {
        $p = self::bundler($js_files, "jsbundle", ".js");
        echo "<script type='text/javascript' crossorigin='anonymous' integrity='sha256-$p[SHA256]' src='$p[SRC]'></script>";
    }

    /**
     * Main bundling function - Bundles files in the specified order
     *
     * @param string[] $files         files to bundle
     * @param string   $bundle_prefix prefix for the bundle file name (cssbundle, jsbundle etc)
     * @param string   $ext           file extention
     *
     * @return array
     */
    private static function bundler($files, $bundle_prefix, $ext) {
        $ext = str_replace(".", "", $ext);

        $paths = self::getPaths($files);

        $file_change_hash = self::compileHash($paths);

        $bundle_name = "$bundle_prefix-$file_change_hash.$ext";
        $bundle_path = self::$server_root . DIRECTORY_SEPARATOR . self::$bundle_directory . DIRECTORY_SEPARATOR . $bundle_name;

        self::save($paths, $bundle_path, $bundle_prefix);

        $web_path = self::$web_root . self::$bundle_directory . "/$bundle_name";
        $sha256 = base64_encode(hash_file("sha256", $bundle_path, true));

        return array("SRC"    => $web_path,
                     "SHA256" => $sha256);
    }

    /**
     * Checks if a file path has a .min version in the same folder.
     * Returns the .min file if found else passes through the file path
     *
     * @param string $path
     *
     * @return string
     */
    private static function getMin($path) {
        if (file_exists($path)) {
            $ext = substr($path, strrpos($path, "."));
            $min_path = str_replace("$ext", ".min$ext", $path);
            if (file_exists($min_path)) {
                return $min_path;
            }

            return $path;
        }

        return "";
    }

    /**
     * Compiles paths array with filemtime key of the paths
     * Uses self::getMin() to check and select a .min if it exists
     *
     * @param string[] $files
     *
     * @return array
     */
    private static function getPaths($files) {
        $paths = array();
        foreach ($files as $file) {
            $file = str_replace("/", "\\", $file);
            $path = self::getMin(self::$server_root . DIRECTORY_SEPARATOR . $file);
            $paths[ filemtime($path) ] = $path;
        }

        return $paths;
    }

    /**
     * If the requested bundle isn't already save on the server, then create and save the bundle for later use.
     *
     * @param string[] $files
     * @param string   $bundle_path
     * @param string   $type CSS|JS
     */
    private static function save($files, $bundle_path, $type) {
        if (!file_exists(self::$server_root . DIRECTORY_SEPARATOR . self::$bundle_directory)) {
            mkdir(self::$server_root . DIRECTORY_SEPARATOR . self::$bundle_directory);
        }
        if (!file_exists($bundle_path)) {
            $contents = "";
            foreach ($files as $file) {
                $contents .= file_get_contents($file);
            }
            foreach (glob(dirname($bundle_path) . "/$type*") as $file) {
                unlink($file);
            }
            $file = fopen($bundle_path, "w");
            fwrite($file, preg_replace("/\r|\n/", "", $contents));
            fclose($file);
        }
    }

    /**
     * Compiles a base64 encoded sha256 checksum for file change validation
     * using filemtime from the paths variable from the bundler (CSS,JS)
     *
     * @param string[] $paths
     *
     * @return string Base64 encoded sha256 hash
     */
    private static function compileHash($paths) {
        $key_val = "";
        foreach (array_keys($paths) as $key) {
            $key_val .= $key;
        }

        return hash("sha256", $key_val);
    }
}
