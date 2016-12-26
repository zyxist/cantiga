Getting started
===============

With some programming skills, Cantiga can be extended with new functionality. The system is written using `Symfony Framework 3.1 <http://symfony.com>`_. If you are familiar with it, you will find it extremely easy to customize the system.

--------------------------
Versioning the source code
--------------------------

Cantiga is developed as a standalone Symfony application. Unfortunately, the way the dependency management works in PHP, makes it hard to keep the source code of your bundled in a version control system.

The best solution for this issue is keeping entire customized Cantiga application in your private repository. Start by cloning the vanilla Cantiga repository from Github, add some modifications, commit them and publish the repository as your own. At any time, you can upgrade to the newer version of Cantiga by pulling the changes from the original, vanilla repository, and merging them into your code.

Below you can find some guidelines for developing your customizations in this way:

1. never modify the original source code in ``src/Cantiga`` directory, and other default files, or you risk merge conflicts during the upgrade to the newer version,
2. remember that we **DO NOT** accept pull requests from customized repositories. Any changes that you wish to add to the core, must be made on a vanilla Cantiga repository,
3. we do periodically update the framework version. To keep your codebase clean, follow the Symfony updates and remove deprecated features as soon as they become deprecated, to avoid migration problems in the future,

-------
Bundles
-------

To extend Cantiga, create a regular Symfony bundle with your customizations. If you are not familiar with Symfony Framework, please take some time to learn the basics of it at `Symfony website <http://symfony.com>`_.

Please note that Cantiga **does not** use Doctrine ORM for database schema management. You can read more about Cantiga API-s in the project wiki on Github.

Usually, your bundle will register an additional module for Cantiga, which can be enabled for the project. To define a module, edit the bundle class and add a ``boot()`` method::

   public function boot()
   {
      Modules::registerModule('mymodule', 'My custom module');
   }
