MONOGATARI
==========

###Markdown-based blog

## Characteristics
* Extended markdown syntax ([php-markdown], [php-smartypants]).
* Syntax highliting ([prettify]).
* Localization.
* Caching of rendered markdown pages ([zf2]).
* Support for themes ([twig]).
* Simple and short single-file application.

## Installation
1. Update settings.php with your configuration.
2. Adjust `RewriteBase` inside htaccess if needed.

## Usage

### Page structure
A page is a normal markdown text file, with the addition of an header containing metadata. The available fields are:
* title: will be displayed next to the site name.
* date: the format is Y/M/D.
* description: used in `<meta name="description"/>`
* robots: used in `<meta name="robots"/>`
* excerpt: number of lines (from the beginning of the content) to include in the page preview.

This is a sample header:

``` html
 <!---
    title = Title
    date = 2013/03/31
    description = Description
    robots = noindex,nofollow
    excerpt = 8
--!>
```

All pages must end with the `.md` extension.

### Page localization
This blog supports using up to two languages, with the following rules about page filenames.
* Localized pages end with a double extension, like `page.en.md` and `index.ja.md`.
* The order of preference is [localized page], [neutral page], [page in another language].

### Sections
Sections are showed in the navigation menu. To create a section do the follwing:
* Create a directory under `/content`.
* Add a new page named index (can be localized).
* Add a `section_index` field inside the header and set it to a number describing the show order in the navigation menu (lower to higher).
* Sections can have subdirectories, but they will not generate additional sections (useful to organize the pages).

### Layouts
Layouts are saved inside the homonymous directory.

Layouts can access a number of data through twig:

* {{ lang }}
* {{ layout_url }}
* {{ base_dir }}
* {{ base _url }}
* {{ continue_reading }}
* {{ analytics_ua }}
* {{ frontpage }}
* {{ url }}
* {{ site_title }} 
* {{ sections }}
* {{ metadata }}
* {{ content }}

Look at the default theme's index.html for sample usage.

### Markdown
Markdown is rendered in three passes:
1. (MarkdownExtra)[php-markdown]
2. (SmartyPants)[php-smartypants]
3. (Prettify)[prettify] (client-side)

About the syntax, refer to the respective project documentation.
Note: you can use html, and can also include markdown inside html tags if you specify the `markdown="1"` attribute.

### TODO
* Integrate comments (using external services)
* If code prettify is too slow, execute code highlight in php to let it cache the highlighted page.

[php-markdown]: https://github.com/michelf/php-markdown
[php-smartypants]: https://github.com/michelf/php-smartypants
[prettify]: https://code.google.com/p/google-code-prettify/‎
[zf2]: https://github.com/zendframework/zf2
[twig]: https://github.com/fabpot/Twig
