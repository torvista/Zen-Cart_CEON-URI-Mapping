# CEON-URI-Mapping
Currently based on version 5.1.1 available 11/01/2024 from ceon.net  
It does NOT include the files for the commercial UMM version.

Due to the haphazard communication/support from CEON, this repository aims to collect bugfixes post 5.1.1, to keep things rolling until... something else happens.

As this repository is just strictly maintenance, the original documentation has not been updated.

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

## Forum Support:
https://www.zen-cart.com/showthread.php?225478-Ceon-URI-Mapping-V5-0

## Changelog
07/08/2024:  
update template RC header for mobile_detect  
removed ceon_uri_mapping_sessions_define.php: not needed anymore

11/02/2024: Simplify directory structure for comparisons/remove obsolete files

11/02/2024: Upload of version 5.1.1

