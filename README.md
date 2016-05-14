# ASG Assessment

### Installation

1. For the sake of completion, the ``/vendor`` directory is included in the submission as well as the git repo, but this should never be the case for a production application. If the folder does not exist or is empty, please install using the following in the root directory of the project:
``composer install``.
2. Please enable/install the GD image library for PHP. For Linux, see: http://www.cyberciti.biz/faq/ubuntu-linux-install-or-add-php-gd-support-to-apache/

3. Restore the MySQL database from the ``/database/asg.sql`` file. Configure the database settings in the ``/src/startup.php`` file to correspond to your working environment. The specific fields to edit are:

```
/**
*   Initialize DB.
*/
DB::$user = 'root';
DB::$password = 'password';
DB::$dbName = 'asg';
DB::$error_handler = 'error_handler';
```

### Note on included files

Ideally you would have a ``.gitignore`` file to exclude the ``/vendor``, ``/src/templates/cache`` and so forth. For the sake of having a working project without the need of further technologies such as Composer, I have submitted everything to this repo. For production solutions a ``.gitignore`` file is absolutely mandotory.

### Running the application

Please navigate to the ``/public`` folder and start your server from this directory. With PHP it's as easy as: ``php -S localhost:8000`` or similar configuration.

### Technologies used

* FastRoute for routing. https://github.com/nikic/FastRoute
* Twig templating engine. http://twig.sensiolabs.org/
* MeekroDB as database layer. http://meekro.com/
* Intervention image manipulation library. http://image.intervention.io/
