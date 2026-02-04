# 🛠️ Development Guide

This setup maps your local `wp-content` folder directly into the WordPress container. This means you can use your favorite code editor (VS Code, etc.) on your host machine, and changes will be instantly visible on the local site.

## 🧩 Developing a Plugin

1.  Navigate to `wp-content/plugins/`.
2.  Create a new folder for your plugin, e.g., `my-custom-plugin`.
3.  Inside that folder, create a PHP file with the same name, e.g., `my-custom-plugin.php`.
4.  Add the standard WordPress plugin header:

    ```php
    <?php
    /*
    Plugin Name: My Custom Plugin
    Description: A brief description of the plugin.
    Version: 1.0
    Author: Your Name
    */

    // Your code starts here
    function my_custom_function() {
        // ...
    }
    ```

5.  Go to the **WordPress Admin Dashboard** -> **Plugins**.
6.  Activate your new plugin.

## 🎨 Developing a Theme

1.  Navigate to `wp-content/themes/`.
2.  Duplicate an existing theme or create a new folder.
3.  Edit `style.css` and `index.php` as needed.

## 🐛 Debugging

To enable debugging, checking the `wp-config.php` inside the container is usually required, but for simple errors, PHP warnings will often appear on the screen if `WP_DEBUG` is enabled.

To manually enable debug logging:
1.  You may need to edit `wp-config.php`. Since this file is in the container root (not mapped), you can use `docker exec`:
    ```bash
    docker exec -it grija-wp-dev bash
    apt-get update && apt-get install -y nano
    nano wp-config.php
    ```
    Add/Change:
    ```php
    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    ```
