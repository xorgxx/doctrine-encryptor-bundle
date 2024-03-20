<?php

    namespace DoctrineEncryptor\DoctrineEncryptorBundle\Resources\script;

    use Symfony\Component\Yaml\Yaml;
    require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
    
    class PostInstallScript
    {
        public static function doctrineEncryptor(): void
        {
            // Chemin vers le fichier de configuration YAML de votre bundle pour Doctrine Encryptor
            $source = dirname(__DIR__, 6) . '/config/packages/doctrine_encryptor.yaml';
            $targetFile = dirname(__DIR__, 1) . '/config/setup/doctrine_encryptor.yaml';

            // Charger les configurations existantes
            $targetConfig = Yaml::parseFile($targetFile);
            $sourceConfig = file_exists($source) ? Yaml::parseFile($source) : [];

            // Fusionner les configurations
            $mergedConfig = self::recursiveMerge($sourceConfig, $targetConfig);

            file_put_contents($source, Yaml::dump($mergedConfig, 4));

        }

        public static function gaufrette(): void
        {
            $source = dirname(__DIR__, 6) . '/config/packages/gaufrette.yaml';
            $targetFile = dirname(__DIR__, 1) . '/config/setup/gaufrette.yaml';

            // Charger les configurations existantes
            $targetConfig = Yaml::parseFile($targetFile);
            $sourceConfig = file_exists($source) ? Yaml::parseFile($source) : [];

            // Fusionner les configurations
            $mergedConfig = self::recursiveMerge($sourceConfig, $targetConfig);

            // Écrire la configuration fusionnée dans le fichier de destination
            file_put_contents($source, Yaml::dump($mergedConfig, 4));
        }
        private static function recursiveMerge(array $source, array $target): array
        {
            foreach ($source as $key => $value) {
                if (is_array($value) && isset($target[$key]) && is_array($target[$key])) {
                    // Si la clé existe dans les deux configurations et que les deux valeurs sont des tableaux,
                    // fusionner récursivement les sous-tableaux
                    $target[$key] = self::recursiveMerge($value, $target[$key]);
                } elseif (!isset($target[$key])) {
                    // Si la clé n'existe pas déjà dans la configuration cible, l'ajouter
                    $target[$key] = $value;
                }
            }

            return $target;
        }
    }