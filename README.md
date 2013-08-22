MyCasaParticular v2.0
=====================

Redesign of MyCasaParticular.com

Installation
------------

1. First clone this repository using the git clone command (git clone https://github.com/ABUC/MyCP.git)
2. Change to project root directory and checkout the develop branch with "git checkout develop"
3. Copy app/config/parameters.yml.template to app/config/parameters.yml
4. Edit app/config/parameters.yml according to your local settings
5. Run "composer install" in the project's root directory
6. Wait some minutes until composer downloaded and installed all libraries/packages/bundles needed
7. Configure your local web server to be able to access web/app_dev.php
8. Open a browser and open app_dev.php (e.g. http://mycasaparticular.local/app_dev.php)
9. Enjoy!

Working with this repository
----------------------------

We would like all developers to commit code to the develop branch. The develop branch will be merged into the master branch when the current state of the develop branch has been verified and a new project version shall be pushed to the web server.

For new features that take more time and commits than changing some lines of code we would like all developers to create feature branches from develop and merge them back to develop when you are finished with the feature. Then the feature branch can be deleted.
