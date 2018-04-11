<?php
session_start();

// === Constants === //
const LANGUAGE = 'es';
const TRANSLATIONS = [
'es' => array (
  'method' => 'Método de desembalaje',
  'download' => 'Descargar archivo',
  'unzip_it' => 'Descomprimirlo',
  'delete_it' => 'Bórralo',
  'zip_file' => 'archivo zip',
  'zip_files' => 'archivos zip',
  'msg_found_files' => 'Encontrado %s en este directorio.',
  'msg_files_not_found' => 'No hay un archivo zip en este directorio.',
  'msg_not_zip_file' => 'Este %s no es un archivo zip.',
  'msg_error_while_unzip' => 'Error al descomprimir archivo %s.',
  'msg_unzip_success' => 'El archivo %s ha sido desconectado.',
  'msg_cannot_delete' => 'Este archivo %s no puede ser eliminado.',
  'msg_error_while_delete' => 'Error al eliminar el archivo %s.',
  'msg_delete_success' => 'Archivo %s ha sido eliminado.',
  'msg_missing_token' => 'Falta token.',
  'msg_invalid_token' => 'Simbolo no valido.',
  'msg_warning_files_overwrite' => 'Todos los archivos descomprimidos se sobrescribirán si ya existen.',
  'msg_warning_file_delete' => 'El archivo se eliminará permanentemente.',
  'msg_warning_script_delete' => 'Este archivo de script se eliminará permanentemente.',
  'msg_remind_to_delete' => 'Recuerde eliminar este script cuando haya terminado.',
  'msg_are_you_sure' => '¿Estás seguro?',
  'msg_confirm_your_action' => 'Confirma tu acción',
  'msg_action_proceed' => 'Sí, proceda',
  'msg_action_close' => 'No, ciérralo',
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
