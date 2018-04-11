<?php
session_start();

// === Constants === //
const LANGUAGE = 'de';
const TRANSLATIONS = [
'de' => array (
  'method' => 'Auspackmethode',
  'download' => 'Download-Datei',
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
  'msg_remind_to_delete' => 'Denken Sie daran, dieses Skript zu löschen, wenn Sie fertig sind.',
  'msg_are_you_sure' => 'Bist du sicher?',
  'msg_confirm_your_action' => 'Bestätigen Sie Ihre Aktion.',
  'msg_action_proceed' => 'Ja, mach weiter',
  'msg_action_close' => 'Nein, schließe es',
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
