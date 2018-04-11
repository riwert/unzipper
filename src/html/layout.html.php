<!DOCTYPE html>
<html lang="<?=_lang()?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$unZipper->getTitle()?></title>
    <link rel="stylesheet" type="text/css" media="screen" href="https://bootswatch.com/4/lumen/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
    <link rel="icon" href="src/img/favicon.ico">
    <link rel="stylesheet" href="src/css/style.css">
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
                        <?php $i = 1; foreach ($unZipper->getMethods() as $methodKey => $methodName): ?>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="method" value="<?=$methodKey?>" <?=(empty($_POST['method']) && $i == 1 || ! empty($_POST['method']) && $_POST['method'] == $methodKey) ? 'checked' : ''?> />
                                    <?=$methodName?>
                                </label>
                            </div>
                        <?php $i++; endforeach; ?>
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
                    <input type="hidden" name="delfiles[]" value="<?=basename(__FILE__)?>" />
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
    <script src="src/js/script.js"></script>
</body>
</html>
