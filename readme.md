About
-----
This is a plugin to add OpenSocial gadgets to the Moodle.
OpenSocial gadgets are rendered via Apache Shindig (version 2.0)
in an iGoogle similar fashion. 

Requirements
------------
* Moodle 2.1 (was not checked on previous versions!)
* Apache Shindig 2.0

Installation
------------
* Rename this folder to **widgetspace** and drop it to moodle->mod. 
* Go in Moodle to Site Administration->Notifications and install plugin
* Specify the Apache Shindig installation to use for gadgets 
(see function get_shindig_url at container.php )

If you only want to render gadgets, you can specify any existing shindig installation
in the cloud (for example http://shindig.epfl.ch). If you want to support OpenSocial APIs, you should
connect your own shindig installation to your Moodle database. You will need to patch the core
Apache Shindig with Moodle-extensions to match OpenSocial APIs with Moodle database schema (patch is coming soon).

Howto
-----
Step 1. Create a space
==============
To add widgets to Moodle, you follow the same path as to add pages for course.
On the course page enable editing by clicking on "Turn editing on".
Then click "Add a resource...". Choose "Widget space".


Step 2. Add space settings 
=================================
* Add name and description for a space (and possible some html content).
* Specify number of columns for widgets (one, two or three column view)
* Specify url of OpenSocial gadgets that you wish to add.

Step 3. Save and view the widgets
=========================================
Click button "Save and display". Enjoy!