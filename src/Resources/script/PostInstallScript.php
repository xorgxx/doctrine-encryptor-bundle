<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Resources\script;

    use Symfony\Component\Yaml\Yaml;

    class PostInstallScript
    {
        public static function doctrineEncryptor()
        {
            // Chemin vers le fichier de configuration YAML de votre bundle pour Doctrine Encryptor
            $source     = dirname(__DIR__, 6). '/config/packages/doctrine_encryptor.yaml';
            $targetFile = dirname(__DIR__, 1). '/config/setup/doctrine_encryptor.yaml';
            
            if (!file_exists($source)) {
                $config = Yaml::parseFile($targetFile);
                file_put_contents($source, Yaml::dump($defaultConfig));
            } else {
                $existingConfig = Yaml::parseFile($source);
                $mergedConfig   = array_merge_recursive($targetFile, $existingConfig);
                file_put_contents($source, Yaml::dump($mergedConfig));
            }
        }

        public static function gaufrette()
        {
            // Chemin vers le fichier de configuration YAML de votre bundle pour Doctrine Encryptor
            $source     = dirname(__DIR__, 6) . '/config/packages/gaufrette.yaml';
            $targetFile = dirname(__DIR__, 1) . '/config/setup/gaufrette.yaml';

            if (!file_exists($source)) {
                $config = Yaml::parseFile($targetFile);
                file_put_contents($source, Yaml::dump($defaultConfig));
            } else {

                $existingConfig = Yaml::parseFile($source);
                $mergedConfig   = array_merge_recursive($targetFile, $existingConfig);
                file_put_contents($source, Yaml::dump($mergedConfig));
            }

        }
    }