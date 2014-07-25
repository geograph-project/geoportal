geoportal
=========

Geograph Portal is a code to create a 'mini-site' based on Geograph Images

Users of the site can browse a pre-curated selection of images from the wider Geograph Collection. 


Requirements
=========

* MySQL Database (5.5+ Recommended)
* PHP (5.3+ Recommended)
* Webserver (Apache Recommended) 


Features
=========

(everything is still mostly at the prototype stage right now) 

* Downloads images along specific critera from Geograph website, and stores in local database (currently on by keyword)
* Offers various display methods to browse the local database of images
   Examples includes (filterable) thumbnail displays, breakdown statistics and an overview Google Map. 
* Curation to exclude permentaly specific images from portal
* Custom Labels. Attach custom labels to images, to allow facetted browsing


Todo
=========

* Login system - to assign admin, and moderators etc
* Not optimized for over about 10,000 images - makes minimal use of indexes. 
* More flexible 'import' system, beyond running simple keyword searches. 
* More mapping options (eg OS OpenSpace) 
* More streamlined modeation and labeling facility
* Way for users to directly add images to the portal


Installation
=========

* Find a server with PHP and a MySQL database available

* Download code, either via git directly:

	git clone git@github.com:barryhunter/geoportal.git
	
	or download the zip file:
	
	https://github.com/barryhunter/geoportal/archive/master.zip
	
* Copy config.sample.php to config.inc.php

* Edit config.inc.php to include connection details for your database

* Upload files to server - just put all the files in a folder of your choice

* visit http://yourdomain.com/yourfolder/ to begin setup

* fill out the inital configuation

* Visit http://yourdomain.com/yourfolder/ping.php to initially seed it with some images

* Then setup crontab to call ping.php on a regular schedule, say daily. 

Admin
=========

http://yourdomain.com/yourfolder/admin-moderate.php to remove any unsuitable images

http://yourdomain.com/yourfolder/admin-labels.php to setup custom labels

http://yourdomain.com/yourfolder/admin-label-image.php to assign label(s) to each image. 
