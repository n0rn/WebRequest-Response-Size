Web Analyzer Tool
==================

This library is a PHP CLI application to calculate the total download size of any URL

0. This library fetches the raw html of the given HTML and parses all the Images, CSS, JS and Embedded objects. 
0. For IFrames, the html of the iframes is parsed and process recursively

Libraries Needed
-------------------

0. PHP cURL
0. cURL

Usage
-----

```
php run.php --url="<URL>"
```

Or, more realistically:

```
php run.php --url="https://google.com"
```
