About
==============
This is a plugin to add OpenSocial gadgets to the Moodle.
OpenSocial gadgets are rendered via Apache Shindig (version 2.0)
in an iGoogle similar fashion. Read more and see screenshots at
[this blog](http://vohtaski.blogspot.com/2011/09/bring-opensocial-gadgets-to-moodle.html)

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
* Iframe gadget (uses appdata to save urls) - http://graaasp.epfl.ch/gadget/iframe.xml
* Sample gadgets stats - viewer/owner and context (widgetspace id) for a widget - http://graaasp.epfl.ch/gadget/gadget_info.xml

See code inside for more details

Installation
==============
Install moodle plugin
--------------
* Rename this folder to **widgetspace** and drop it to moodle->mod. 
* Go in Moodle to Site Administration->Notifications and install plugin
* Specify the Apache Shindig installation to use for gadgets 
(see function get_shindig_url at container.php )

If you only want to render gadgets, you can specify any shindig installation
existing in the cloud (for example http://shindig.epfl.ch). 

Install shindig for moodle
--------------------------
If you want to support OpenSocial APIs, you should
connect your own shindig installation to your Moodle database. You will need to patch the core
Apache Shindig with Moodle-extensions to match OpenSocial APIs with Moodle database schema.
You can find a patch in the code - shindig_moodle.patch.

Get shindig and patch it!  
    
    $mkdir shindig
    $cd shindig
    $svn checkout http://svn.apache.org/repos/asf/shindig/tags/shindig-project-2.0.0 .
    $patch -p0 < shindig_moodle.patch
    
    
Add ssl keys
   
    $mkdir ssl_keys
    $cd ssl_keys
    $openssl req -newkey rsa:1024 -days 365 -nodes -x509 -keyout testkey.pem -out testkey.pem -subj '/CN=mytestkey'
    $openssl pkcs8 -in testkey.pem -out oauthkey.pem -topk8 -nocrypt -outform PEM
    
   
Add the ssl keys information into java/common/conf/shindig.properties. Don't forget the full path to your oauthkey.pem!!
    
    shindig.signing.key-name=mytestkey
    shindig.signing.key-file=/path_to_shindig_branch/ssl_keys/oauthkey.pem
    

Add your database information to java/samples/src/main/resources/socialjpa.properties.
    
    db.driver=com.mysql.jdbc.Driver
    db.url=jdbc:mysql://localhost:3306/moodle
    db.user=shindig
    db.password=shindig
    db.write.min=1
    db.read.min=1
    jpa.socialapi.unitname=default
    shindig.canonical.json.db=sampledata/canonicaldb.json
    
    
Change host and port settings for your shindig in java/server/src/main/webapp/WEB-INF/web.xml
    
    shindig.host=iamac71.epfl.ch
    aKey=/shindig/gadgets/proxy?container=default&amp;url=
    shindig.port=8080
    

Change column name in person.db file. Only, if you do not use standard moodle prefix for tables "mdl_"
    
    @Table(name = "mdl_user")
    
Compile and start your server
    
    // To build the project
    $mvn -Dmaven.test.skip
    // To run the server on localhost (if not - put .war file into tomcat)
    $cd java/server
    $mvn jetty:run
    
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

Shindig patch (shindig_moodle.patch) - ASF
------------------------------------------

    /**
     * Licensed to the Apache Software Foundation (ASF) under one
     * or more contributor license agreements. See the NOTICE file
     * distributed with this work for additional information
     * regarding copyright ownership. The ASF licenses this file
     * to you under the Apache License, Version 2.0 (the
     * "License"); you may not use this file except in compliance
     * with the License. You may obtain a copy of the License at
     * 
     *  http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing,
     * software distributed under the License is distributed on an
     * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
     * KIND, either express or implied. See the License for the
     * specific language governing permissions and limitations
     * under the License.
     */
    