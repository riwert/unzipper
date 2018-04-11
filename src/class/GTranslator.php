<?php
/**
 * GTranslator
 *
 * Translate between multiple languages using Google Translate.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.0.0
 */
class GTranslator
{
    const API_URL = 'https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&ie=UTF-8&oe=UTF-8';
    const USER_AGENT = 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1';
    const ITEM_DELIMITER = "\n";

    private $sourceLanguage = 'en';
    private $targetLanguage = 'pl';
    private $targetLanguages = ['pl', 'de', 'es', 'ru'];
    private $fields;
    private $result;
    private $format = ['%d', '%s'];
    private $formatWrap = ['(% d)', '(% s)'];

    public function __construct($sourceLanguage = null, $targetLanguage = null)
    {
        if ($sourceLanguage) {
            $this->sourceLanguage = $sourceLanguage;
        }
        if ($targetLanguage) {
            $this->targetLanguage = $targetLanguage;
        }
    }

    private function fetchText($text)
    {
        if (strlen($text) >= 5000) {
            throw new \Exception('Maximum number of characters exceeded: 5000 is the limit.');
        }

        $fields = array(
            'sl' => urlencode($this->sourceLanguage),
            'tl' => urlencode($this->targetLanguage),
            'q' => urlencode($text)
        );

        $queryFields = http_build_query($fields);
        $postFields = urldecode($queryFields);

        // Open connection
        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        // Execute post
        $this->result = curl_exec($ch);
        // Close connection
        curl_close($ch);
    }

    private function wrapFormat($text)
    {
        return str_replace($this->format, $this->formatWrap, $text);
    }

    private function unWrapFormat($text)
    {
        return str_replace($this->formatWrap, $this->format, $text);
    }

    private function backupFile($fileName, $backupName)
    {
        if (! file_exists($fileName)) {
            throw new \Exception('File: ' . $fileName . ' does not exists.');
        }
        copy($fileName, $backupName);
    }

    private function removeBackupFile($backupName)
    {
        if (! file_exists($backupName)) {
            throw new \Exception('File: ' . $backupName . ' does not exists.');
        }
        unlink($backupName);
    }

    public function getJsonResult()
    {
        return $this->result;
    }

    public function getArrayResult()
    {
        return json_decode($this->result);
    }

    public function getResult()
    {
        $arrayResult = $this->getArrayResult();

        $sentences = $arrayResult->{'sentences'};

        $result = '';
        foreach ($sentences as $sentence) {
            $result .= (isset($sentence->trans)) ? $sentence->trans : '';
        }

        return $result;
    }

    public function translateText($text)
    {
        $this->fetchText($text);

        return $this->getResult();
    }

    public function translateArray($textsArray)
    {
        $textsArray = array_map(function($value) { return $this->wrapFormat($value); }, $textsArray);

        $text = implode(self::ITEM_DELIMITER, $textsArray);
        $this->fetchText($text);
        $result = $this->getResult();
        $resultArray = explode(self::ITEM_DELIMITER, $result);

        $i = 0;
        $translated = [];
        foreach ($textsArray as $key => $value) {
            $translated[$key] = (isset($resultArray[$i])) ? $resultArray[$i] : '';
            $i++;
        }

        $translated = array_map(function($value) { return $this->unWrapFormat($value); }, $translated);

        return $translated;
    }

    public function exportToPhpFile($translationsArray, $fileName)
    {
        $contents = var_export($translationsArray, true);
        file_put_contents($fileName, "<?php\nreturn {$contents};\n");

        return true;
    }

    public function updateTranslations($fileName, $languages = null)
    {
        if (! file_exists($fileName)) {
            throw new \Exception('File: ' . $fileName . ' does not exists.');
        }

        $translations = (require $fileName);

        if (is_string($languages)) {
            $languages = [$languages];
        }

        if (empty($languages)) {
            $languages = $this->targetLanguages;
        }

        foreach ($languages as $language) {
            if (! is_array($translations[$this->sourceLanguage])) {
                throw new \Exception('Translations in source language: ' . $this->sourceLanguage . ' do not exist.');
            }
            $textsArray = $translations[$this->sourceLanguage];
            $this->targetLanguage = $language;
            $translations[$language] = $this->translateArray($textsArray);
        }

        $now = date('YmdHis');
        $backupName = $fileName . '~' . $now;
        $this->backupFile($fileName, $backupName);

        if (! $this->exportToPhpFile($translations, $fileName)) {
            throw new \Exception('Error while exporting translations to file: ' . $fileName . '.');
        }

        echo 'File ' . $fileName . ' has been updated with '
            . implode(', ', $languages)
            . ' language translations.'."\n" ;

        $this->removeBackupFile($backupName);

        return true;
    }
}
