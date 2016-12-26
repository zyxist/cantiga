Managing mail templates
=======================

E-mail notifications sent by Cantiga can be customized by system administrators:

1. go to *admin workspace*,
2. expand **Settings** section from the *workspace menu*,
3. select **Mail templates** link.

When you create a new mail template, you must select the place where the e-mail is sent (not to be confused with *projects*, *groups* and *areas*), from the predefined list. The e-mails can be internationalized by creating multiple templates for the same place, but with different locales (language codes, such as *en*, *de*, *fr*, *pl*).

The mail template content is written in Twig template engine syntax, the same as used by Cantiga for rendering the HTML pages. Twig offers a rich language for rendering the message, but the basic usage is really simple. You write the template as a regular HTML page, with embedded placeholders, where custom content shall be displayed. You can take a look at the default mail templates to learn what placeholders are available in each place.

The Twig documentation for template designers can be found here: http://twig.sensiolabs.org/doc/templates.html

**Note**: mail templates are shared by all the projects in the system. It is not possible to create a mail template specific for a single project.

**Hint**: if the template contains a syntax error, Cantiga is not able to generate a mail template, and the user sees an error message. Do the modifications carefully and always test your templates before applying them to the production system.
