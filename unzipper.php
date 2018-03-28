<?php
session_start();

class unZipper {

    private $dir = './';
    private $zips = [];
    private $message;
    private $status;
    private $token;

    public function __construct()
    {
        if (isset($_POST['zipfile']) && $this->verfiyToken()) {
            $this->unZip($_POST['zipfile']);
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
        if (exec('unzip -o ' . $file)) {
            return true;
        }
        return false;
    }

    public function systemUnzip($file)
    {
        if (system('unzip -o ' . $file)) {
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
}

$unzipper = new unZipper();

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
                <?php if ($unzipper->getMessage()): ?>
                    <div class="notification alert alert-dismissible fade show alert-<?=safe($unzipper->getStatus())?>">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?=$unzipper->getMessage()?>
                    </div>
                <?php endif ?>
            </div>

            <?php if ($unzipper->getZips()): ?>
                <form class="form-unzip" method="POST" action="unzipper.php">
                    <input type="hidden" name="token" value="<?=$unzipper->getToken()?>" />
                    <input type="hidden" name="zipfile" value="" />

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
                        <?php foreach ($unzipper->getZips() as $key => $zip): ?>
                            <li class="list-group-item">
                                <input type="hidden" name="zipfiles[<?=$key?>]" value="<?=safe($zip)?>" />
                                <button type="submit" class="btn-unzip btn btn-warning float-right mb-0">
                                    <i class="fas fa-cubes mr-1"></i>
                                    Unzip It
                                </button>
                                <h3>
                                    <a href="<?=$zip?>" title="Download <?=safe($zip)?>">
                                        <i class="fas fa-file-archive mr-1"></i> <?=$zip?>
                                    </a>
                                </h3>
                            </li>
                        <?php endforeach ?>
                    </ul>

                </form>
            <?php endif ?>

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
                    All files will be overwritten if already exists.
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary form-confirm">Yes, unzip it</button>
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

        // Confirm unzip
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
