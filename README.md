# WordPress GitHub Updater
Update WordPress plugins or themes from GitHub

## Plugins
- include the `GitHub_Plugin_Updater` Class file in your plugin
- Adapt the Namespace of the class
- Invoke the class in your main plugin file

## Example:
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
- Make sure that on each update, you updat the veresion (default to `1.0.0` in the class paramater when invoking it
- Make sure to namespace each instance (every plugin will have its own namespace)
- Make sure to use create a proper, installable zip file with the exact same name as your Plugin has when installed.
    - That ZIP must be uploaded to the GitHub Release in the `Attach binaries by dropping them here or selecting them.` section
    - The ZIP must hold all plugin files, just like the actual WP Plugin
    - You must use proper Tags (like `1.0.1` and then next `1.0.2`) when creating releases
    - You must use releases to push updates

