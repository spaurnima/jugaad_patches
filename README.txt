CONTENTS OF THIS FILE
---------------------
Introduction
Requirements
Installation
Configuration


INTRODUCTION
===============
Jugaad Pathches provides a way to add products and their links as contents
in the site and using that URL ,QR code will gets generated and
placed in block besides Product information.

REQUIREMENTS
===============
- This module requires no modules outside of Drupal core.
- Install endroid/qr-code package from packagist.org using: composer
  require endroid/qr-code

INSTALLATION
===============
Download the module in your module/custom folder.
Install the module as you would normally install a contributed
Drupal module.
- Add below snippet in your main composer.json file and run the
      composer require drupal/jugaad_patches:dev-main to download the module
    and it's dependencies.
      "repositories": [
              {
                  "type": "vcs",
                  "url": "git@github.com:spaurnima/jugaad_patches"
              }
          ],

CONFIGURATION
===============
1. Navigate to Administration > Extend and enable the module.
The Products content type with fields will get created.
2. Also you can navigate to product-list which is front page of the site.
