<?php
session_start();
/**
 * unZipper
 * 
 * Unzip zip files. One file server side simple unzipper with UI.
 * 
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.0.0
 */
class unZipper {

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
    }

    public function findZips()
    {
        $files = scandir($this->dir);

        foreach ($files as $file) {
            if ($this->checkExtention($file)) {
                $this->zips[] = $file;
            }
        }

        if (! $this->message) {
            if ($count = count($this->zips)) {
                $this->message = 'Found <strong>' . $count . ' zip</strong> ' . ($count == 1 ? 'file' : 'files') . ' in this directory.';
                $this->status = 'info';
            } else {
                $this->message = 'There is no zip files in this directory.';
                $this->status = 'warning';
            }
        }
    }

    public function checkExtention($file)
    {
        if (file_exists($file)) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension == 'zip') {
                return true;
            }
        }
        return false;
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

    public function unZip($zip)
    {
        if ($this->checkExtention($zip)) {

            $method = $_POST['method'];

            switch($method) {
                case 'execUnzip':
                    $unzipResult = $this->execUnzip($zip);
                    break;
                case 'systemUnzip':
                    $unzipResult = $this->systemUnzip($zip);
                    break;
                default:
                    $unzipResult = $this->unZipArchive($zip);
            }

            if ($unzipResult) {
                $this->message = 'File <strong>' . $zip . '</strong> has been unziped.';
                $this->status = 'success';
                return true;
            } else {
                $this->message = 'Error while unzipping file <strong>' . $zip . '</strong>.';
                $this->status = 'danger';
            }
        } else {
            $this->message = 'This <strong>' . $zip . '</strong> is not a zip file.';
            $this->status = 'danger';
        }
        return false;
    }

    public function execUnzip($file)
    {
        if (exec('unzip -o ' . $file, $output)) {
            $this->output = implode('<br>', $output);
            return true;
        }
        return false;
    }

    public function systemUnzip($file)
    {
        ob_start();
        system("unzip -o {$file}");
        $this->output = nl2br(ob_get_contents());
        ob_end_clean();

        if ($this->output) {
            return true;
        }
        return false;
    }

    public function unZipArchive($file)
    {
        $path = pathinfo(realpath($file), PATHINFO_DIRNAME);
        $zip = new ZipArchive;
        if ($zip->open($file) === TRUE) {
            $zip->extractTo($path);
            $zip->close();
            return true;
        }
        return false;
    }

    public function setToken()
    {
        $_SESSION['token'] = bin2hex(random_bytes(32));
        $this->token = $_SESSION['token'];
    }

    public function getToken()
    {
        return $this->token;
    }

    public function verfiyToken()
    {
        if (! empty($_POST['token'])) {
            if (hash_equals($_SESSION['token'], $_POST['token'])) {
                return true;
            }
        }
        $this->message = 'Invalid token.';
        $this->status = 'danger';
        return false;
    }

    public function delete($file)
    {
        if ($this->checkExtention($file) || $file == basename(__FILE__)) {
            if (unlink($file)) {
                $this->message = 'File <strong>' . $file . '</strong> has been deleted.';
                $this->status = 'success';
                return true;
            }
        }
        $this->message = 'Error while deleting file <strong>' . $file . '</strong>.';
        $this->status = 'danger';
        return false;
    }
}

$unZipper = new unZipper();

function safe($text) {
    return htmlspecialchars($text, ENT_COMPAT);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>unZipper</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="https://bootswatch.com/4/lumen/bootstrap.min.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
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
                    unZipper
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
