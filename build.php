<?php

$unzipper = file_get_contents('unzipper.php');
$translations = (require 'translations.php');
$languages = array_keys($translations);

foreach ($languages as $language) {
    $unzipperLocalized = 'unzipper.' . $language . '.php';
    $translation = var_export($translations[$language], true);
    $languageReplace = 'const LANGUAGE = ' .  '\'' . $language . '\';';
    $translationReplace =  'const TRANSLATIONS = [' . "\n" . '\'' . $language . '\' => ' . $translation . '];';
    $content = $unzipper;
    $content = str_replace('const LANGUAGE = \'\';', $languageReplace, $content);
    $content = str_replace('const TRANSLATIONS = [];', $translationReplace, $content);
    file_put_contents($unzipperLocalized, $content);
}
