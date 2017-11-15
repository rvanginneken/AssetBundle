DynamicAssetIncludeBundle
=============

The DynamicAssetIncludeBundle makes it easy to include assets at any point in your twig files and render it at the end of the \<head\> and \<body\> tags in the page. No blocks needed.
Stylesheets are printed inline and all results are cached in production, following Symfony's *cache.app* settings.

Build for Symfony 3.3 in PHP 7.1.

Installation
------------

Require the bundle with composer:
```bash
    $ composer require rvanginneken/dynamic-asset-include-bundle
```

Enable the bundle in the AppKernel:
```php
    public function registerBundles()
    {
        $bundles = [
            // ...
            new \RVanGinneken\DynamicAssetIncludeBundle\RVanGinnekenDynamicAssetIncludeBundle(),
            // ...
        ];
    }
```

That's it.

Usage
-------
Note: priorities are optional and are only added to these examples to show their availability.

Include a stylesheet:
```twig
    {{- add_stylesheet('css/hello_world.css', 0) -}}
```

Include a javascript file:
```twig
    {{- add_javascript('js/hello_world.js', 0) -}}
```

Include inline javascript:
```twig
    {%- set inline_hello_world -%}
        <script type="text/javascript">
            console.log('Hello world!');
        </script>
    {%- endset -%}
    
    {{- add_inline_javascript(inline_hello_world, 0) -}}
```