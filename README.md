AssetBundle
=============

The AssetBundle makes it easy to include assets at any point in your twig files and render it at the end of the \<head\> and \<body\> tags in the page. No blocks needed.
Stylesheets are printed inline and all results are cached in production, following Symfony's *cache.app* settings.

In debug mode, actual assets are served. In no-debug mode, files are copied to *public/asset_cache* with unique naming. The directory is automatically 
cleared with the *cache:clear* command. Using unique file names guarantees browser cache busting when the application cache is cleared.

Installation
------------

Require the bundle with composer:
```bash
    $ composer require rvanginneken/asset-bundle
```

Enable the bundle in the `config/bundles.php`  (_use `AppKernel.php` before symfony 4_):

```php
    return [
        // ..
        RVanGinneken\AssetBundle\RVanGinnekenAssetBundle::class => ['all' => true],
        // ..
    ];
```

Ignore the asset cache directory (used to bust browser cache). Put the following in your `.gitignore` (_use `web` before symfony 4_):
```text
    # ..
    /public/asset_cache/
    # ..
```

That's it.

Usage
-------
Note: priorities are optional and are only added to these examples to show their availability.

Include a stylesheet:
```twig
    {% asset 'css_file', 'css/hello_world.css', 0 %}
```

Include inline style:
```twig
    {%- set inline_style_hello_world -%}
        <style>
            body {
                background-color: lightblue;
            }
        </style>
    {%- endset -%}
  
    {% asset 'css', inline_style_hello_world, 0 %}
```

Include a javascript file:
```twig
    {% asset 'javascript_file', 'js/hello_world.js', 0 %}
```

Include inline javascript:
```twig
    {%- set inline_javascript_hello_world -%}
        <script type="text/javascript">
            console.log('Hello world!');
        </script>
    {%- endset -%}
    
    {% asset 'javascript', inline_javascript_hello_world, 0 %}
```

Public folder
-------
The bundle detects the public folder based on the symfony version. If you're using something custom you can override it by using:
```
    rvanginneken_asset:
        public_folder: your_custom_folder
```