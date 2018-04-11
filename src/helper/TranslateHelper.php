<?php
/**
 * TranslateHelper
 *
 * Helps manage translations.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.2.0
 */
class TranslateHelper
{
    private static $language;
    private static $defaultlanguage = 'en';
    private static $availableLanguages = ['en', 'pl', 'de', 'es', 'ru'];
    private static $translationsFileName = 'src/lang/translations.php';
    private static $translations;

    private function __construct() {}

    private static function findTranslation($key)
    {
        return (! empty(self::$translations[self::$language][$key])) ? self::$translations[self::$language][$key] : str_replace('_', ' ', ucfirst($key));
    }

    private static function setLanguageFromGet($name)
    {
        if (! empty($_GET[$name])) {
            self::$language = (in_array($_GET[$name], self::$availableLanguages)) ? $_GET[$name] : self::$defaultlanguage;
        }
    }

    private static function setLanguageFromUri()
    {
        $args = explode('/', $_SERVER['REQUEST_URI']);
        $cleanArgs = array_filter($args, function ($value) { return $value !== ''; });
        $lastArg = array_pop($cleanArgs);
        if (in_array($lastArg, self::$availableLanguages)) {
            self::$language = $lastArg;
        }
    }

    private static function setLanguageFromBrowser()
    {
        self::$availableLanguages = array_flip(self::$availableLanguages);
        $languagesWeight = [];
        preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            list($a, $b) = explode('-', $match[1]) + ['', ''];
            $value = isset($match[2]) ? (float) $match[2] : 1.0;
            if (isset(self::$availableLanguages[$match[1]])) {
                $languagesWeight[$match[1]] = $value;
                continue;
            }
            if (isset(self::$availableLanguages[$a])) {
                $languagesWeight[$a] = $value - 0.1;
            }
        }
        if ($languagesWeight) {
            arsort($languagesWeight);
            self::$language = key($languagesWeight);
        }
    }

    private static function setLanguage()
    {
        self::setLanguageFromGet('lang');
        if (empty(self::$language)) {
            self::setLanguageFromUri();
        }
        if (empty(self::$language)) {
            self::setLanguageFromBrowser();
        }
        if (empty(self::$language)) {
            self::$language = self::$defaultlanguage;
        }
        if (! empty(LANGUAGE)) {
            self::$language = LANGUAGE;
        }
    }

    private static function readTranslations($fileName)
    {
        if (! file_exists($fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($extension != 'php') {
            return false;
        }

        return (include $fileName);
    }

    private static function setTranslations()
    {
        self::$translations = (! empty(TRANSLATIONS)) ? TRANSLATIONS : self::readTranslations(self::$translationsFileName);
    }

    public static function getTranslation($key)
    {
        self::setLanguage();
        self::setTranslations();
        return self::findTranslation($key);
    }

    public static function getLanguage()
    {
        self::setLanguage();
        return self::$language;
    }
}
