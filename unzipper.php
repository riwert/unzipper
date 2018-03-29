<?php
session_start();
/**
 * UnZipper
 *
 * Unzip zip files. One file server side simple unzipper with UI.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.0.1
 */
class UnZipper
{
    private $dir = './';
    private $zips = [];
    private $message;
    private $status;
    private $token;
    private $output;

    public function __construct()
    {
        if (! empty($_POST['zipfile']) && $this->verfiyToken()) {
            $this->unZip($_POST['zipfile']);
        }
        if (! empty($_POST['delfile']) && $this->verfiyToken()) {
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
            $file = ($count == 1) ? 'file' : 'files';
            $this->message = 'Found <strong>' . $count . ' zip ' . $file . '</strong> in this directory.';
            $this->status = 'info';
        } else {
            $this->message = 'There is no zip file in this directory.';
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

    private function unZip($zip)
    {
        if (! $this->checkExtention($zip)) {
            $this->message = 'This <strong>' . $zip . '</strong> is not a zip file.';
            $this->status = 'danger';
            return false;
        }

        $method = $_POST['method'];

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
            $this->message = 'Error while unzipping file <strong>' . $zip . '</strong>.';
            $this->status = 'danger';
            return false;
        }

        $this->message = 'File <strong>' . $zip . '</strong> has been unziped.';
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
            $this->message = 'This file <strong>' . $file . '</strong> cannot be deleted.';
            $this->status = 'danger';
            return false;
        }

        if (! unlink($file)) {
            $this->message = 'Error while deleting file <strong>' . $file . '</strong>.';
            $this->status = 'danger';
            return false;
        }

        $this->message = 'File <strong>' . $file . '</strong> has been deleted.';
        $this->status = 'success';

        return true;
    }

    private function verfiyToken()
    {
        if (empty($_POST['token'])) {
            $this->message = 'Missing token.';
            $this->status = 'danger';
            return false;
        }

        if (! hash_equals($_SESSION['token'], $_POST['token'])) {
            $this->message = 'Invalid token.';
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
}

$unZipper = new UnZipper();

function safe($text)
{
    return htmlspecialchars($text, ENT_COMPAT);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>UnZipper</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="https://bootswatch.com/4/lumen/bootstrap.min.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
    <link href="data:image/x-icon;base64,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" rel="icon" type="image/x-icon">
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
                <a href="unzipper.php" title="unZipper">
                    <i class="fas fa-cube mr-1"></i>
                    UnZipper
                </a>
            </h1>
        </div>
    </header>

    <section id="body">
        <div class="container">

            <div class="notification-box">
                <?php if ($unZipper->getMessage()): ?>
                    <div class="notification alert alert-dismissible fade show alert-<?=safe($unZipper->getStatus())?>">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?=$unZipper->getMessage()?>
                    </div>
                <?php endif ?>
            </div>

            <form class="form-unzip" method="POST" action="unzipper.php">
                <input type="hidden" name="token" value="<?=$unZipper->getToken()?>" />
                <input type="hidden" name="zipfile" value="" />
                <input type="hidden" name="delfile" value="" />

                <?php if ($unZipper->getZips()): ?>
                    <div class="form-control mb-3 d-flex justify-content-around align-items-center">

                        <strong>Method:</strong>

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
                                <h3 class="text-nowrap float-left">
                                    <a href="<?=$zip?>" title="Download <?=safe($zip)?>">
                                        <i class="fas fa-file-archive mr-1"></i> <?=$zip?>
                                    </a>
                                </h3>
                                <input type="hidden" name="zipfiles[<?=$key?>]" value="<?=safe($zip)?>" />
                                <button type="submit" class="btn-unzip btn-modal btn btn-warning float-right mb-0" title="Unzip It" data-modal-body="All unzipped files will be overwritten if already exists.">
                                    <i class="fas fa-cubes mr-1"></i>
                                    Unzip It
                                </button>
                                <input type="hidden" name="delfiles[<?=$key?>]" value="<?=safe($zip)?>" />
                                <button type="submit" class="btn-delete btn-modal btn btn-outline-danger float-right mb-0 mr-3" title="Delete It" data-modal-body="File will be deleted permanently.">
                                    <i class="fas fa-trash-alt mr-1"></i>
                                    Delete It
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
                    <button type="button" class="btn-delete btn-modal btn btn-outline-warning" title="Delete It" data-modal-body="This script file will be deleted permanently.">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Remember to delete this script file when you are done.
                    </button>
                </div>

            </form>

        </div>
    </div>

    <div class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Are you sure?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Confirm your action.
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary form-confirm">Yes, proceed</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, close</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center my-3">
        <div class="container">
            Made by
            <a href="http://revert.pl" title="Programista PHP i Web Developer" target="_blank">
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
        })

        // Notification auto close
        let delay = 5000; // 5 s
        setTimeout(function(){
            $('.notification').alert('close');
        }, delay);
    </script>
</body>
</html>
