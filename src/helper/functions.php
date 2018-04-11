<?php
/**
 * Get translation from translate helper function
 */
function _t($key, ...$args)
{
    $translation = TranslateHelper::getTranslation($key);
    $result = vsprintf($translation, $args);

    return $result;
}

/**
 * Get language from translate helper function
 */
function _lang()
{
    return TranslateHelper::getLanguage();
}

/**
 * Alias of htmlspecialchars helper function
 */
function _h($text)
{
    return htmlspecialchars($text, ENT_COMPAT);
}
