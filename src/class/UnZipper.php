<?php
/**
 * UnZipper
 *
 * Unzip zip files. One file server side simple unzipper with UI.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.2.1
 */
class UnZipper
{
    private $title = 'UnZipper';
    private $dir = './';
    private $zips = [];
    private $alertMessage;
    private $alertStatus;
    private $token;
    private $output;
    private $methods = [
        'zipArchive' => 'ZipArchive',
        'execUnzip' => 'exec unzip',
        'systemUnzip' => 'system unzip',
    ];

    public function __construct()
    {
        $this->checkIfUnZip();
        $this->checkIfDeleteFile();
        $this->setToken();
        $this->findZips();
        $this->countZips();
    }

    private function checkIfUnZip()
    {
        if (! empty($_POST['zipfile']) && $this->verifyToken($_POST['token'])) {
            $this->unZip($_POST['zipfile'], $_POST['method']);
        }
    }

    private function checkIfDeleteFile()
    {
        if (! empty($_POST['delfile']) && $this->verifyToken($_POST['token'])) {
            $this->deleteFile($_POST['delfile']);
        }
    }

    private function findZips()
    {
        $fileNames = scandir($this->dir);

        foreach ($fileNames as $fileName) {
            if ($this->checkZipFile($fileName)) {
                $this->zips[] = $fileName;
            }
        }
    }

    private function countZips()
    {
        if ($this->alertMessage) {
            return false;
        }

        $count = count($this->zips);
        if ($count) {
            $zipFile = ($count == 1) ? _t('zip_file') : _t('zip_files');
            $this->alertMessage = _t('msg_found_files', '<strong>' . $count . ' ' . $zipFile . '</strong>');
            $this->alertStatus = 'info';
        } else {
            $this->alertMessage = _t('msg_files_not_found');
            $this->alertStatus = 'warning';
        }
    }

    private function checkZipFile($fileName)
    {
        if (! file_exists($fileName)) {
            return false;
        }

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($extension != 'zip') {
            return false;
        }

        return true;
    }

    private function unZip($zip, $method = null)
    {
        if (! $this->checkZipFile($zip) || ! in_array($zip, scandir($this->dir))) {
            $this->alertMessage = _t('msg_not_zip_file', '<strong>' . $zip . '</strong>');
            $this->alertStatus = 'danger';
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
            $this->alertMessage = _t('msg_error_while_unzip', '<strong>' . $zip . '</strong>');
            $this->alertStatus = 'danger';
            return false;
        }

        $this->alertMessage = _t('msg_unzip_success', '<strong>' . $zip . '</strong>');
        $this->alertStatus = 'success';

        return true;
    }

    private function unZipArchive($fileName)
    {
        $dirPath = pathinfo(realpath($fileName), PATHINFO_DIRNAME);
        $zip = new ZipArchive;
        if ($zip->open($fileName) !== true) {
            return false;
        }
        $zip->extractTo($dirPath);
        $zip->close();

        return true;
    }

    private function execUnzip($fileName)
    {
        if (! exec('unzip -o ' . $fileName, $output)) {
            return false;
        }

        $this->output = implode('<br>', $output);

        return true;
    }

    private function systemUnzip($fileName)
    {
        ob_start();
            if (! system("unzip -o {$fileName}")) {
                return false;
            }
            $this->output = nl2br(ob_get_contents());
        ob_end_clean();

        return true;
    }

    private function deleteFile($fileName)
    {
        if (! $this->checkZipFile($fileName) && $fileName != basename(__FILE__)) {
            $this->alertMessage = _t('msg_cannot_delete', '<strong>' . $fileName . '</strong>');
            $this->alertStatus = 'danger';
            return false;
        }

        if (! unlink($fileName)) {
            $this->alertMessage = _t('msg_error_while_delete', '<strong>' . $fileName . '</strong>');
            $this->alertStatus = 'danger';
            return false;
        }

        $this->alertMessage = _t('msg_delete_success', '<strong>' . $fileName . '</strong>');
        $this->alertStatus = 'success';

        return true;
    }

    private function verifyToken($inputToken)
    {
        if (empty($inputToken)) {
            $this->alertMessage = _t('msg_missing_token');
            $this->alertStatus = 'danger';
            return false;
        }

        if (! hash_equals($_SESSION['token'], $inputToken)) {
            $this->alertMessage = _t('msg_invalid_token');
            $this->alertStatus = 'danger';
            return false;
        }

        return true;
    }

    private function setToken()
    {
        $_SESSION['token'] = md5(uniqid(rand(), true)); // PHP 7 only: bin2hex(random_bytes(32));
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
        return $this->alertMessage;
    }

    public function getStatus()
    {
        return $this->alertStatus;
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

    public function getMethods()
    {
        return $this->methods;
    }
}
