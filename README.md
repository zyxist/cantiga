[![Build Status](https://travis-ci.org/zyxist/cantiga.svg?branch=master)](https://travis-ci.org/zyxist/cantiga)

Cantiga Project
===============

[![Join the chat at https://gitter.im/zyxist/cantiga](https://badges.gitter.im/zyxist/cantiga.svg)](https://gitter.im/zyxist/cantiga?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Cantiga Project is an open-source membership management software. Its primary target are non-profit organizations which not only organize
various social projects/events, but also offer them to other local communities as a franchise. The software provides tools for the franchise management:

 * registration and moderation of local editions,
 * on-line trainings for leaders of local editions,
 * progress monitoring,
 * membership management,
 * collaboration tools,
 * statistics.

Cantiga was originally written to support the coordination of a big, nationwide social project with more than 100 local editions,
and 30 000 participants. Now it is available for everyone who needs to run such a project, and we are adding new features to cover
new use cases.

How it works?
-------------

In Cantiga, you can define social projects which can have their local editions, called *areas*, as the local edition is usually held
for a local community in certain geographical area. The local leaders who are interested in running such an area in their neighbourhood,
can register and send an area creation request. Project members moderate the request and approve it or reject. Once approved, the leader
gets an access to the collaboration tools, on-line trainings, and other tools necessary to manage the area. At the same time, project
members can manage the project as a whole, and track the progress. If your project is too big to manage the areas directly, you can
combine several areas into groups. Each group has a designated local leader who is responsible for the areas assigned to it.

If your event is held on a recurring basis (i.e. once a year), Cantiga assumes creating separate projects for each occurrence,
and archiving the old data. This allows a greater flexibility in adjusting the project structure to the changing needs, as you do not
have to take care of archived data. The leaders who are interested in participating again, can import the previously created area
to the new project.

Different features are being designed around the collaboration model presented above. In addition, we are preparing to provide
a support for projects that consist of groups only and do not use the area functionality.

Technical details
-----------------

Currently, the project requires at least PHP 7.0 and MySQL/MariaDB database in order to work. Originally written in Symfony 2.7, now
runs on Symfony 3.0, with planned migration to Symfony 3.1. The user interface is built on Bootstrap 3 and AdminLTE admin theme with
some additional customizations.

Extensions are first-class citizens in Cantiga ecosystem. The core provides only the basic project-group-area model, and user management,
whereas all the other features are provided as separate modules. You can create new Symfony bundles with new features which hook into
the existing extension points without the need to hack the core. In addition, the modules can be enabled and disabled for each project
independently.

Once you clone the repository, don't forget to populate the `vendor` directory using Composer!

Features and roadmap
--------------------

Working features:
 - project/area/group management,
 - user management and registration,
 - user invitations to projects, groups and areas,
 - progress tracking via milestones,
 - on-line trainings,
 - statistical and chart engine,
 - exporting the data via REST to external web services.

Planned features:
 - discussion board,
 - event management,
 - user activity tracking,
 - task management,
 - mass mailing tools.

Contributing
------------

1. Login or register on GitHub
2. Raise a ticket in the issue tracker
3. Read our coding guidelines
4. Send us a pull request

License and copyright
---------------------

The project is distributed under the terms of GNU General Public License 3. You can find the full text of the license
in `license/CANTIGA-LICENSE` file.

Put simply, *there's more happiness in giving than in getting*. We give you a nice software (at least we think so), with the right to use it
and modify for any purpose. We expect that if you distribute it further, possibly with your modifications, you'll give it
to the others under exactly the same terms and won't restrict the others' rights.

Practically:
 - you can run your own private website, make some modifications to the source code and keep them private,
 - if you want to allow the others to install the original or modified version, you must give them the full source code, too,
   and you must not change the licensing terms.

Copyright 2015-2016 Tomasz Jędrzejewski