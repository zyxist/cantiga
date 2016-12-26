.. _data_export:

Data export
===========

Cantiga offers a way to export certain data periodically to external systems via REST interface.

-----------------------------
Specifying the data to export
-----------------------------

The exported data is defined programatically. By default, all the area information is exported, and you can add additional data blocks. You need two things:

1. some code to extract the necessary data from the database,
2. a listener for ``ExportEvent``.

Below, you can find a sample implementation of the event listener::

   public function onProjectExported(ExportEvent $event)
   {
      $territoryBlock = $this->repo->exportTerritories($event->getProjectId());
      $event->addBlock('territory', $territoryBlock);
   }

The event is pre-populated with the list of areas modified since the previous data export. All the data to export shall be added as new instances of `ExportBlock` class in the event. The class provides a way to store the information about the ID-s of the rows that are still present in the database (to detect removed rows), and the detailed information of all the rows modified or inserted since the last export.

Registering the listener in the framework::

    mybundle.export_listener:
         class: MyBundle\EventListener\ExportListener
         arguments: ["@some_custom_repository"]
         tags:
            - { name: kernel.event_listener, event: cantiga.export.ongoing, method: onProjectExported }

--------------------
Export configuration
--------------------

Data export configuration can be done by system administrators:

1. go to the *admin workspace*,
2. expand **Manage** section from the *workspace menu*,
3. select **Export settings**,
4. click **Insert** button,
5. specify the export settings.

The exporter is defined for the specified project and areas with certain status. You must provide the destination URL and the encryption key. The payload is encrypted with AES-256-CBC algorithm, and the destination URL must be able to decrypt it using the same key. The key must be encoded in Base64.

To run the actual export, you must call the following console command which shall be scheduled in Cron::

   php bin/console cantiga:export-data

-------------
Output format
-------------

The destination URL shall accept POST requests with ``text/plain`` payload. The actual payload is a string encoded with Base64, and then encrypted with AES-256-CBC algorithm using the specified encryption key. The encoded content is a JSON with the following structure::

   {
      'block1': {
         'ids': [ ids of present rows ],
         'update': [ modified or inserted rows ],
      },
      'block2': {
         'ids': [ ids of present rows ],
         'update': [ modified or inserted rows ],
      }
      ...
   }
