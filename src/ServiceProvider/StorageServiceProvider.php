<?php
namespace ServiceProvider;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use League\Flysystem\Filesystem;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class StorageServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['storage'] = function ($c) {
            $adapter = new CachedAdapter(new Local(__DIR__ . '/../../content'), new Memory);

            return new Filesystem($adapter);
        };
    }
}