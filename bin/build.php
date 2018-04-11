<?php

chdir('../');

$unzipper = file_get_contents('index.php');
$translations = (require 'src/lang/translations.php');
$languages = array_keys($translations);

function base64Uri($path) {
    if (! file_exists($path)) {
        return false;
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

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
