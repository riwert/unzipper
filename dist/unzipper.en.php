<?php
session_start();

// === Constants === //
const LANGUAGE = 'en';
const TRANSLATIONS = [
'en' => array (
  'method' => 'Unpacking method',
  'download' => 'Download file',
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
  'msg_remind_to_delete' => 'Remember to delete this script when you are done.',
  'msg_are_you_sure' => 'Are you sure?',
  'msg_confirm_your_action' => 'Confirm your action.',
  'msg_action_proceed' => 'Yes, proceed it',
  'msg_action_close' => 'No, close it',
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
