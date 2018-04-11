<?php
session_start();

// === Constances === //
const LANGUAGE = '';
const TRANSLATIONS = [];

// === Helpers === //
include 'src/helper/TranslateHelper.php';
include 'src/helper/functions.php';

// === Classes === //
include 'src/class/UnZipper.php';

// === Instances === //
$unZipper = new UnZipper();

// === Template === //
include 'src/html/layout.html.php';
