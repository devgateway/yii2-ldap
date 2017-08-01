<?php
spl_autoload_register(function ($full_name) {
    $components = explode('\\', $full_name);
    $namespace = reset($components);
    if ($namespace != 'ldap') {
        return;
    }
    $class_name = end($components);
    require_once('..' . DIRECTORY_SEPARATOR . "$class_name.php");
} );
