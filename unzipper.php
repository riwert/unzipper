<?php
session_start();
/**
 * UnZipper
 *
 * Unzip zip files. One file server side simple unzipper with UI.
 *
 * @author Robert Wierzchowski <revert@revert.pl>
 * @version 1.0.0
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
        if ($zip->open($file) === true) {
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
    <link href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAYAAADDPmHLAAARO0lEQVR4Xu2dCdhVUxfHV8pQ5nlokqKn+AxJHmNUSoZCSDKTeSbzPFMImTI/pkilUDIVofAZIlSIV695DMkU3/6dnL77nrv3mc99771nr+fZD293j2v/z95rr73W2g0kmDqqLL1V6qJSU5VaBhexOeqRAzWq7VqVJqo0WqU3/PrSwPBjI/Xv/VQaqNJ/6nEwtunkHJimqhik0nCVFnir0wFgJZVpgkp8+ZaqhwNT1FB6qjS3cEheADT/d+loXT3jtiMp4MBM9f9dVfrM/bdCADRW//hfldpbllU1B6ar0XVSaT6jLAQAAsNuVT10OziXAyPU/+xdCIBe6o8xlj+54sBOarTjWQEaqjRDpTa5Gr4d7CzFgnYAoIdKT1p+5JIDXQHAEJVOyOXw7aAHA4DJKm1teZFLDkwEALNVapXL4dtBzwIA/1g+5JYD8ywAcjv3CwduAaCY0Lt3bznjjDNk+eWXl+eee04eeeQRmTRpUi6gkXsA9O/fX+67776iyR47dqzstdde8scff1Q1EHINgGWWWUZqampkpZW4AC2m8847Ty6++GILgGrlwIknnijXXnutcXi1tbXSokUL+eef6pWTc7sCNGjQQGbPni1rr722L75bt27t5KtWyi0AtthiC3n55ZcD53XnnXeWcePGBear1Ay5BcCVV14pp512WuC8HXLIIXLXXXcF5qvUDLkFwJtvvikbb7xx4LwdccQRMmzYsMB8lZohlwBYbLHF5LfffpPFF188cN4OOOAAuffeewPzVWqGXAJg2WWXlZ9++inUnPXs2VOefLJ6b8tzCQBm/vvvv5cVV1wxEAQcA+fMmROYr1Iz5BYA7OsDBgzwnbdvvvlGVltttUqd21D9zi0A+LLfffddQRtoouuvv15OOKG6bWVyCwAmvVevXjJixAhZYoklijDw119/ySabbCLTp2NFXb2UawAwrVtttZUMHz5cmjVrtmiWv/rqKznllFPk/vvvr96Z/3dkuQcAfOBU0KdPH1lllVXkvffek6efflr+/PPPqp98BmgBkItpNg/SAsACwNoE5hkDdgXI8+xbGSDns28BYAFgt4CcY8ACwALAngLyjAG7AuR59q0QmO7s412EmRl2hL/++mu6lWdUm10BUmJsly5d5JlnnhHMzR977DE56qij5LPPFgXjSqmV9KvJLQC222475xZw9dVXl99//12+/vpref311+Xyyy+XV199NTKnn3jiCdlpJ8LuLCQuk2gjjOl55MZSLJBbALz00kuy5ZZbFrFy5MiRsueee0Zi8VJLLSU//vijLLnkknXKVYJBSeYAWHPNNeWwww6Tzp07y7rrrutcuTZu3NhZKrOkTz/91JnI1157raiZJk2aOEahDRsSH6sukb9TJ8LohSfGpvMmHjp0qBx33HHhK6qHnJkCACOLd955R1ZYYYV6GNpCw882bdrIDz/8UKf9jh07aoFBplmzZknbtm0j9ff000+XK664oqjMueeeK5dcckmkukqdOTMANGrUSKZNmybt29dv4NHjjz9ebrjhhjp87devnzzwwANaXmMBjL1gFHrooYdk772duIt1SNd2lHpLkTczAOBy/d1335ViDL5tHHTQQXLPPffUyXP22Wcbv8wvvvhC1lprrUj9njFjhnbVwKkEQXPDDTeUzTffXDbbbDPB2bR58+aOSbq7FbIdIUOw+mCR9Morrzi+CD///HOkfsTJnBkA6MzDDz/sBFmoLxo8eLDj/+d1777tttscuURHn3/+uTRtyrMI4QiDUs78Onni448/FmQghMSoRGAKTNPYWl588cWoxUPnzxQAO+64o4wfPz50Z9LKiNsXypgHH3xQW+WECROke/fu2t8QHlu2DP8mxvrrr5+55TBAOOaYY+SDDz5Ii0WL6skUANjcP/roo9K1KxHKS0Pffvut7LrrrjJ16lRjg5h6M3E6IhYAy3QYQs4566yz5MILLwyTPVEetgk+qClTCPufHmUKALqJRH3dddfJ3LlzHZmAve6XX35xFCVRIm/g0Ikz59JLL+0IXLpJ+uSTT6RHjx7OXupHfm5h7Oft2rXzLc95ny1k4MCBkVaLpNOGpxK+CmlqGDMHQNJBe8ujYr3xxhuL9AgITyzrQcxhz0bzZyLcxjt06KD9GRAiVF500UWR5IQ0eUAEszTlqooCwEknnSTXXHNNET9R3eLFy5cdROgm/Jw90RBuvXVx5NxWrVo50cR02sOgNk2/IzyyMiJAcioI465OXVw4ccROgyoGAGeeeaZcdtllRWNGA8eez7YShlhC33jD/JAWFzo77LBDnarQHH744YeORB+H/v77b3nrrbfk+eefd/bwmTNnOgLd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" rel="icon" type="image/x-icon">
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
