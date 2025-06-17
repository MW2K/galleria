# galleria
Practice files for a PHP gallery application. This requires phpdotenv from vlucas/phpdotenv although you're free to adapt any method of hiding credentials you want.
It works but needs lots of work to make it viable on the web, not the least of which is the total lack of styling. I personally recommend W3school's w3.css for styling. 

Needed: PHP > 7, PHP composer, vlucas/phpdotenv, a database - I use MariaDB but you're free to adapt to whatever you wish.

# File structure
 The public/ folder is meant to sit in your doc_root. Everything else sits one directory above, outside the WWW. Adapt as needed.
 The private/ folder contains the gallery configuration, the CSRF token auth, and the gallery functions
 The admin/ folder contains tools needed by an admin of the gallery
 The vendor/ folder is for the composer installs of phpdotenv, et. al. 
 test.php creates a test admin to use to configure the gallery. Naturally, it's not to be used in a live environment.
 composer.json contains the logic to install vlucas/phpdotenv - run it using `composer install`
