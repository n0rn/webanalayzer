Web Analyzer Tool
==================

This library fetches the raw html of the given HTML and parses all the Images, CSS, JS and Embedded objects. 
For IFrames, the html of the iframes is parsed and process recursively

Libraries Needed
-------------------

1. PHP cURL
2. cURL

Usage
-----

```
php run.php --url="<URL>"
```

Example:

```
php run.php --url="https://google.com"
```