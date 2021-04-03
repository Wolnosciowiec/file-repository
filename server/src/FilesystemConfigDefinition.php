<?php declare(strict_types=1);

namespace App;

/**
 * <docs>
 * Configuring filesystem adapters
 * ===============================
 *
 * Filesystem adapters extensible configuration allows to configure multiple adapters, but use up to two of them.
 * There are two slots: READ-ONLY (RO) and READ-WRITE (RW)
 *
 * **Each adapter configuration has a slug, and can be used in RW/RO slots:**
 *
 * .. code:: shell
 *
 *     FS_RW_NAME=SOMESLUG  # define SOMESLUG to be used as READ-WRITE adapter
 *     FS_SOMESLUG_ADAPTER="aws"
 *     # ...
 *     FS_SOMESLUG_BUCKET="..."
 *
 *
 * **Available adapter slots:**
 *
 *     - FS_RW_NAME: Default read-write adapter
 *     - FS_RO_NAME: Optional read-only adapter
 *
 *
 * **Google Cloud example:**
 *
 * .. code:: shell
 *
 *    FS_RW_NAME=GC
 *    FS_GC_ADAPTER=gcloud
 *    FS_GC_PREFIX=
 *    FS_GC_BUCKET=backups-files-storage
 *    FS_GC_KEYFILEPATH=/home/backuprepository/gcs-service-account.json
 *    FS_GC_PROJECTID=backups-hosting
 *
 * </docs>
 */

class FilesystemConfigDefinition
{
    public static function get(): array
    {
        return [
            'local' => [
                'directory'   => ['%kernel.root_dir%/../var/uploads', 'string'],
                'permissions' => [
                    [
                        'file' => [
                            [
                                'public' => ['0644', 'string'],
                                'private' => ['0600', 'string']
                            ]
                        ],
                        'dir' => [
                            [
                                'public' => ['0755', 'string'],
                                'private' => ['0700', 'string']
                            ]
                        ]
                    ]
                ],
                'lock'        => [false, 'bool'],
                'skip_links'  => [true, 'bool']
            ],

            'aws' => [
                'client'  => ['', 'string'], // service name, autogenerated when empty
                'bucket'  => ['', 'string'],
                'prefix'  => ['', 'string'],

                // those goes as $args to S3Client
                'credentials' => [
                    [
                        'key'    => ['', 'string', false],
                        'secret' => ['', 'string', false]
                    ], 'array', false
                ],

                'region'   => ['eu-central-1', 'string', false],
                'version'  => ['latest', 'string', false],
                'endpoint' => [null, 'string', false],
                'options' => [
                    [
                        '@http' => [
                            [
                                'version' => ['1.0', 'string']
                            ]
                        ]
                    ]
                ]
            ],

            'gcloud' => [
                'client'      => ['', 'string'], // service name, autogenerated when empty
                'bucket'      => ['', 'string'],
                'prefix'      => ['', 'string'],

                // those goes as $config to StorageClient
                'projectId'   => [null, 'string', false],
                'keyFilePath' => [null, 'string', false],
            ]
        ];
    }
}
