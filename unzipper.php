<?php
session_start();
/**
 * UnZipper
 *
 * Unzip zip files. One file server side simple unzipper with UI.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.1.1
 */
class UnZipper
{
    private $title = 'UnZipper';
    private $dir = './';
    private $zips = [];
    private $message;
    private $status;
    private $token;
    private $output;

    public function __construct()
    {
        if (! empty($_POST['zipfile']) && $this->verifyToken()) {
            $this->unZip($_POST['zipfile'], $_POST['method']);
        }
        if (! empty($_POST['delfile']) && $this->verifyToken()) {
            $this->delete($_POST['delfile']);
        }
        $this->setToken();
        $this->findZips();
        $this->countZips();
    }

    private function findZips()
    {
        $files = scandir($this->dir);

        foreach ($files as $file) {
            if ($this->checkExtention($file)) {
                $this->zips[] = $file;
            }
        }
    }

    private function countZips()
    {
        if ($this->message) {
            return false;
        }

        $count = count($this->zips);
        if ($count) {
            $zipFile = ($count == 1) ? _t('zip_file') : _t('zip_files');
            $this->message = _t('msg_found_files', '<strong>' . $count . ' ' . $zipFile . '</strong>');
            $this->status = 'info';
        } else {
            $this->message = _t('msg_files_not_found');
            $this->status = 'warning';
        }
    }

    private function checkExtention($file)
    {
        if (! file_exists($file)) {
            return false;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension != 'zip') {
            return false;
        }

        return true;
    }

    private function unZip($zip, $method = null)
    {
        if (! $this->checkExtention($zip) || ! in_array($zip, scandir($this->dir))) {
            $this->message = _t('msg_not_zip_file', '<strong>' . $zip . '</strong>');
            $this->status = 'danger';
            return false;
        }        

        switch ($method) {
            case 'execUnzip':
                $unzipResult = $this->execUnzip($zip);
                break;
            case 'systemUnzip':
                $unzipResult = $this->systemUnzip($zip);
                break;
            default:
                $unzipResult = $this->unZipArchive($zip);
        }

        if (! $unzipResult) {
            $this->message = _t('msg_error_while_unzip', '<strong>' . $zip . '</strong>');
            $this->status = 'danger';
            return false;
        }

        $this->message = _t('msg_unzip_success', '<strong>' . $zip . '</strong>');
        $this->status = 'success';

        return true;
    }

    private function unZipArchive($file)
    {
        $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
        $zip = new ZipArchive;
        if ($zip->open($file) !== true) {
            return false;
        }
        $zip->extractTo($path);
        $zip->close();

        return true;
    }

    private function execUnzip($file)
    {
        if (! exec('unzip -o ' . $file, $output)) {
            return false;
        }

        $this->output = implode('<br>', $output);

        return true;
    }

    private function systemUnzip($file)
    {
        ob_start();
            if (! system("unzip -o {$file}")) {
                return false;
            }
            $this->output = nl2br(ob_get_contents());
        ob_end_clean();

        return true;
    }

    private function delete($file)
    {
        if (! $this->checkExtention($file) && $file != basename(__FILE__)) {
            $this->message = _t('msg_cannot_delete', '<strong>' . $file . '</strong>');
            $this->status = 'danger';
            return false;
        }

        if (! unlink($file)) {
            $this->message = _t('msg_error_while_delete', '<strong>' . $file . '</strong>');
            $this->status = 'danger';
            return false;
        }

        $this->message = _t('msg_delete_success', '<strong>' . $file . '</strong>');
        $this->status = 'success';

        return true;
    }

    private function verifyToken()
    {
        if (empty($_POST['token'])) {
            $this->message = _t('msg_missing_token');
            $this->status = 'danger';
            return false;
        }

        if (! hash_equals($_SESSION['token'], $_POST['token'])) {
            $this->message = _t('msg_invalid_token');
            $this->status = 'danger';
            return false;
        }

        return true;
    }

    private function setToken()
    {
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $this->token = $_SESSION['token'];
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getZips()
    {
        return $this->zips;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getScriptPath()
    {
        return $_SERVER['REQUEST_URI'] ;
    }

    public function getTitle()
    {
        return $this->title;
    }
}

$unZipper = new UnZipper();

// === Helpers === //

/**
 * HTML special chars alias helper
 */
function _h($text)
{
    return htmlspecialchars($text, ENT_COMPAT);
}

/**
 * Translate helper
 */
function _t($key)
{
    $availableLanguages = ['en', 'pl', 'de', 'es', 'ru'];

    $translations = [
        'en' => [
          'method' => 'The method',
          'download' => 'Download',
          'unzip_it' => 'Unzip it',
          'delete_it' => 'Delete it',
          'zip_file' => 'zip file',
          'zip_files' => 'zip files',
          'msg_found_files' => 'Found %s in this directory.',
          'msg_files_not_found' => 'There is no zip file in this directory.',
          'msg_not_zip_file' => 'This %s is not a zip file.',
          'msg_error_while_unzip' => 'Error while unzipping file %s.',
          'msg_unzip_success' => 'File %s has been unziped.',
          'msg_cannot_delete' => 'This file %s cannot be deleted.',
          'msg_error_while_delete' => 'Error while deleting file %s.',
          'msg_delete_success' => 'File %s has been deleted.',
          'msg_missing_token' => 'Missing token.',
          'msg_invalid_token' => 'Invalid token.',
          'msg_warning_files_overwrite' => 'All unzipped files will be overwritten if they already exist.',
          'msg_warning_file_delete' => 'File will be deleted permanently.',
          'msg_warning_script_delete' => 'This script file will be deleted permanently.',
          'msg_remind_to_delete' => 'Remember to delete this script file when you are done.',
          'msg_are_you_sure' => 'Are you sure?',
          'msg_confirm_your_action' => 'Confirm your action.',
          'msg_action_proceed' => 'Yes, proceed it',
          'msg_action_close' => 'No, close it',
        ],
        'pl' => [
          'method' => 'Metoda',
          'download' => 'Pobieranie',
          'unzip_it' => 'Rozpakuj to',
          'delete_it' => 'Usuń to',
          'zip_file' => 'plik zip',
          'zip_files' => 'pliki zip',
          'msg_found_files' => 'Znaleziono %s w tym katalogu.',
          'msg_files_not_found' => 'W tym katalogu nie ma pliku zip.',
          'msg_not_zip_file' => 'To %s nie jest plikiem zip.',
          'msg_error_while_unzip' => 'Błąd podczas rozpakowywania pliku %s.',
          'msg_unzip_success' => 'Plik %s został rozpakowany.',
          'msg_cannot_delete' => 'Tego pliku %s nie można usunąć.',
          'msg_error_while_delete' => 'Błąd podczas usuwania pliku %s.',
          'msg_delete_success' => 'Plik %s został usunięty.',
          'msg_missing_token' => 'Brakujący token.',
          'msg_invalid_token' => 'Nieprawidłowy Token.',
          'msg_warning_files_overwrite' => 'Wszystkie rozpakowane pliki zostaną nadpisane, jeśli już istnieją.',
          'msg_warning_file_delete' => 'Plik zostanie trwale usunięty.',
          'msg_warning_script_delete' => 'Ten plik skryptu zostanie trwale usunięty.',
          'msg_remind_to_delete' => 'Pamiętaj, aby usunąć ten skrypt po zakończeniu.',
          'msg_are_you_sure' => 'Jesteś pewny?',
          'msg_confirm_your_action' => 'Potwierdź swoje działanie.',
          'msg_action_proceed' => 'Tak, kontynuuj',
          'msg_action_close' => 'Nie, zamknij to',
        ],
        'de' => [
          'method' => 'Die Methode',
          'download' => 'Herunterladen',
          'unzip_it' => 'Entpacken Sie es',
          'delete_it' => 'Lösche es',
          'zip_file' => 'zip-Datei',
          'zip_files' => 'Zip-Dateien',
          'msg_found_files' => 'Gefunden %s in diesem Verzeichnis.',
          'msg_files_not_found' => 'In diesem Verzeichnis befindet sich keine Zip-Datei.',
          'msg_not_zip_file' => 'Dies %s ist keine Zip-Datei.',
          'msg_error_while_unzip' => 'Fehler beim Entpacken der Datei %s.',
          'msg_unzip_success' => 'Datei %s wurde entpackt.',
          'msg_cannot_delete' => 'Diese Datei %s kann nicht gelöscht werden.',
          'msg_error_while_delete' => 'Fehler beim Löschen der Datei %s.',
          'msg_delete_success' => 'Datei %s wurde gelöscht.',
          'msg_missing_token' => 'Fehlendes Token',
          'msg_invalid_token' => 'Ungültiges Token',
          'msg_warning_files_overwrite' => 'Alle entpackten Dateien werden überschrieben, wenn sie bereits existieren.',
          'msg_warning_file_delete' => 'Die Datei wird dauerhaft gelöscht.',
          'msg_warning_script_delete' => 'Diese Skriptdatei wird dauerhaft gelöscht.',
          'msg_remind_to_delete' => 'Denken Sie daran, diese Skriptdatei zu löschen, wenn Sie fertig sind.',
          'msg_are_you_sure' => 'Bist du sicher?',
          'msg_confirm_your_action' => 'Bestätigen Sie Ihre Aktion.',
          'msg_action_proceed' => 'Ja, mach weiter',
          'msg_action_close' => 'Nein, schließe es',
        ],
        'es' => [
          'method' => 'El método',
          'download' => 'Descargar',
          'unzip_it' => 'Descomprimirlo',
          'delete_it' => 'Bórralo',
          'zip_file' => 'archivo zip',
          'zip_files' => 'archivos zip',
          'msg_found_files' => 'Encontrado %s en este directorio.',
          'msg_files_not_found' => 'No hay un archivo zip en este directorio.',
          'msg_not_zip_file' => 'Este %s no es un archivo zip.',
          'msg_error_while_unzip' => 'Error al descomprimir archivo %s.',
          'msg_unzip_success' => 'El archivo %s ha sido desconectado.',
          'msg_cannot_delete' => 'Este archivo %s no puede ser eliminado.',
          'msg_error_while_delete' => 'Error al eliminar el archivo %s.',
          'msg_delete_success' => 'Archivo %s ha sido eliminado.',
          'msg_missing_token' => 'Falta token.',
          'msg_invalid_token' => 'Simbolo no valido.',
          'msg_warning_files_overwrite' => 'Todos los archivos descomprimidos se sobrescribirán si ya existen.',
          'msg_warning_file_delete' => 'El archivo se eliminará permanentemente.',
          'msg_warning_script_delete' => 'Este archivo de script se eliminará permanentemente.',
          'msg_remind_to_delete' => 'Recuerde eliminar este archivo de script cuando haya terminado.',
          'msg_are_you_sure' => '¿Estás seguro?',
          'msg_confirm_your_action' => 'Confirma tu acción',
          'msg_action_proceed' => 'Sí, proceda',
          'msg_action_close' => 'No, ciérralo',
        ],
        'ru' => [
          'method' => 'Метод',
          'download' => 'Скачать',
          'unzip_it' => 'Распаковать',
          'delete_it' => 'Удали это',
          'zip_file' => 'zip-файл',
          'zip_files' => 'zip-файлы',
          'msg_found_files' => 'Найдено %s в этом каталоге.',
          'msg_files_not_found' => 'В этом каталоге нет zip-файла.',
          'msg_not_zip_file' => 'Это %s не является zip-файлом.',
          'msg_error_while_unzip' => 'Ошибка при распаковке файла %s.',
          'msg_unzip_success' => 'Файл %s был распакован.',
          'msg_cannot_delete' => 'Этот файл %s не может быть удален.',
          'msg_error_while_delete' => 'Ошибка при удалении файла %s.',
          'msg_delete_success' => 'Файл %s удален.',
          'msg_missing_token' => 'Отсутствует токен.',
          'msg_invalid_token' => 'Недопустимый токен.',
          'msg_warning_files_overwrite' => 'Все распакованные файлы будут перезаписаны, если они уже существуют.',
          'msg_warning_file_delete' => 'Файл будет удален постоянно.',
          'msg_warning_script_delete' => 'Этот файл сценария будет удален постоянно.',
          'msg_remind_to_delete' => 'Не забудьте удалить этот файл сценария, когда закончите.',
          'msg_are_you_sure' => 'Ты уверен?',
          'msg_confirm_your_action' => 'Подтвердите свое действие.',
          'msg_action_proceed' => 'Да, продолжайте',
          'msg_action_close' => 'Нет, закройте его',
        ],
    ];

    if (! empty($_GET['lang'])) {
        $language = (in_array($_GET['lang'], $availableLanguages)) ? $_GET['lang'] : 'en';
    }
    if (empty($language)) {
        $args = explode('/', $_SERVER['REQUEST_URI']);
        $cleanArgs = array_filter($args, function ($value) { return $value !== ''; });
        $lastArg = array_pop($cleanArgs);
        if (in_array($lastArg, $availableLanguages)) {
            $language = $lastArg;
        }
    }
    if (empty($language)) {
        $availableLanguages = array_flip($availableLanguages);
        $languagesWeight = [];
        preg_match_all('~([\w-]+)(?:[^,\d]+([\d.]+))?~', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            list($a, $b) = explode('-', $match[1]) + ['', ''];
            $value = isset($match[2]) ? (float) $match[2] : 1.0;
            if (isset($availableLanguages[$match[1]])) {
                $languagesWeight[$match[1]] = $value;
                continue;
            }
            if (isset($availableLanguages[$a])) {
                $languagesWeight[$a] = $value - 0.1;
            }
        }
        if ($languagesWeight) {
            arsort($languagesWeight);
            $language = key($languagesWeight);
        }
    }
    if (empty($language)) {
        $language = 'en';
    }

    $result = (! empty($translations[$language][$key])) ? $translations[$language][$key] : $key;

    if (func_num_args() > 1) {
        $args = func_get_args();
        $key = array_shift($args);
        $result = vsprintf($result, $args);
    }

    return $result;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$unZipper->getTitle()?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="https://bootswatch.com/4/lumen/bootstrap.min.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAATbUlEQVR4Xu1dB3QV1RY9L4VQQk1Cb1GpAtIRASmhiYIICiJ2BSugFLvfWFgqBizfiqjfCkhTsEDohCYltFBCCS0gkhCkhJ7MP3s08Mq0N28meeUeFytr+W49Z88tp10H6VDs28ktpXDp1jCiLhI5qjmIaunVEb8XHQckov0OiTLzHdJiR37ErOxnElK1RsPyVKDExREx0RcHsdDH8K+Ni246omffOSBtYlC8k13zxBQaMCDPvT0PAJR9M6V8ZMTZZIeDWvreuWjBbzggSavyovJ65gzvddJ5TC4AqDB+Xo3wfFpMDsfVfjNwMRDLOCBJUroUHpFwbGTCoYJGrwAgcXHx2OiL6/l/NLSsR9GQ/3FAorSs3MhWlNj5HAZ3GQCxSfNmOcjR1/9GLEZkAwemZY3uPuAyAGLHJ/fhk+PPNnQkmvRbDki9skb3+N1BiVJYXHRyOi8G1/jtWMXALOcA3wx2Zo/qVt9RIWl+j3CS5lreg2jQ7zmQL0kJjpik5Pf4vj/C70crBmg5B/hWkOSIS0pO4ZbbW966aNDvOSARawvj3knO4LtAvN+PVgzQcg7gHIAVgP8KCkkOSJQrABCSkr8yaQEA5kXPq+NoeJt4KlMsglIO5tCcnX/RyoPHQwIaIQ+A/g0q0ye9PA2ec/dk0UOzN9HF/ODeIUMaAKUiwyl1aAcqXzxS8Wt/a8UemrA6I6hXgpAGwNDmNemNzvVUBXz41HlqOnGZAECwcmDdkPZUs0wJzem1mrSc9p84G6wsoJBdAVpWKUu/3dVaV7CDZm6ghXuzdcsFaoGQBcDLN9ahYa1q68ptxNytNHnrYd1ygVogZAGw6J7rqVHF0rpyGzV/G327+bIDjW75QCsQkgCAF0zm010pMkzZJ9ZZiE/8nkbTtv0ZaHI1PN6QBACuf3uHdzHEpDtnpNKifccMlQ3EQiEJAAhq5xOdqVzxCF2Z4RqI62CwUsgCYHy3hnRPk2qacs0+e4Eafrw0WGUvzytkAVCtdHFa/sANhO1AjT5PPUAvLmZvuSCmkAUAZNqDjUBf9L6OioV7HgYvsQ2gyzeracex00Es/hBeAQqk2rpqOZp4SxOqWjrqsqCPnrlAryxJpxnbjwS18EN6C3CWLLaB3nUrUYUSkZR+LJeW7j9GWAFCgUJ6CwgFAevNUQBAj0NB/rsAQJALWG96AgB6HAry3wUAglzAetMTANDjUJD/LgAQ5ALWm54AgB6Hgvx3AYAgF7De9AQA9DgU5L8LAFgo4OGta8tuZvAjPHsp38KW7WtKAMAi3ravUYFmDGghJ12ax1FFYxZspyOn/d+RJGQBcEON8rIVsGLJYnQ+L5+y2QK46a+T9P4fe2nDEZdUeoYg8kO/ZtQ1PvZyWYSU9Z26ltYePmGoflEVClkA/DKoFcEU7E5zdh6lh+Zs8koeUeFhtHtYZ8JfZwoEhxLbAVCxVDG6u3F1whd3VbmSVKFkJJWICL+Sn84rVhsvnHnyHD3Igtyo8DWXiAijDHYKDed0qO6Er7/H938Y74hLtq1enn4e6JlYddKGg/TCoh1etVXYhW0FQJXoKFp2/w1UNkrf+dKOiR8/d5Fac2jXifOXXJq/rlIZmn93G8Uu9xw/Q22/XOHVcBBggkATd3pzxW56d/Ver9oq7MK2AQBf15L72lK9mFKFPSeX/vAF4kt0ptvqV6bPblbOgX3o1DlqNhFpk4zT53yWuLVeJY8KSn0bb7VwStoGALhcw/W6qGnY3DSautU1sOMpTgbxQnvltIh/5Z6nxp96FxG88oF2dE2Fkh5TRVDJrB1HqGFcNLXgWMSmlctSfNmSVLVMFJWLiqTi2Ap5FzrFKxRWqYzjubJHUuqfJ+R4xNyLHsm9LWenbQDASCf1bkJ92NWqqOijtfvp1WU7Pbqf0L0hn0uUXcJxdWvymXEAILrowFMJiucJRBVXKhXFgnY9HBrhx4U8SXZN+2DNXvrj0N9GqpgqYysAutSOoSn9m5samC+VzrESZsS8rfLXp0RTeUydeWxKhMNj88+NbwHY4lL4nGMnLWEgPLtgB+39+4zl3dgKADhbft23Kd1Ys4LlA1dr8NjZi3T3rA20npdRNVp2f1uqHxOt+PM+/mpxcDRCOOc8dX08PXuD/dn1T124RAOnp9I6jXkZGbN7GVsBgM6uLl+SxnapL+9zOSyck+cv0mne2y7l55PkheNtGDM7gpdbgKpPvcoUX84zscOBk2dpADMpg0/yWqQVFrYrJ5fafbVSsz7iCHC1fYJP/zXKFDfDd1N1EKmEWAUrNYy2A8DUTDUq3X9ddXq7awMPPQIOT3dMX6/LHOzZhzgyWI22HD1FCd+uVvwZWoM7G1Wl59pdQ7jiFgXN5gxmD8/ZbFnXAQWAR1rUpNc7eeb0ST1yghDF+/c51/u+EpcguE2P3KjKQBy4ek9Z6/E7Usl8fHMjRe2hWWmcuZQnr4xY3cpxoioj4eroq/M3q2hrljURSwEDgBGt4+nFDp5XtxWczw97vtErE6x1SA6hRkv358griTNBc7jm4fbyid4MIcYkjVeWlZk5tI5tA7t5m8ngAx0Oq85UmvMUNq1chtpUK0f96ldRvFqiPA63j/y6xcxQPOoEBABGXn8VL7ueBy3clR/gXH7ujNTiDA6k0+9ooVrk991ZdN/PG11+jy4WTmmPdaSSfG83Snl8wFl+4DjN3nmEft11VD7/eEu4Rb3Rub4HENB2E9ZVZLEBy1fyewCM4RP2mLZXecwTTB3yy2avQ7h6160oB4Sq0fTtf9Ljv6W5/Ix7PKx9MPkapdWZf1Mftgb6SjAwvd21Pt3VyFVv8cKidNZwHvC1ef8OD8dXj6/fnbAEQkj4ErylAQ2r0Ic3NVKt9r9NmfQM2/KdCYkkcdeHYcsoLdx7jAbN1Hyz0WhTcrn3e15Lg66terkOroO9fljjVRtKhf12BXieT9pP8x3bnX7kfD3DWb1rNnbz3ibVKalbA1XGfbxuPyUu9dQe4kv8fXBrahT3T2IpQC8r94IqKMyYlbWkiXMIDGu1yv5z/cX8G36yxNTW4tyPXwJATfhTOF0b3K28/+6vTHkIZwcdq5EdNGlVBo1buUdRFk0qlZaXYhzisAX1b1CF/qNgBURlAPVJtgVYSej7vR5XXvXDdRDXQl/I7wAAIw2MNe40Oe0wPcXqXV+Ejzbht/dSB0/TbUF/ry/bRf9du88QT1/rVJcebaH8lLLSVmKoUY1COIzuGdblsg7kq42Z9OxC1+3K2z78CgAvsvBH2Ch8MGc0Hyif0VDdPr9wB32x0dV8rMbUT9mk3I9Ny0qktpV4KyD38tsf70QxnMcAZMZ5xb09vwGAmvCxlA7jpdTXL79g4mq3ioLfvckMOoOvkx1U7BzjeSt5W2Ur8QUEe9j1DPoCEHQf8R8s8qU5/7gFQMEDRY87zZRP+1tMH/iUOKMHAG/2VdwM1BxevNlKjEoQnlW7nnT1sQAAjCrBlPop8hVAbc/H4WYo3/PNnvbVmKoHAG+SQzsvx+792eENdH31cjR7YCuXruC8AicWs1SkAIAZdZSKkgdfopl7vh4j9ADQf9p6SjmQo9eM/PufI7sqOoLgN/gj4OBqJY1jIxiMYc6EPIawEpqlIgOA2mEMQRVQ79qVpEnvEAhDkBEPHKXl2FkI0FL+nO7bFc25PbXXTaq9u8CnZ22KBABQ8OCu707Qnt370wafJqT3JWDF0XLguHnyGkPBHLVZIQMDkRrdPWsjJWdk6Q3H8O+v3FiX/Q9cr5zeei/5xRlA7R6+MvO4bNL1xrBjmHtOBdUMSwVFoF414nXTjK128wYru5ajrX4/rqfl/AKZFQQLJvpyNxcDYACaL1SoKwAQDCS7E+z5/ZlhvpxmjTIBegZcOdXIKAAS4mNocj91f8ebGEhabmlGxwv7A4SP1Lbu5I3OQq2/QgOAmgp2e/ZpupWtZkacOYwyTaucGggL6hgVnFZsAdrq+PUqwtx8odgSxWjq7c2pscLDFtCLNLMgk3mhAEDNAAO36Vsmr/XpGuMtg/VeCjMKAD2jkq+PTcVzGB2Ej7OGEsEWgcOyr2Q7AAZeW4U+6NnIw4fvKFvSbuEDF7xwC5MebFqD3kqo7/MW8HjLWpTY0XM7K2i40SdLCTmHzRCMPnjODrp/NerO8YtKcY/e9mcrAOB8gRBs9yDM0xfyZGcJuEkVNiEgBIEhamT0FgB7Aq6UamRGQ1eXYwxe7ViPcL7QIlhFh7NV1AqyDQCYxDd9m3mcXBE3P5ifYkOwQ1GQnkOIUT2AliUQ86o8Yb5hLSaWebxdPIg9jpUilp35hFWl/VcrLDsz2QIAxN1PY0MJnBjcyRtjix0AQagaQtbUqO+P6ww9HP0uryKDVcLLkHCixnsLNYeP96o61oqR4wturlORPYP1Zwvl2EC+KhvVVOq3aMN7AYi4mX1nK8X3eD5hb5tX/vW2AThq80EH15tqHFxRnf9W5X9VOG8/rF0lOQCkVGQEleJ9EFowBIW4E+wEF5jZuD5mnTnPjhpn5Je/1ULCUB8vhX/D0UpqdDurgpcZUAUjuhg3ASVCWHq9j5Z4/IS5tuKPAxZEPFbhjYsZGnuOTdVfGjRVGxE+yli6AuDRhbl3taHKCkETuOZNTjtE9WKjqW6FUrLQDYDe6DxcyjkDzb0BxAQiNlCNjL4S9lWf6+QvV4kATFwBc1hHHxkWRmU4UhpxBVqHOr2Jas1Jr67W75YBAF/pL4Na07UcCu0PBNduuHi7k5JFzbnMYI4xmJ+h/1Sse04gO+f86fr99J8lnn6KVvRpCQDwJX/vliTJisH50gaECGG6E5QqCzUCQ+75aaOc5UuPEFtgd9ArVpLXU3YSwtztIksAoKdetWvwWu0i41e37zxz/WCb2jhUPTTMqBEHdnmsJnYRtkw4wyyw+eFqSwCgliHDLuYotYuECs6vf33N/v3I1adEmzk2UOmcgrI4ZS828FLoXHYRb84ZP+wgGKPgDANrn91kCQD0tGJ2TwJh4W0mrZAffarDypTzHHOHNCtqfoRayiCjGjatx6fdwWh0/tCRvLs6gybwP6s9odTG4DMAqvNpHpq1TnynLSo6yRG2COmGbcEoKfkh4hqJyFvc4/VoKSfAasA3GiWCmbZyqeKEOAKjBMvhaH6p3KqoX6P9+gwARLLO4Xu/HuGrwH++kIMvjpGcnEHp+ojsXsjsga/IKD3crCa9yr79sLMjkuex3zazXsFY/eXsEArVrRLBE2hsyi5awIfNMjop8rB6jVuxRw4kKQryGQAYdIFvH5i3aF+2rKnalnWKDvEeBodFqxMn4z5dg+/VzTnzFnQKiK9fwc4Xcw2c3t2ZXIfr4zD3w5bDXvkgrnqwnZz9RIkKooIQy4eYPiWCD8Rn6w/QbAaLHb6PRsFkCQDQGWLWsBRDCxYK9MdD7ThNjTIAvt9yiJ5O3iazAWpnKIxO8Kl+W/YpOWQc2kqkovEHsgwA/jCZwhzDWvYHLAjUdO/XipCtwpqLAIBJTqcO6UA4ACvRRH51/KUAeXVcAMAkAJBnSC1R1IccXPoaB5kGAgkAmJRS2qMdVa15SBCNRNGBQAIAJqW07fGOBKdNJUJQKIJDA4EEAExKKf2JToTUMUo0NmU3vc85fgOBBABMSglRumrvINgRGWxymLrVBAB0WaRcIIMzdag5eCDHEBJEBAIJAJiU0r4RXVTzBr68JF3W8gUCCQCYlNJBfiPA/ZGogqZeZB0AHowKBBIAMCmlw5xwWslRFc1ZlcTR5NC8qiYA4BW7rhT+a1Q3VadWO7KDmBymbjUBAF0WKRc4ygBQIyuidk0Oy+tqAgBes+yfCgIAJhkXLNW0ACC2gGCRssY8tM8A1mTyLgw2ii3AJJePjOymGs+HIA4EcwQCCQCYlBLeHVJ74gWmYJiEA4EEAExKSUsT+BY7ecK1OxBIAMCklLSenhP+ACaZGkjVtDyCYAeAPSAQSKwAJqWk9frot5sP0SgO8ggEEgAwKSWtVPFWPutmcniGqwkAGGaVa0GtFDEIL0eYeSCQAIBJKSFrN7J3KxFuALgJBAIJAJiUEsLLkWegIHURwuJGJm+Vo36sDoUzOURD1QQADLFJudAHHPd3579v+Y3kULDvOCQs0EgAwAeJIS/Ss/y4JbKRzNh+xIeWiq6qAEDR8d4venbEJiVncry968O0fjE0MQi7OcCZEPYDAKkMgGZ2dyba90sOrHbEvjNvosPhGOKXwxODspcDEn3IZ4B5N3HC0N/s7Um07o8cyHdQVwclSmFx0fMRy6ye+9wfRy/G5BMHJElKzx7dvYGcbylu/LyBJDmm+NSiqBxQHMgnR/9jo7vNvJxwKy4peQbPoF9AzUIM1hQHOFvbT9mje9yGylcyrk1YWSIu/zRyqzY21aqoFBgckCgtKzeyFSV2ltOQuqTcK//W/Jrh4fnJfCuoFxizEaP0hgP85e/gg1/3nFE9DhbU88i5WPbNlPKREWcZBNTSm8ZFWT/ngCStyovK65kzvNdJ55Eqv9mQuDgiJvriIH7wZYzYEvxcsLrDkzZJUti47FrHp9KAAXnuxXUf7Ygdt7C5FJbXP0yijpKDqnMF1wdsdQcgChQmB2T1rkSZ+SQtcUgRM7OfSUjV6v//TSTVGOvULoMAAAAASUVORK5CYII=" rel="icon" type="image/x-icon">
    <style>
        a:hover {
            text-decoration: none;
        }
        label {
            margin: 0;
        }
        .notification {
            position: absolute;
            top: 1rem;
            right: 1rem;
        }
        .output {
            max-height: 10rem;
            overflow: auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1 class="page-title text-center my-3">
                <a href="<?=$unZipper->getScriptPath()?>" title="<?=_h($unZipper->getTitle())?>">
                    <i class="fas fa-cube mr-1"></i>
                    <?=$unZipper->getTitle()?>
                </a>
            </h1>
        </div>
    </header>

    <section id="body">
        <div class="container">

            <div class="notification-box">
                <?php if ($unZipper->getMessage()): ?>
                    <div class="notification alert alert-dismissible fade show alert-<?=_h($unZipper->getStatus())?>">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?=$unZipper->getMessage()?>
                    </div>
                <?php endif ?>
            </div>

            <form class="form-unzip" method="POST" action="">
                <input type="hidden" name="token" value="<?=$unZipper->getToken()?>" />
                <input type="hidden" name="zipfile" value="" />
                <input type="hidden" name="delfile" value="" />

                <?php if ($unZipper->getZips()): ?>
                    <div class="form-control mb-3 d-flex justify-content-around align-items-center">

                        <strong><?=_t('method')?>:</strong>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="method" value="zipArchive" <?=(empty($_POST['method']) || $_POST['method'] == 'zipArchive') ? 'checked' : ''?> />
                                ZipArchive
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="method" value="execUnzip" <?=(! empty($_POST['method']) && $_POST['method'] == 'execUnzip') ? 'checked' : ''?> />
                                exec unzip
                            </label>
                        </div>

                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="method" value="systemUnzip" <?=(! empty($_POST['method']) && $_POST['method'] == 'systemUnzip') ? 'checked' : ''?> />
                                system unzip
                            </label>
                        </div>

                    </div>

                    <ul class="list-group">
                        <?php foreach ($unZipper->getZips() as $key => $zip): ?>
                            <li class="list-group-item clearfix">
                                <h2 class="text-nowrap float-left mb-0">
                                    <a href="<?=$zip?>" title="<?=_t('download')?> <?=_h($zip)?>">
                                        <i class="fas fa-file-archive mr-1"></i> <?=$zip?>
                                    </a>
                                </h2>
                                <input type="hidden" name="zipfiles[<?=$key?>]" value="<?=_h($zip)?>" />
                                <button type="submit" class="btn-unzip btn-modal btn btn-warning float-right mb-0" title="<?=_t('unzip_it')?>" data-modal-body="<?=_t('msg_warning_files_overwrite')?>">
                                    <i class="fas fa-cubes mr-1"></i>
                                    <?=_t('unzip_it')?>
                                </button>
                                <input type="hidden" name="delfiles[<?=$key?>]" value="<?=_h($zip)?>" />
                                <button type="submit" class="btn-delete btn-modal btn btn-outline-danger float-right mb-0 mr-3" title="<?=_t('delete_it')?>" data-modal-body="<?=_t('msg_warning_file_delete')?>">
                                    <i class="fas fa-trash-alt mr-1"></i>
                                    <?=_t('delete_it')?>
                                </button>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <div class="output-box">
                    <?php if ($unZipper->getOutput()): ?>
                        <div class="output alert alert-dismissible fade show alert-warning mt-3">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?=$unZipper->getOutput()?>
                        </div>
                    <?php endif ?>
                </div>

                <div class="reminder-box mt-3 text-center">
                    <input type="hidden" name="delfiles[]" value="unzipper.php" />
                    <button type="button" class="btn-delete btn-modal btn btn-outline-warning" title="<?=_t('delete_it')?>" data-modal-body="<?=_t('msg_warning_script_delete')?>">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        <?=_t('msg_remind_to_delete')?>
                    </button>
                </div>

            </form>

        </div>
    </div>

    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?=_t('msg_are_you_sure')?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?=_t('msg_confirm_your_action')?>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary form-confirm"><?=_t('msg_action_proceed')?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=_t('msg_action_close')?></button>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center my-3">
        <div class="container">
            Made by
            <a href="http://revert.pl" title="Full-Stack Developer" target="_blank">
                Revert
            </a>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        // Set zipfile name
        $('.btn-unzip').on('click', function (e) {
            e.preventDefault();
            let zipfile = $(this).prev().val();
            $('input[name=zipfile]').val(zipfile);
            $('.form-unzip').submit();
        });

        // Set delfile name
        $('.btn-delete').on('click', function (e) {
            e.preventDefault();
            let delfile = $(this).prev().val();
            $('input[name=delfile]').val(delfile);
            $('.form-unzip').submit();
        });

        // Set modal data
        $('.btn-modal').on('click', function (e) {
            let title = $(this).data('modal-title');
            if (title) {
                $('.modal-title').html(title);
            }
            let body = $(this).data('modal-body');
            if (body) {
                $('.modal-body').html(body);
            }
        });

        // Form submit confirm with modal dialog
        $('.form-unzip').submit(function(e){
            if ($(this).hasClass('confirmed')) {
                return true;
            } else {
                e.preventDefault();
                $('.modal').modal('show');
            }
        });
        $('.form-confirm').on('click', function (e) {
            $('.form-unzip').addClass('confirmed');
            $('.form-unzip').submit();
        });
        $('.modal').on('hidden.bs.modal', function (e) {
            $('input[name=zipfile]').val('');
            $('input[name=delfile]').val('');
        });

        // Notification auto close
        let delay = 5000; // 5 s
        setTimeout(function(){
            $('.notification').alert('close');
        }, delay);
    </script>
</body>
</html>
