<?php

namespace Majora\Bundle\GeneratorBundle\Generator\ContentModifier;

use Majora\Framework\Inflector\Inflector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Service for updating main routing from a bundle routing file.
 */
class UpdateRoutingModifier extends AbstractContentModifier
{
    protected $filesystem;
    protected $logger;
    protected $resolver;

    /**
     * construct.
     *
     * @param Filesystem      $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem      $filesystem,
        LoggerInterface $logger
    )
    {
        $this->filesystem  = $filesystem;
        $this->logger      = $logger;

        $this->resolver = new OptionsResolver();
        $this->resolver->setDefaults(array(
            'target' => '/config/routing.yml',
            'prefix' => null,
            'host' => null,
        ));
        $this->resolver->setRequired(array(
            'resource', 'route'
        ));
    }

    /**
     * @see ContentModifierInterface::modify()
     */
    public function modify(SplFileInfo $generatedFile, array $data, Inflector $inflector, SplFileInfo $templateFile)
    {
        $options = $this->resolver->resolve($data);

        // retrieve target location
        $targetRoutingFilepath = $this->resolveTargetFilePath(
            $options['target'],
            $generatedFile->getPath()
        );

        // build content
        $routing = sprintf('
%s:
    resource: "%s"
    %s%s',
            $inflector->translate($options['route']),
            $inflector->translate($options['resource']),
            is_null($options['host']) ? '' : sprintf(
                "host: %s\n",
                $options['host']
            ),
            is_null($options['prefix']) ? '' : sprintf(
                "prefix: %s\n",
                $inflector->translate($options['prefix'])
            )
        );

        $routingFile = new SplFileInfo($targetRoutingFilepath, '', '');
        $routingContent = $routingFile->getContents();

        // is routing not already registered ?
        if (strpos($routingContent, trim($routing)) !== false) {
            $this->logger->debug(sprintf(
                'Routing file "%s" is already registered into "%s". Abording.',
                $generatedFile->getFilename(),
                $targetRoutingFilepath
            ));

            return $generatedFile->getContents();
        }

        $this->filesystem->dumpFile(
            $routingFile->getRealpath(),
            sprintf('%s%s', $routingContent, $routing)
        );

        $this->logger->info(sprintf('file updated : %s',
            $routingFile->getRealpath()
        ));

        return $generatedFile->getContents();
    }
}
