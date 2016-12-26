Managing areas
==============

The project and group members can manage the areas in their respective workspaces. Group members can access only the areas that are assigned to their group, whereas project members see all the areas in the project. The area management panel can be found under **Data > Areas** in the *workspace menu*.

-----------
Area status
-----------

Each area can have some status, which can be defined by project managers in **Manage > Area status** page. Cantiga allows customizing the name of the status and the color of their labels. The names of the colors are specified below:

 * ``primary`` - blue label,
 * ``success`` - green label,
 * ``warning`` - yellow label,
 * ``danger`` - red label,
 * ``default`` - grey label.
 
The status can be used for multiple purposes. An example workflow suggestion is presented below:

1. new areas have the *New* status,
2. when the area leaders take part in trainings and courses, their areas can have the status *Training*,
3. when the training is done, the area status can be changed to *Active*,
4. if the given area fails to start, the status can be set to *Inactive*.

-----------------------
Automatic status change
-----------------------

Setting the status manually for a big number of areas may be time-consuming and frustrating. Fortunately, Cantiga can set the area status automatically, depending on reaching certain milestones in the project. To read more about milestones, see :ref:^creating_milestones^ page.

The rules for automatic status change are defined by project managers in **Manage > Status rules** panel. Each rule has the following form:

1. initial status,
2. new status,
3. list of milestones that must be completed to perform the transition,
4. activation order, if there are multiple rules applying to the same initial status. Generally, the rules which require *fewer* milestones, shall have the lower activation order.

**Warning**: the area status does not change immediately once all milestones are completed. The change is done by calling ``cantiga:milestone:update-status`` console command::

   php bin/console cantiga:milestone:update-status

We recommend to schedule this command to be executed every 30 minutes to 1 hour on the web server.
