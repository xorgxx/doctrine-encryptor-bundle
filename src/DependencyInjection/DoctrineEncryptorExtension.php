<?php

	namespace DoctrineEncryptor\DoctrineEncryptorBundle\DependencyInjection;

	use Exception;
	use Symfony\Component\Config\FileLocator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Extension\Extension;
	use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\DependencyInjection\Reference;

    class DoctrineEncryptorExtension extends Extension
	{

		/**
		 * @inheritDoc
		 * @throws Exception
		 */
		public function load( array $configs, ContainerBuilder $container ) :void
		{

			$loader         = new YamlFileLoader( $container, new FileLocator(__DIR__ . "/../Resources/config") );
			$loader->load("services.yaml");

            $configuration  = $this->getConfiguration($configs, $container);
            $config         = $this->processConfiguration($configuration, $configs);

            // set key config as container parameters
            foreach ($config as $key => $value) {
                $container->setParameter( 'doctrine_encryptor.' . $key, $value);
            }
        
		}
	}