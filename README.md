# CEON-URI-Mapping

## 27/12/2025 This branch is in use in production, containing many minor code changes as a result of IDE suggestions, but no actual bug fixes. Issues may emerge due to type hinting, please report.
Based on version 5.1.1 available 11/01/2024 from ceon.net  
It does NOT include the UMM-edition files that are bundled with the commercial Uri Mappings Manager (UMM). I have private repositories with fixes for those, open to users who have purchased that module.

Due to the haphazard communication/support from CEON support, this repository aims to collect bugfixes post 5.1.1, to keep things rolling until... something else happens.

As this repository is just strictly maintenance, the original documentation has not been updated.

## Compatibility
PHP8+ & current (2.1.0) Zen Cart but probably 157d onwards.

## Changes not fixes...
Since I was accused of the heinous crime of offering invalid HTML from my repository (as if I could have written this plugin)...I bit the bullet and went through all the admin files to see what else would be automagically detected.  
The answer is not much, thanks to the quality efforts of the author Conor Kerr (RIP) who was CEON.  
So, these files are cleared of whitespace, have type hints for parameters and functions, short array and phpdoc syntax, makes use of php8 functions and has some simplification of code where identified.  
I use the admin files all in strict mode  to make it more fussy, and so far so good.  
I'm using these files in strict mode so am reasonably confident others should not have issues....but if you do, open an issue here, obviously!

## Installation
All files are contained in the /files folder.  
All files apart from those in /file/includes/template files are new and so will not overwrite existing files.  
The files in /files/includes/templates/*CEON URI TO MERGE files should be merged into your files. Use file comparison software to compare versions.


1. Copy the files into your development server for testing, prior to uploading to your production server: YOU HAVE BEEN WARNED!  
As the template folders have their own names, there is no danger of overwriting existing files.

1. [Zen Cart Versions 1.3.0 - 1.3.8 Only] Enable Canonical URI Support.

1. In the Admin, Browse to Modules > URI Mapping Config to auto-install the database tables. 

1. Browse to Ceon URI Mapping (SEO) Config->Installation Check->Installation Check->Click here to go to the Ceon URI Mapping Installation Check page.  
You should get a "Congratulations" Message...if not, sorry, you'll have to read the error message and figure it out.

1. If all was good in the previous step, it should now have generated a list of rules to add to your SITE ROOT .htaccess file.  

1. [OPTIONAL but RECOMMENDED] Use the Sitemap XML Plugin: https://github.com/lat9/sitemapxml

1. [OPTIONAL but RECOMMENDED] Take a note of the FAQs in the original documentation.

1. Set up the URI mappings for categories, products, manufacturers and EZ-Pages.  
Note this "free" version of URI Mapping does not automatically generate static urls for the products/categories/pages that already exist (for example when browsing the storefront). The static urls are only generated when those items are edited in the Admin, one by one...

To add auto-generation, generate the urls en masse and define the structure of the static url as a template per category/manufacturer/product, you need the (paid) add-on: https://ceon.net/seo-modules/ceon-uri-mapping-manager  
Don't waste your time, just buy it (say I). You'll have lots of merging fun adding it into the files from this repository, but that's just the way it is while there is no real-time CEON-managed bug-fixing process.

## Forum Support:
https://www.zen-cart.com/showthread.php?225478-Ceon-URI-Mapping-V5-0

## Changelog
07/01/2025: lots of minor nitpicks...for future changes refer to the commit history.

31/12/2025: remove includes\init_includes\overrides\init_add_crumbs.php. Not necessary ZC158+

07/08/2024:  
update template RC header for mobile_detect  
removed ceon_uri_mapping_sessions_define.php: not needed anymore

11/02/2024: Simplify directory structure for comparisons/remove obsolete files

11/02/2024: Upload of version 5.1.1
