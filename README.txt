
CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Social Post is part of the Social API. It provides a common interface for
creating modules related to autoposting to external provider services.

This module defines a path Administration » Configuration » Social API » Social
Post which displays a table of implementers (modules to autopost to external
providers on users' behalf).

This module does not restrict the way an implementer provides its functionality.
This allows the implementers to adapt to the external provider's requirements
they implement.

 * For a full description of the module, visit the project page:
  https://www.drupal.org/project/social_post

 * To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/social_post


REQUIREMENTS
------------

This module requires the following module and library:

 * Required module: Social API (https://www.drupal.org/project/social_api)
 * Required library: The League OAuth2 client library


INSTALLATION
------------

 * To download the module with all dependencies run the below command:
  composer require drupal/social_post:^2.0

 * Install the module from admin module page (Administration » Extend) or using
  the drush command:
  drush en social_post


CONFIGURATION
-------------

 * Configure user permissions in Administration » People » Permissions:
  - Delete Social Post user accounts

    Users in roles with the "Delete Social Post user accounts" permission can
    delete all user accounts associated to all Drupal users.

  - Delete own Social Post user accounts

    Users in roles with the "Delete own Social Post user accounts" permission
    can delete own Social Post user accounts.

  - View Social Post user entity lists

    Users in roles with the "View Social Post user entity lists" permission can
    view Social Post user entity lists.

 * Go to Configuration » Social API settings » Autoposting settings.
  The list looks empty initially as you have not enabled an implementer yet.

 * To post to external providers, you need to install their respective Social
  Post implementers. After installing implementers, they will be displayed in
  the Social Post implementer list.

  Visit https://www.drupal.org/project/social_post to find all the available
  Social Post implementers.


MAINTAINERS
-----------

Current maintainers:
 * Getulio Valentin Sánchez (gvso) - https://www.drupal.org/u/gvso

This project has been supported by:
 * Google Summer of Code
  An annual program for university students organized by Google with projects
  managed by open source organization mentors such as us (Drupal!).
  Visit g.co/gsoc for more information.

 * Google Code-In
  The Google Code-In is a contest to introduce pre-university students (ages
  13-17) to the many kinds of contributions that make open source software
  development possible. Visit g.co/gci for more information.
