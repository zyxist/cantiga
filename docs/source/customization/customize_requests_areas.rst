.. _customize_requests_areas:

Customizing area requests and areas
===================================

The structure of the area profile and area requests can be programatically customized. These customizations do not require the database schema change, and different projects can use different form structures. This article assumes that you have already created a custom Symfony bundle.

---------------------------
Modifying the area requests
---------------------------

Area request form can be customized by implementing a new Symfony service that implements ``CustomFormModelInterface``. The interface has four methods:

 * ``constructForm()`` - defines the Symfony form structure,
 * ``validateForm()`` - defines the custom validation rules,
 * ``createFormRenderer()`` - creates the form renderer which is responsible for the final layout of the form,
 * ``createSummary()`` - creates the summary renderer which is responsible for displaying the entered data in the area request detail page.

As a starting point, you can use the following class: ``Cantiga\CoreBundle\CustomForm\DefaultAreaRequestModel``.

The custom form shall be registered in Cantiga in the service configuration::

    mybundle.form.area_request:
        class:     MyBundle\CustomForm\AreaRequestModel
        tags:
            - { name: cantiga.extension, point: core.form.area-request, module: mymodule, description: "My custom area request form" }

Finally, modify the ``boot()`` method in your bundle class:

    CustomForms::registerService('mymodule:area-request-form', 'mybundle.form.area_request');

-----------------------
Modifying area profiles
-----------------------

Area profiles can be modified in a similar way, by creating a new Symfony service that implements ``CustomFormModelInterface``. The service shall be registered in ``core.form.area`` extension point, instead of ``core.form.area-request``, and similarly added in ``boot()`` method.

The area profile custom form may optionally implement ``CompletenessCalculatorInterface`` which adds one method: ``calculateCompleteness()``. This method takes an array of all the form fields, with their values, and shall return a number from range [0, 100] that describes the completeness of the area profile in percents. Cantiga draws a nice progress bar in the area profile editor and in the area management panels.

Once you created your custom forms, you can use them in your project. As a project manager:

1. go to the *project workspace*,
2. expand **Manage** section from the *workspace menu*,
3. select **Settings**,
4. modify **Area form** and **Area request form** properties: select the custom forms you have created.
