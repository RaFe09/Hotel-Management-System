<?php












declare(strict_types=1);

function stderr(string $msg): void {
    fwrite(STDERR, $msg . PHP_EOL);
}

function nowStamp(): string {
    return date('Ymd-His');
}

function normalizePath(string $path): string {
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);
    return $path;
}

function parseArgs(array $argv): array {
    $args = [
        'root' => '.',
        'ext' => ['php', 'js', 'css', 'sql'],
        'backup' => true,
        'dry_run' => false,
        'backup_dir' => null,
    ];

    for ($i = 1; $i < count($argv); $i++) {
        $a = $argv[$i];
        if ($a === '--root' && isset($argv[$i + 1])) {
            $args['root'] = $argv[++$i];
            continue;
        }
        if ($a === '--ext' && isset($argv[$i + 1])) {
            $ext = array_filter(array_map('trim', explode(',', (string)$argv[++$i])));
            $args['ext'] = array_values(array_unique(array_map('strtolower', $ext)));
            continue;
        }
        if ($a === '--no-backup') {
            $args['backup'] = false;
            continue;
        }
        if ($a === '--backup') {
            $args['backup'] = true;
            continue;
        }
        if ($a === '--dry-run') {
            $args['dry_run'] = true;
            continue;
        }
        if ($a === '--help' || $a === '-h') {
            $args['help'] = true;
            continue;
        }
    }

    return $args;
}

function usage(): void {
    $u = <<<TXT
Strip comments from files under a root directory.

Options:
  --root <path>        Root directory (default: .)
  --ext <csv>          Extensions, e.g. php,js,css,sql (default: php,js,css,sql)
  --backup / --no-backup  Enable/disable backups (default: backup enabled)
  --dry-run            Show what would change but do not write files

Examples:
  php tools/strip_comments.php
  php tools/strip_comments.php --root . --ext php,js --dry-run
TXT;
    echo $u . PHP_EOL;
}

function ensureDir(string $dir): void {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

function writeBackup(string $backupRoot, string $rootAbs, string $fileAbs, string $contents): void {
    $rootAbs = normalizePath($rootAbs);
    $fileAbs = normalizePath($fileAbs);
    $rel = ltrim(substr($fileAbs, strlen(rtrim($rootAbs, '/'))), '/');
    $dest = rtrim($backupRoot, '/') . '/' . $rel;
    ensureDir(dirname($dest));
    file_put_contents($dest, $contents);
}

function stripHtmlCommentsPreserveNewlines(string $html): string {
    $len = strlen($html);
    $out = '';
    $in = false;

    for ($i = 0; $i < $len; $i++) {
        $c = $html[$i];
        if (!$in) {
            if ($c === '<' && ($i + 3 < $len) && substr($html, $i, 4) === '











') {
            $in = false;
            $i += 2;
        }
    }

    return $out;
}

function stripInlineHtmlMixed(string $html): string {
    $out = '';
    $pos = 0;
    $len = strlen($html);

    while ($pos < $len) {
        $pScript = stripos($html, '<script', $pos);
        $pStyle  = stripos($html, '<style', $pos);

        $nextPos = null;
        $tag = null;
        if ($pScript !== false) { $nextPos = $pScript; $tag = 'script'; }
        if ($pStyle !== false && ($nextPos === null || $pStyle < $nextPos)) { $nextPos = $pStyle; $tag = 'style'; }

        if ($nextPos === null) {
            $out .= stripHtmlCommentsPreserveNewlines(substr($html, $pos));
            break;
        }

        $out .= stripHtmlCommentsPreserveNewlines(substr($html, $pos, $nextPos - $pos));

        $openEnd = strpos($html, '>', $nextPos);
        if ($openEnd === false) {
            $out .= stripHtmlCommentsPreserveNewlines(substr($html, $nextPos));
            break;
        }

        $closeStart = stripos($html, '</' . $tag, $openEnd + 1);
        if ($closeStart === false) {
            $out .= stripHtmlCommentsPreserveNewlines(substr($html, $nextPos));
            break;
        }
        $closeEnd = strpos($html, '>', $closeStart);
        if ($closeEnd === false) {
            $out .= stripHtmlCommentsPreserveNewlines(substr($html, $nextPos));
            break;
        }

        $openTag = substr($html, $nextPos, ($openEnd - $nextPos) + 1);
        $inner = substr($html, $openEnd + 1, $closeStart - ($openEnd + 1));
        $closeTag = substr($html, $closeStart, ($closeEnd - $closeStart) + 1);

        if ($tag === 'script') {
            $inner = stripCStyleAndLineComments($inner, [
                'line_slashslash' => true,
                'block_c' => true,
                'line_sql' => false,
                'line_hash' => false,
                'js_regex' => true,
            ]);
        } else {
            $inner = stripCStyleAndLineComments($inner, [
                'line_slashslash' => false,
                'block_c' => true,
                'line_sql' => false,
                'line_hash' => false,
                'js_regex' => false,
            ]);
        }

        $out .= $openTag . $inner . $closeTag;
        $pos = $closeEnd + 1;
    }

    return $out;
}

function stripPhpComments(string $code): string {
     
     
    if (strpos($code, '<?') === false) {
        return $code;
    }

    $tokens = token_get_all($code);
    $out = '';

    foreach ($tokens as $t) {
        if (is_array($t)) {
            $id = $t[0];
            $text = $t[1];

            if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                 
                $nl = substr_count($text, "\n");
                if ($nl > 0) {
                    $out .= str_repeat("\n", $nl);
                } else {
                     
                    $out .= ' ';
                }
                continue;
            }

            if (defined('T_INLINE_HTML') && $id === T_INLINE_HTML) {
                $out .= stripInlineHtmlMixed($text);
                continue;
            }

            $out .= $text;
        } else {
            $out .= $t;
        }
    }

    return stripInlineHtmlMixed($out);
}

function isIdentChar(string $ch): bool {
    $o = ord($ch);
    return ($o >= 48 && $o <= 57) || ($o >= 65 && $o <= 90) || ($o >= 97 && $o <= 122) || $ch === '_' || $ch === '$';
}

function lastNonWsChar(string $s, int $i): string {
    for ($j = $i; $j >= 0; $j--) {
        $c = $s[$j];
        if ($c !== ' ' && $c !== "\t" && $c !== "\r" && $c !== "\n") {
            return $c;
        }
    }
    return '';
}

function looksLikeRegexStart(string $code, int $slashPos): bool {
     
    $prev = lastNonWsChar($code, $slashPos - 1);
    if ($prev === '') return true;
    $set = ['(', '[', '{', '=', ':', ',', ';', '!', '?', '+', '-', '*', '/', '%', '&', '|', '^', '~', '<', '>'];
    return in_array($prev, $set, true);
}

function consumeRegexLiteral(string $code, int $i): array {
     
    $len = strlen($code);
    $out = '/';
    $i++;
    $inClass = false;
    $escaped = false;

    while ($i < $len) {
        $c = $code[$i];
        $out .= $c;

        if ($escaped) {
            $escaped = false;
            $i++;
            continue;
        }
        if ($c === '\\') {
            $escaped = true;
            $i++;
            continue;
        }
        if ($c === '[') {
            $inClass = true;
            $i++;
            continue;
        }
        if ($c === ']') {
            $inClass = false;
            $i++;
            continue;
        }
        if ($c === '/' && !$inClass) {
            $i++;
             
            while ($i < $len && isIdentChar($code[$i])) {
                $out .= $code[$i];
                $i++;
            }
            break;
        }
        $i++;
    }

    return [$out, $i];
}

function stripCStyleAndLineComments(string $code, array $modes): string {
     
     
     
     
     
     
    $len = strlen($code);
    $out = '';

    $inS = false;   
    $inD = false;   
    $inB = false;   
    $inRegex = false;
    $escaped = false;

    $inLine = false;
    $lineKind = '';  
    $inBlock = false;

    for ($i = 0; $i < $len; $i++) {
        $c = $code[$i];
        $n = ($i + 1 < $len) ? $code[$i + 1] : '';

        if ($inLine) {
            if ($c === "\n") {
                $out .= "\n";
                $inLine = false;
                $lineKind = '';
            }
            continue;
        }

        if ($inBlock) {
            if ($c === '*' && $n === '/') {
                $inBlock = false;
                $i++;  
                continue;
            }
             
            if ($c === "\n") {
                $out .= "\n";
            }
            continue;
        }

        if ($inRegex) {
             
            $out .= $c;
            continue;
        }

         
        if ($inS || $inD || $inB) {
            $out .= $c;
            if ($escaped) {
                $escaped = false;
                continue;
            }
            if ($c === '\\') {
                $escaped = true;
                continue;
            }
            if ($inS && $c === "'") $inS = false;
            if ($inD && $c === '"') $inD = false;
            if ($inB && $c === '`') $inB = false;
            continue;
        }

         
        if (!empty($modes['block_c']) && $c === '/' && $n === '*') {
            $inBlock = true;
            $i++;  
            continue;
        }
        if (!empty($modes['line_slashslash']) && $c === '/' && $n === '/') {
            $inLine = true;
            $lineKind = '//';
            $i++;  
            continue;
        }
        if (!empty($modes['line_sql']) && $c === '-' && $n === '-') {
            $inLine = true;
            $lineKind = '--';
            $i++;  
            continue;
        }
        if (!empty($modes['line_hash']) && $c === '#') {
            $inLine = true;
            $lineKind = '#';
            continue;
        }

         
        if (!empty($modes['js_regex']) && $c === '/' && $n !== '/' && $n !== '*') {
            if (looksLikeRegexStart($code, $i)) {
                [$rx, $nextI] = consumeRegexLiteral($code, $i);
                $out .= $rx;
                $i = $nextI - 1;
                continue;
            }
        }

         
        if ($c === "'") { $inS = true; $out .= $c; continue; }
        if ($c === '"') { $inD = true; $out .= $c; continue; }
        if ($c === '`') { $inB = true; $out .= $c; continue; }

        $out .= $c;
    }

    return $out;
}

function stripByExtension(string $ext, string $code): string {
    $ext = strtolower($ext);
    if ($ext === 'php') {
        return stripPhpComments($code);
    }
    if ($ext === 'js') {
        return stripCStyleAndLineComments($code, [
            'line_slashslash' => true,
            'block_c' => true,
            'line_sql' => false,
            'line_hash' => false,
            'js_regex' => true,
        ]);
    }
    if ($ext === 'css') {
        return stripCStyleAndLineComments($code, [
            'line_slashslash' => false,
            'block_c' => true,
            'line_sql' => false,
            'line_hash' => false,
            'js_regex' => false,
        ]);
    }
    if ($ext === 'sql') {
        return stripCStyleAndLineComments($code, [
            'line_slashslash' => false,
            'block_c' => true,
            'line_sql' => true,
            'line_hash' => true,
            'js_regex' => false,
        ]);
    }
    return $code;
}

function shouldSkipDirName(string $name): bool {
    $skip = [
        '.git', '.svn', '.hg',
        'node_modules', 'vendor',
        '_comment_backups',
    ];
    return in_array($name, $skip, true);
}

function walkFiles(string $rootAbs, array $extensions): Generator {
    $dirIter = new RecursiveDirectoryIterator($rootAbs, FilesystemIterator::SKIP_DOTS);
    $filter = new RecursiveCallbackFilterIterator($dirIter, function (SplFileInfo $current) {
        if ($current->isDir()) {
            return !shouldSkipDirName($current->getFilename());
        }
        return true;
    });

    $it = new RecursiveIteratorIterator($filter);
    foreach ($it as $path => $info) {
         
        if (!$info->isFile()) continue;
        $name = $info->getFilename();
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $extensions, true)) continue;
        yield [$path, $ext];
    }
}

 
$args = parseArgs($argv);
if (!empty($args['help'])) {
    usage();
    exit(0);
}

$root = $args['root'];
$rootAbs = realpath($root);
if ($rootAbs === false || !is_dir($rootAbs)) {
    stderr("Invalid --root: " . $root);
    exit(2);
}
$rootAbs = normalizePath($rootAbs);

$exts = $args['ext'];
if (empty($exts)) {
    stderr("No extensions provided (use --ext php,js,css,sql).");
    exit(2);
}

$backupRoot = null;
if ($args['backup']) {
    $backupRoot = normalizePath($rootAbs . '/tools/_comment_backups/' . nowStamp());
    if (!$args['dry_run']) {
        ensureDir($backupRoot);
    }
}

$total = 0;
$changed = 0;
$errors = 0;

foreach (walkFiles($rootAbs, $exts) as [$fileAbs, $ext]) {
    $total++;
    $orig = file_get_contents($fileAbs);
    if ($orig === false) {
        $errors++;
        continue;
    }
    $new = stripByExtension($ext, $orig);
    if ($new === $orig) continue;

    $changed++;
    if ($args['dry_run']) continue;

    if ($backupRoot !== null) {
        writeBackup($backupRoot, $rootAbs, $fileAbs, $orig);
    }

    $ok = file_put_contents($fileAbs, $new);
    if ($ok === false) {
        $errors++;
    }
}

echo "Scanned: {$total} files" . PHP_EOL;
echo "Changed: {$changed} files" . PHP_EOL;
echo "Errors : {$errors} files" . PHP_EOL;
if ($backupRoot !== null) {
    echo "Backups: {$backupRoot}" . PHP_EOL;
}

