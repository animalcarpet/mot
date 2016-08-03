MOT PHP Documentation
=====================

MOT documentation generated from PHP sources using [Sami](https://github.com/FriendsOfPHP/Sami).

Requirements
------------

[PHP 5.5+](https://secure.php.net/) and [Composer](https://getcomposer.org/).

Installation
------------

    $ composer install

Building documentation
----------------------

To build documentation for the API and Frontend applications at once execute:

    $ make
    
The documentation built will be available in the `api/build/` and `frontend/build/` subfolders.

    $ open api/build/index.html
    $ open frontend/build/index.html
    
To build API only:

    $ make api
    
To build Frontend only:

    $ make frontend
    
To clean generated files:

    $ make clean
    
