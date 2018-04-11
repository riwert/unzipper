<?php
session_start();

// === Constants === //
const LANGUAGE = 'pl';
const TRANSLATIONS = [
'pl' => array (
  'method' => 'Metoda rozpakowywania',
  'download' => 'Pobieranie pliku',
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
