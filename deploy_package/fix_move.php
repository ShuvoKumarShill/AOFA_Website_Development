<?php
/**
 * AOFA Fix: Move files from /public_html/public_html/ → /public_html/
 */
header('Content-Type: text/plain');
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

echo "=== AOFA FILE MOVE FIX ===\n\n";

$src = __DIR__ . '/public_html/';
$dst = __DIR__ . '/';

if (!is_dir($src)) {
    echo "ERROR: Source directory '$src' does not exist.\n";
    echo "Listing current directory:\n";
    foreach (scandir(__DIR__) as $item) {
        echo "  $item\n";
    }
    exit;
}

echo "Source: $src\n";
echo "Destination: $dst\n\n";

function move_dir_contents($src, $dst) {
    $items = scandir($src);
    $moved = 0;
    $errors = 0;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $s = rtrim($src, '/') . '/' . $item;
        $d = rtrim($dst, '/') . '/' . $item;

        // Skip the destination's own helpers to avoid overwriting
        $skip = ['fix_move.php', 'do_deploy.php', 'test_diag.php', 'wp-config.php',
                 'chunk_unzip.php', 'import_db_temp.php', 'run_exec_unzip.php',
                 'run_unzip.php', 'run_unzip_standalone.php', 'unzip_import_helper.php'];
        if (in_array($item, $skip)) {
            echo "  SKIP (protected): $item\n";
            continue;
        }

        if (file_exists($d) && is_dir($d)) {
            // Recursively merge directories
            $sub = move_dir_contents($s, $d);
            $moved += $sub[0];
            $errors += $sub[1];
            // Remove empty source dir
            @rmdir($s . '/' . $item);
        } elseif (rename($s, $d)) {
            $moved++;
        } else {
            // Try copy+delete for cross-device moves
            if (is_dir($s)) {
                echo "  WARN: Cannot move dir '$item' - trying recursive copy\n";
            } elseif (@copy($s, $d)) {
                @unlink($s);
                $moved++;
            } else {
                echo "  ERROR: Failed to move '$item'\n";
                $errors++;
            }
        }
    }
    return [$moved, $errors];
}

echo "Moving files...\n";
[$moved, $errors] = move_dir_contents($src, $dst);
echo "\nMoved: $moved items, Errors: $errors items\n";

// Remove the now-empty public_html subdirectory
if (is_dir($src)) {
    $remaining = array_diff(scandir($src), ['.', '..']);
    if (empty($remaining)) {
        rmdir($src);
        echo "Removed empty source directory: public_html/\n";
    } else {
        echo "Note: Source directory still has " . count($remaining) . " items remaining:\n";
        foreach ($remaining as $r) echo "  $r\n";
    }
}

echo "\n=== MOVE COMPLETE — Site should now be live at https://aofabd.org ===\n";

// Self-delete
@unlink(__FILE__);
