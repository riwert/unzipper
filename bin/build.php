<?php

$unzipper = file_get_contents('index.php');
$translations = (require 'src/lang/translations.php');
$languages = array_keys($translations);

// Convert image to Base64
function base64Uri($path) {
    if (! file_exists($path)) {
        return false;
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// Loop through all languages
foreach ($languages as $language) {
    // Localized file name
    $unzipperLocalized = 'unzipper.' . $language . '.php';

    // Initial content
    $content = $unzipper;

    // Translation constants
    $translation = var_export($translations[$language], true);
    $languageReplace = 'const LANGUAGE = ' .  "'" . $language . "';";
    $translationReplace =  'const TRANSLATIONS = [' . "\n'" . $language . "' => " . $translation . '];';

    $content = str_replace("const LANGUAGE = '';", $languageReplace, $content);
    $content = str_replace("const TRANSLATIONS = [];", $translationReplace, $content);

    // Translate helper
    $translateHelperReplace = file_get_contents('src/helper/TranslateHelper.php');
    $translateHelperReplace = str_replace('<?php'."\n", '', $translateHelperReplace);

    $content = str_replace("include 'src/helper/TranslateHelper.php';", $translateHelperReplace, $content);

    // Helper functions
    $helperFunctionsReplace = file_get_contents('src/helper/functions.php');
    $helperFunctionsReplace = str_replace('<?php'."\n", '', $helperFunctionsReplace);

    $content = str_replace("include 'src/helper/functions.php';", $helperFunctionsReplace, $content);

    // UnZipper class
    $unZipperReplace = file_get_contents('src/class/UnZipper.php');
    $unZipperReplace = str_replace('<?php'."\n", '', $unZipperReplace);

    $content = str_replace("include 'src/class/UnZipper.php';", $unZipperReplace, $content);

    // Template layout
    $templateReplace = file_get_contents('src/html/layout.html.php');
    $templateReplace = '?>'."\n".$templateReplace;
    $templateReplace = str_replace('<?php'."\n", '', $templateReplace);
    $templateReplace = str_replace('src/img/favicon.ico', base64Uri('src/img/favicon.ico'), $templateReplace);

    $content = str_replace("include 'src/html/layout.html.php';", $templateReplace, $content);

    // Save file
    file_put_contents($unzipperLocalized, $content);
}
