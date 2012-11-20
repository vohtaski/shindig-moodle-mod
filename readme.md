About
==============
This is a plugin to add OpenSocial gadgets to the Moodle.
OpenSocial gadgets are rendered via Apache Shindig (version 2.0,
extended with spaces) in an iGoogle similar fashion. 

Read more and see screenshots at [this blog](http://vohtaski.blogspot.com/2011/09/bring-opensocial-gadgets-to-moodle_22.html)

For the OpenSocial JavaScript API to use inside gadget please refer to [wiki page](https://github.com/vohtaski/shindig-moodle-mod/wiki).

Requirements
==============
* Moodle 2.1 (was not checked on previous versions!)
* Apache Shindig 2.0
   
How to use gadgets
==================
Step 1. Create a space
----------------------
To add widgets to Moodle, you follow the same path as to add pages for course.
On the course page enable editing by clicking on "Turn editing on".
Then click "Add a resource...". Choose "Widget space".

Step 2. Add space settings 
--------------------------
* Add name and description for a space (and possible some html content).
* Specify number of columns for widgets (one, two or three column view)
* Specify url of OpenSocial gadgets that you wish to add.

Step 3. Save and view the widgets
---------------------------------
Click button "Save and display". Enjoy!

Example gadgets
===============
* Iframe gadget (uses appdata to save urls) - http://graasp.epfl.ch/gadget/iframe.xml
* Sample gadgets stats - viewer/owner and context (widgetspace id) for a widget - http://graasp.epfl.ch/gadget/gadget_info.xml

See code inside for more details

Installation
==============
Install moodle plugin
--------------
* Rename this folder to **widgetspace** and drop it to moodle->mod. 
* Go in Moodle to Site Administration->Notifications and install plugin
* Specify the Apache Shindig installation to use for gadgets 

        Find the function get_shindig_url in container.php. The default
        return value is "http://iamac71.epfl.ch:8080", which means that the
        server http://iamac71.epfl.ch:8080 will be used. If you want to use your
        own server, the default return value should be replaced by its
        domain, e.g., change the return value to "http://localhost:8080".


If you only want to render gadgets, you can specify any shindig installation
existing in the cloud (for example http://shindig.epfl.ch). 

Install shindig for moodle
--------------------------
If you want to support OpenSocial APIs, you should
connect your own shindig installation to your Moodle database.
You will need to install Apache Shindig 2.0 extended with spaces
as described here.

It extends Apache Shindig to match OpenSocial APIs with Moodle database schema.
The installation instruction and the code can be found here:
[Moodle-Shindig](https://github.com/vohtaski/moodle-shindig).
    
Notes for Moodle 2.2
-------------------
For moodle 2.2 you have to change the CHAR_MAX_LENGTH limit, which is set to 1333.
Find line CHAR_MAX_LENGTH = 1333 in moodle/lib/xmldb/xmldb_field.php
and change it into
CHAR_MAX_LENGTH = 8192
    
License
=======
Moodle plugin - GPL
-------------------

    // This plugin is part of Moodle - http://moodle.org/
    //
    // Moodle is free software: you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation, either version 3 of the License, or
    // (at your option) any later version.
    //
    // Moodle is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    //
    // You should have received a copy of the GNU General Public License
    // along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

Thanks
=======
* Carsten Ullrich
* Alex DePena
