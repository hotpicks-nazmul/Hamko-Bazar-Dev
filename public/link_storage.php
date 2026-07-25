<?php
$target = __DIR__ . '/../storage/app/public';
$link = __DIR__ . '/storage';
if (!file_exists($link)) {
    symlink($target, $link);
    echo 'SYMLINK_CREATED';
} elseif (is_dir($link) && !is_link($link)) {
    // It's a real directory, remove it and create symlink
    echo 'DIR_EXISTS_RENAMING';
    rename($link, $link . '_bak');
    symlink($target, $link);
    echo 'SYMLINK_CREATED_AFTER_RENAME';
} else {
    echo 'ALREADY_LINK';
}
