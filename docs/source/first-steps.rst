First steps
===========

To start using the application you need to install PHP 7 with extensions listed in `composer.json` file (see entries ext-{name}),
composer, and a database engine - SQLite3 or MySQL server.

You can also use a ready-to-use docker container instead of using host installation of PHP, **if you have a possibility always use a docker container**.

Summary of application requirements:

- PHP7.2 or newer
- SQLite3 or MySQL database
- Composer (PHP package manager, see packagist.org)
- make (GNU Make)

Manual installation
===================

At first you need to create your own customized `.env` file with application configuration.
You can create it from a template `.env.dist`.

Make sure the **APP_ENV** is set to **prod** for time when your application is online, for the configuration time please
set **APP_ENV** to **dev**.

.. code:: shell

    cp .env .env.dist
    edit .env

To install the application - download dependencies, install database schema use the make task **install**.

.. code:: bash

    make install

All right! The application should be ready to go. To check the application you can launch a **development web server**.

.. code:: bash

    make run_dev



Installation with docker
========================

You have at least three choices:

- Use `wolnosciowiec/file-repository` container by your own (advanced)
- Use a prepared docker-compose environment placed in `examples/docker` directory
- Create your own environment based on docker-compose

Proposed way to choose is the prepared docker-compose environment that is placed in `examples/docker` directory.
Here are instructions how to start with it:

.. code:: bash

    # go to the environment directory and copy template file
    cd ./examples/docker
    cp .env.dist .env

    # adjust application settings
    edit .env


Now adjust the environment variables to your need - you might want to see the configuration reference.
If you think the configuration is finished, start the environment. To stop it - type CTRL+C.

.. code:: bash

    # start the environment
    make start


Example docker-compose.yml file:

.. literalinclude:: ../../examples/docker/docker-compose.yml
   :language: yaml
   :linenos:


Post-installation
=================

At this point you have the application, but you do not have access to it.
**You will need to generate an administrative access token** to be able to create new tokens, manage backups, upload files to storage.
To achieve this goal you need to set up the application to be temporarily working in **dev mode** - set *APP_ENV=dev* to do it.

When you will turn on **dev mode** you can use a special token **"test-token-full-permissions"** to create your unique administrative token.

Apply configuration change:

.. code:: bash

    APP_ENV=dev


Now check all available roles in the application:


.. code:: bash

    GET /auth/roles?_token=test-token-full-permissions

:ref:`Note: If you DO NOT KNOW HOW to perform a request, then please check the postman section <postman>`

You should see something like this:

.. code:: json

    {
        "roles": {
            "upload.images": "Allows to upload images",
            "upload.documents": "Allows to upload documents",
            "upload.backup": "Allows to submit backups",
            "upload.all": "Allows to upload ALL types of files regardless of mime type",
            "security.authentication_lookup": "User can check information about ANY token",
            "security.overwrite": "User can overwrite files",
            "security.generate_tokens": "User can generate tokens with ANY roles",
            "security.use_technical_endpoints": "User can use technical endpoints to manage the application",
            "deletion.all_files_including_protected_and_unprotected": "Delete files that do not have a password, and password protected without a password",
            "view.any_file": "Allows to download ANY file, even if a file is password protected",
            "view.files_from_all_tags": "List files from ANY tag that was requested, else the user can list only files by tags allowed in token",
            "view.can_use_listing_endpoint_at_all": "Define that the user can use the listing endpoint (basic usage)",
            "collections.create_new": "Allow person creating a new backup collection",
            "collections.allow_infinite_limits": "Allow creating backup collections that have no limits on size and length",
            "collections.modify_any_collection_regardless_if_token_was_allowed_by_collection": "Allow to modify ALL collections. Collection don't have to allow such token which has this role",
            "collections.view_all_collections": "Allow to browse any collection regardless of if the user token was allowed by it or not",
            "collections.can_use_listing_endpoint": "Can use an endpoint that will allow to browse and search collections?",
            "collections.manage_tokens_in_allowed_collections": "Manage tokens in the collections where our current token is already added as allowed",
            "collections.upload_to_allowed_collections": "Upload to allowed collections",
            "collections.list_versions_for_allowed_collections": "List versions for collections where the token was added as allowed",
            "collections.delete_versions_for_allowed_collections": "Delete versions only from collections where the token was added as allowed"
        }
    }

Pick your roles (eg. all) and generate an administrative token for unlimited management of the application when it is in **APP_ENV=prod** mode.

.. code:: bash

    POST /auth/token/generate?_token=test-token-full-permissions
    {
        "roles": ["upload.images", "upload.documents", "collections.create_new", "collections.modify_any_collection_regardless_if_token_was_allowed_by_collection"],
        "data": {
            "tags": [],
            "allowedMimeTypes": [],
            "maxAllowedFileSize": 0
        }
    }

As the response you should get the token id that you need.

.. code:: json

    {
        "tokenId": "34A77B0D-8E6F-40EF-8E70-C73A3F2B3AF8",
        "expires": null
    }

**Remember the tokenId** and use it as your main token that could not only upload files, but also be able to create new tokens
to grant other persons to limited set of actions.

Now **you should switch back the application to the production mode** in `.env` file:

.. code:: bash

    APP_ENV=prod


That's all.
