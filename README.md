Cantiga Project
===============

A collaboration tool for managing distributed social projects. Originally, we have developed it to manage one of our biggest social projects
that originated in our community, with more than 60 local editions in whole country. We decided to make it an open-source project to boost
its development and allow the leaders of those editions to join the development process (if they know PHP, of course).

How it works?
-------------

Cantiga allows creating multiple social projects, which can have their local editions, called *areas*. In addition, the areas can be
combined into groups. Each project, group and area, has its own *workspace*, where the members can perform different activities and
communicate one with another. The membership is granted by area/group/project managers through sending an invitation. The newly
registered users can also request creating a new area in the given project.

Different functionalities are being built around this model. Right now the project is in the early development phase, but we are
constantly extending it to meet our needs.

Technical details
-----------------

The project requires at least PHP 5.6 in order to work, and is written in Symfony 2.7. In the near future, we are planning to migrate
to Symfony 3.0, and test the compatibility with PHP 7.

Cantiga is designed to be extensible. You can create new Symfony bundles that hook into various extension points, so that it is possible
to add new functionality without the need to hack the core. Actually, you can find the bundles specific to our community here, in this
repository as well, together with generic bundles.

Once you clone the repository, don't forget to populate the `vendor` directory using Composer!

Contributing
------------

1. Login or register on GitHub
2. Raise a ticket in the issue tracker
3. Read our coding guidelines
4. Send us a pull request

License and copyright
---------------------

The project is distributed under the terms of GNU General Public License v3. You can find the full text of the license
in `license/CANTIGA-LICENSE` file.

Put simply, *there's more happiness in giving than in getting*. We give you a nice software (at least we think so), with the right to use it
and modify for any purpose. We expect that if you distribute it further, possibly with your modifications, you'll give it
to the others under exactly the same terms and won't restrict the others' rights.

Practically:
 - you can run your own private website, make some modifications to the source code and keep them private,
 - if you want to allow the others to install the original or modified version, you must give them the full source code, too,
   and you must not change the licensing terms.

Copyright 2015 Tomasz Jędrzejewski