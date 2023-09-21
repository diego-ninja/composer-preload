
# Composer Preload  Plugin

Preload your code to opcache with a composer command to make your code run faster.

Composer Preload is a Composer plugin that aims to provide and complement PHP opcache warming.  
This plugin introduces a new `composer preload` command that can generate a `vendor/preload.php` file (after  `vendor/autoload.php` pattern) that contains calls to warm up the opcache cache.

# How it works

Currently, this plugin recursively scans for `.php` files in the given paths and creates a file that calls   
`opcache_compile_file` or `require_once` functions, depending on the mechanism selected in the configuration.

# Installation

You can install this plugin the same way you'd install a normal composer package:
```  
composer require diego-ninja/composer-preload  
```  

If you would rather install this globally:
```  
composer g require diego-ninja/composer-preload  
```  

# Configuration

1: Edit your `composer.json` file and create a section called `extra`  if it doesn't already exist. Here is an   
example:

```  
{  
    "extra": {  
        "preload": {  
            "paths": [  
                "app",
                "bootstrap",
                "config"
                "vendor"  
            ],  
            "exclude": [  
                "app/core/tests",  
                "app/core/lib/Drupal/Component/Assertion",  
                "app/core/modules/simpletest",  
                "app/core/modules/editor/src/Tests"  
            ],  
            "extensions": ["php", "module", "inc", "install"],  
            "exclude-regex": [
	            "/[A-Za-z0-9_]test\\.php$/i",
			],  
            "no-status-check": false,
            "mechanism": "compile",  
            "files": [  
                "somefile.php"  
            ]  
        }  
    }  
}  
```  

The `extra.preload` directive contains all the configuration options for this plugin. The `paths` directive must be an array of directories relative to the `composer.json` file. These directories will be scanned recursively for `.php` files, converted to absolute paths, and appended to the `vendor/preload.php` file.

2: Run the `composer preload` command.

3: Execute the generated `vendor/preload.php` file. You can either run `php vendor/preload.php` or use your web server   
to execute it. See the Preloading section below for more information.


## Configuration options

### `extra.preload.paths` : _Required_

An array of directory paths to look for `.php` files in. This setting is required as of now. The directories must exist at the time `composer preload` command is run.

### `extra.preload.exclude` : _Optional_

An array of directory paths to exclude from the `preload.php`. This list must be relative to the `composer.json` file,  similar to the `paths` directive. The ideal use case limiting the scope of the `paths` directive.

### `extra.preload.extensions` : _Optional_, Default: `["php"]`

An array of file extensions to search for. If not entered, it will search for all `.php` files.  
Do not enter the proceeding period (`.`) character. The example above is suitable for Drupal. For Symfony/Laravel projects,  you can leave the default option `["php"]` or just not use this option so it defaults to just `.php`.

### `extra.preload.exclude-regex` : _Optional_

Specify an array of PCRE-compatible full regular expressions (including delimiters and modifiers) to be matched against the full path and, if matched, excluded from the preload list. This can help you exclude tests from the preload list.

For example, to exclude all PHPUnit-akin tests, you can use the regular expression

```regex
/[A-Za-z0-9_]test\\.php$/i
```  

This will make sure the file name ends with "test.php", but also has an alphanumeric or underscore prefix. This is a common pattern of PHPUnit tests. The `/i` modifier makes the match case insensitive.

For directory separators, always use Unix-style forward slashes (`/`) even if you are on a Windows system that uses backwards slashes (`\`). Don't forget to properly escape the regex pattern to work within JSON syntax; e.g escape  slashes (`\` and `/`) with a backwards slash (`\` -> `\\` and `/` -> `\/`).

This will make the regular expression  hard to read, but ¯\\_(ツ)_/¯.

###`extra.preload.no-status-check`: _Optional_, Default: _`false`_

If this setting is set to `true` (you can also pass the `--no-status-check` command line option), the generated  preload.php file will not include any additional checks to ensure that opcache is enabled. This setting is disabled by default, and the generated `preload.php` file will contain a small snippet at the top to indicate that opcache is not enabled.

### `extra.preload.files` : _Optional_

An array of individual files to include. This setting is optional. The files must exist  at the time the `composer preload` command is executed.

### `extra.preload.mechanism`: _Optional_, Default: _`compile`_

By default, the Preloader will upload the files to Opcache using `opcache_compile_file()`. This avoids executing any file in your project, but no links (traits, interfaces, extended classes, ...) will be resolved from the files compiled. You may have some warnings of unresolved links when preloading (nothing too dangerous).

You can change this using `useRequire()`, which changes to `require_once`, along the path the Composer Autoloader (usually at `vendor/autoload.php`) to resolve the links.

# Preloading

To do the actual preloading, run `vendor/preload.php`.

If you have opcache enabled for CLI applications, you can call `php vendor/preload.php` directly to preload the generated PHP file and warm up the cache inmediatly.

In a webserver context once generated, tell PHP to use this file as a preloader at start up in your `php.ini`.

```ini
opcache.preload=/app/vendor/preload.php
```

Once the script is generated, **you're encouraged to restart your PHP process** (or server, in some cases) to pick up the generated preload script. Only generating the script [is not enough](https://www.php.net/manual/en/opcache.preloading.php).
# FAQ

### What does this plugin even do?

This plugin can create a new file at `vendor/preload.php` that follows the pattern of Composer's autoloader at   
`vendor/autoload.php`.

This new `preload.php` file contains several function calls that compiles PHP files and cache  them into PHP's opcache. PHP Opcache is a shared memory (with optional file storage option) feature in PHP that can  hold compiled PHP files, so the same file doesn't need to be compiled again and again when its called.

This is a persistent memory until PHP is restarted or the cache is eventually flushed.

Caching files in opcache has siginificant performance benefits for the cost of memory.
### So all the files are loaded all the time?

All the files are loaded into _Opcache_. This is **not** same as you `include()` or `require()` a class, which makes  
PHP actually execute the code. When you cache code to Opcache, those classes are not executed - just their compiled code  is cached to the memory.

For example, if you declare a variable, this plugin's preload functionality will not make the variables available inside  your PHP code. You still have to include the file to make them available.

You can use the `require` mechanism to use `require_once` function instead `opcache_compile_file` to require and execute the files as mechanism to populate the opcache,

### I have the `vendor/preload.php` file. What now?

After generating the file, you might need to actually run it effectively load the files to Opcache. Ideally, you should do this every time you restart your web server or PHP server, depending on how you serve PHP within your web server.

From PHP 7.4 the `php.ini` has the option `opcache.preload` that let you specify this generated file, or a separate file that calls all `vendor/preload.php` files you have across your server to actively warm up the cache.

# Roadmap

- ☐ Extend `extras.preload` section to configure the packages that  should be preloaded instead of setting the individual paths.
- ☐ Progress bar to show the file generation progress
- ☐ Flag to generate the file _and_ run it, so the cache is immediately warmed up.
- ☐ Fancier progress bar.
- ⭕ Full test coverage.
- ☐ Even fancier progress bar with opcache memory usage display, etc.
- ⭕ Get many GitHub stars