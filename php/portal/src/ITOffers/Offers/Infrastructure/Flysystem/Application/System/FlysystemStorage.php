<?php

declare(strict_types=1);

/*
 * This file is part of the itoffers.online project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ITOffers\Offers\Infrastructure\Flysystem\Application\System;

use function array_keys;
use function array_map;
use function file_get_contents;
use ITOffers\Component\Storage\FileStorage;
use ITOffers\Component\Storage\FileStorage\File;
use ITOffers\Offers\Application\Assertion;
use ITOffers\Offers\Application\Exception\Exception;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use function sprintf;

final class FlysystemStorage implements FileStorage
{
    private array $config;

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->config = [];
        $this->filesystem = $filesystem;
    }

    public static function create(array $config) : self
    {
        Assertion::allInArray(['type'], array_keys($config));
        Assertion::inArray($config['type'], ['local', 'azure'], 'Missing or invalid filesystem type');

        switch ($config['type']) {
            case 'local':
                Assertion::allInArray(
                    [
                        'local_storage_path',
                        'storage_url',
                    ],
                    array_keys($config)
                );

                $storage = new self(
                    new Filesystem(
                        new Local(
                            $config['local_storage_path']
                        )
                    )
                );

                break;
            default:
                throw new Exception(sprintf("Invalid storage type %s", $config['type']));
        }

        $storage->config = $config;

        return $storage;
    }

    public function config() : array
    {
        return $this->config;
    }

    public function upload(File $file) : void
    {
        $this->filesystem->put($file->destinationPath(), file_get_contents($file->tmpPath()));
    }

    public function purge() : void
    {
        array_map(
            function (array $file) {
                if ($file['type'] === 'file') {
                    $this->filesystem->delete($file['path']);
                } else {
                    $this->filesystem->deleteDir($file['path']);
                }
            },
            $this->filesystem->listContents()
        );
    }
}
