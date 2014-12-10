Base WP Plugin
====================

This is a simple starter plugin that includes a base class with a very simple set of utilities that most plugins should have.

Getting Started
---------------

You will need to change the name of the plugin in a few simple steps:

1. Rename the base plugin folder name and primary PHP file named 'baseplugin.php' to your plugin's name. Make sure the folder name matches the name of the PHP file (without .php on the end).
2. Rename the Javascript and CSS files inside the /scripts/ and /styles/ directory to 'myplugin.js' and 'myplugin.css'.
3. Search for `'baseplugin'` and replace with your desired plugin name in all lowercase with dashes between words (if desired).
4. Search for `'Base Plugin'` and replace with your plugin's proper name.
5. Search for `'BASEPLUGIN'` and replace with your plugin's name in all caps. Spaces should be convered to underscores.
6. Search for `'Base_Plugin'` and replace with your plugin's proper name with underscores instead of spaces between words.

This base plugin does not include any utilities for creating and saving plugin settings at this time, but we recommend using the Settings API to implement your plugin settings.
