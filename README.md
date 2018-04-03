# UnZipper
Unzip zip files. One file server side simple unzipper with intuitive UI.

## Screenshot
[![UnZipper](screenshot.png)](screenshot.png)

## Features
- Single file script.
- Simple and intuitive.
- Few methods of unzip.
- Includes Bootstrap 4 toolkit.
- Includes FontAwesome 5 icons.
- Prevents redo action after refresh.
- Limited actions of unzip and delete:
    - only zip files may be unziped,
    - only zip files and script itself may be deleted,
    - all actions require confirmation.
- Available in few languages: en, pl, de, es, ru (auto detected or posibble to change by custom URL for e.g. `unzipper.php/pl`).

## Usage
Upload `unzipper.php` file to your directory with zip file(s) on the server and use it in your browser:
```
http://yourdomain.com/path/to/unzipper.php
```
### Language change
Available: en, pl, de, es, ru.
```
http://yourdomain.com/path/to/unzipper.php/language
```
or
```
http://yourdomain.com/path/to/unzipper.php?lang=language
```

After you are done remember to delete `unzipper.php` script and all unnecessary zip files.

## Download
[unzipper.php](https://raw.github.com/riwert/unzipper/master/unzipper.php)
