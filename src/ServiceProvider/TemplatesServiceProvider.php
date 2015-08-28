<?php
namespace ServiceProvider;

use League\Plates\Engine;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Template\Extension\FinderExtension;

class TemplatesServiceProvider implements ServiceProviderInterface
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
        $pimple['templates'] = function ($c) {
            $templatesDir = __DIR__ . '/../../templates';
            $templates    = new Engine($templatesDir, 'phtml');

            $templates->addFolder('shared', $templatesDir . '/shared');
            $templates->loadExtension(new FinderExtension($c));

            return $templates;
        };
    }
}