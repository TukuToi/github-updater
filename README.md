# WordPress GitHub Updater
Update WordPress plugins or themes from GitHub

## Plugins
- include the `GitHub_Plugin_Updater` Class file in your plugin
- Adapt the Namespace of the class
- Invoke the class in your main plugin file

### Example:
```
/**
 * Require the GitHub_Plugin_Updater class.
 *
 * Each of your plugins will require GitHub_Plugin_Updater to be loaded under a different namespace.
 * Ideally use an autoloader, but for the sake of functionality we are using require_once here.
 */
require_once plugin_dir_path( __FILE__ ) . 'class-github-plugin-updater.php';
use MyPlugin\GitHub_Plugin_Updater;
( new GitHub_Plugin_Updater( plugin_basename( __FILE__ ), '1.0.0', 'https://api.github.com/repos/username|org/repo-name' ) )->init();
```

- Make sure to replace `username|org` with _either_ the username or orgname depending on where your `repo-name` is located
- Make sure that on each update, you update the veresion (default to `1.0.0`) in the class paramater when invoking it
- Make sure to namespace each instance (every plugin will have its own namespace)
- Make sure to use create a proper, installable zip file with the exact same name as your Plugin has when installed.
    - That ZIP must be uploaded to the GitHub Release in the `Attach binaries by dropping them here or selecting them.` section
    - The ZIP must hold all plugin files, just like the actual WP Plugin
    - You must use proper Tags (like `1.0.1` and then next `1.0.2`) when creating releases
    - You must use releases to push updates

## Themes
- include the `GitHub_Theme_Updater` Class file in the theme `function.php`
- Adapt the Namespace of the class
- Invoke the class in the theme function file

### Example
```
/**
 * Require the GitHub_Theme_Updater class.
 *
 * Your theme will require GitHub_Theme_Updater to be loaded under a custom namespace.
 * Ideally use an autoloader, but for the sake of functionality we are using require_once here.
 */
require_once get_template_directory() . '/update.php';
use MyTheme\GitHub_Theme_Updater;
( new GitHub_Theme_Updater( 'custom-theme', '1.0.0', 'https://api.github.com/repos/username|org/repo-name' ) )->init();
```

- Make sure to replace `username|org` with _either_ the username or orgname depending on where your `repo-name` is located
- Make sure that on each update, you update the veresion (default to `1.0.0`) in the class paramater when invoking it
- Make sure to namespace each instance (every theme will have its own namespace)
- Make sure to use create a proper, installable zip file with the exact same name as your Theme has when installed.
    - That ZIP must be uploaded to the GitHub Release in the `Attach binaries by dropping them here or selecting them.` section
    - The ZIP must hold all theme files, just like the actual WP Plugin
    - You must use proper Tags (like `1.0.1` and then next `1.0.2`) when creating releases
    - You must use releases to push updates
    - Of course you will NOT update child-themes (that would destroy the user's work)
    - Note that your "theme name" or "theme slug" in this context is always the thems' folder name, and that in general has to match the theme name set in stylesheet (`Theme Name`, `theme-name`)
