Configuration
=============

After the installation, Cantiga is operational. However, you would likely want to provide a custom branding and change some settings.

--------
Branding
--------

To change the branding information, edit ``app/Branding.php`` file.

 * ``APP_NAME`` - application name, shown in the top-left corner,
 * ``APP_LOGO`` - path to a custom logo image, relative to ``web/`` directory,
 * ``COPYRIGHT_NOTE`` - copyright note shown in the footer,
 * ``PERSONAL_INFORMATION_OWNER`` - in some legal systems, it is mandatory to provide the detailed information about the owner of the personal information data stored in the system. This text will appear in the footer.

---------
Cron jobs
---------
 
The following command shall be scheduled in Cron to execute every 30 minutes to 1 hour::

   php bin/console cantiga:milestone:update-status

If you want to periodically export the data to an external web service, schedule the following command as well::

   php bin/console cantiga:export-data

You can read more about periodic data export in the following page: :ref:`data_export`.
