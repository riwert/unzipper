<?php
session_start();

// === Constants === //
const LANGUAGE = 'ru';
const TRANSLATIONS = [
'ru' => array (
  'method' => 'Метод распаковки',
  'download' => 'Скачать файл',
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
  'msg_remind_to_delete' => 'Не забудьте удалить этот скрипт, когда закончите.',
  'msg_are_you_sure' => 'Ты уверен?',
  'msg_confirm_your_action' => 'Подтвердите свое действие.',
  'msg_action_proceed' => 'Да, продолжайте',
  'msg_action_close' => 'Нет, закройте его',
)];

// === Helpers === //
require 'src/helper/TranslateHelper.php';
require 'src/helper/functions.php';

// === Classes === //
require 'src/class/UnZipper.php';

// === Instances === //
$unZipper = new UnZipper();

// === Template === //
require 'src/html/layout.html.php';
