Imbo - Image box
================

Imbo is an image "server" that can be used to add/get/delete images using a RESTful HTTP API. There is also support for adding meta data to the images stored in Imbo. The main idea behind Imbo is to have a place to store high quality original images and to use the API to fetch variations of the images. Imbo will resize, rotate and crop (amongst other transformations) images on the fly so you won't have to store all the different variations. See :ref:`image-transformations` for a complete list of the supported transformations.

Imbo is an open source project written in `PHP`_ and is `available on GitHub`_. If you find any issues or missing features please add an issue in the `issue tracker`_.

Feel free to join the #imbo channel on the `Freenode IRC network`_ (chat.freenode.net).

.. _PHP: http://php.net/
.. _available on GitHub: http://github.com/imbo/imbo
.. _issue tracker: https://github.com/imbo/imbo/issues
.. _Freenode IRC network: http://freenode.net

Documentation
-------------
.. toctree::
   :maxdepth: 3

   usage/requirements
   usage/installation
   usage/configuration
   usage/api

Extending Imbo
--------------
.. toctree::
   :maxdepth: 2

   advanced/cache_adapters
   advanced/custom_database_drivers
   advanced/custom_storage_drivers
   advanced/custom_event_listeners
