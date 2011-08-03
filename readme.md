About
==============
This is a plugin to add OpenSocial gadgets to the Moodle.
OpenSocial gadgets are rendered via Apache Shindig (version 2.0)
in an iGoogle similar fashion. 

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

1. Get shindig and patch it!  
    
    $mkdir shindig
    $cd shindig
    $svn checkout http://svn.apache.org/repos/asf/shindig/tags/shindig-project-2.0.0 .
    $patch -p0 < shindig_moodle.patch
    
    
2. Add ssl keys
   
   $mkdir ssl_keys
   $cd ssl_keys
   $openssl req -newkey rsa:1024 -days 365 -nodes -x509 -keyout testkey.pem      -out testkey.pem -subj '/CN=mytestkey'
   $openssl pkcs8 -in testkey.pem -out oauthkey.pem -topk8 -nocrypt -outform PEM
    
   
3. Add the ssl keys information into java/common/conf/shindig.properties. Don't forget the full path to your oauthkey.pem!!
    
    shindig.signing.key-name=mytestkey
    shindig.signing.key-file=/path_to_shindig_branch/ssl_keys/oauthkey.pem
    

4. Add your database information to java/samples/src/main/resources/socialjpa.properties.
    
    db.driver=com.mysql.jdbc.Driver
    db.url=jdbc:mysql://localhost:3306/moodle
    db.user=shindig
    db.password=shindig
    db.write.min=1
    db.read.min=1
    jpa.socialapi.unitname=default
    shindig.canonical.json.db=sampledata/canonicaldb.json
    
    
5. Change host and port settings for your shindig in java/server/src/main/webapp/WEB-INF/web.xml
    
    shindig.host=iamac71.epfl.ch
    aKey=/shindig/gadgets/proxy?container=default&amp;url=
    shindig.port=8080
    

6. Change column name in person.db file. Only, if you do not use standard moodle prefix for tables "mdl_"
    
    @Table(name = "mdl_user")
    
7. Compile and start your server
    
    // To build the project
    $mvn -Dmaven.test.skip
    // To run the server on localhost (if not - put .war file into tomcat)
    $cd java/server
    $mvn jetty:run
    