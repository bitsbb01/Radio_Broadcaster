<?php

declare(strict_types=1);

/**
 * Build the first-run channel catalogue from the recommended-radio-streams M3U files.
 * Imported entries are Adult by default; the Kids player only shows stations an admin
 * explicitly reviews and labels Kids or All ages.
 */

$playlistDir = $argv[1] ?? '';
$outputFile = $argv[2] ?? __DIR__ . '/../inc/config/channels.php';

if (!is_dir($playlistDir)) {
    fwrite(STDERR, "Playlist directory not found.\n");
    exit(1);
}

function isPublicHttpStream(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parts = parse_url($url);
    if (!is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
        return false;
    }

    if (empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return false;
    }

    $host = strtolower(rtrim($parts['host'], '.'));
    if (
        in_array($host, ['localhost', 'localhost.localdomain'], true)
        || preg_match('/\.(local|localhost|internal|test)$/', $host)
    ) {
        return false;
    }

    if (
        filter_var($host, FILTER_VALIDATE_IP)
        && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
    ) {
        return false;
    }

    return true;
}

function cleanChannelName(string $name, int $fallback): string
{
    $name = html_entity_decode(trim($name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) : false;
    if (is_string($ascii)) {
        $name = $ascii;
    }

    $name = preg_replace('/[^A-Za-z0-9 -]/', ' ', $name) ?? '';
    $name = preg_replace('/\s+/', ' ', trim($name)) ?? '';
    $name = trim($name, ' -');

    if ($name === '') {
        $name = 'Recommended Station ' . $fallback;
    }

    return substr($name, 0, 80);
}

$channels = [];
$seenUrls = [];
$usedNames = [];
$skipped = 0;

$playlists = glob(rtrim($playlistDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.m3u') ?: [];
sort($playlists, SORT_STRING);

foreach ($playlists as $playlist) {
    $lines = file($playlist, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        continue;
    }

    $pendingName = null;
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        if (stripos($line, '#EXTINF:') === 0) {
            $comma = strpos($line, ',');
            $pendingName = $comma === false ? null : trim(substr($line, $comma + 1));
            continue;
        }

        if ($line[0] === '#') {
            continue;
        }

        if ($pendingName === null) {
            continue;
        }

        if (!isPublicHttpStream($line)) {
            $skipped++;
            $pendingName = null;
            continue;
        }

        $dedupeKey = strtolower($line);
        if (isset($seenUrls[$dedupeKey])) {
            $pendingName = null;
            continue;
        }
        $seenUrls[$dedupeKey] = true;

        $name = cleanChannelName($pendingName, count($channels) + 1);
        $baseName = $name;
        $suffix = 2;
        while (isset($usedNames[strtolower($name)])) {
            $tail = ' ' . $suffix++;
            $name = substr($baseName, 0, 80 - strlen($tail)) . $tail;
        }
        $usedNames[strtolower($name)] = true;

        $channels[] = [
            'name' => $name,
            'audience' => 'adults',
            'logo' => null,
            'skin' => null,
            'streams' => [
                'MP3' => [
                    'mp3' => $line,
                ],
            ],
            'stats' => [
                'method' => 'direct',
                'url' => $line,
                'fallback' => null,
            ],
        ];

        $pendingName = null;
    }
}

if ($channels === []) {
    fwrite(STDERR, "No valid public HTTP/HTTPS streams were found in the playlists.\n");
    exit(1);
}

$json = json_encode($channels, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$php = "<?php\n// Generated from the CC0 recommended-radio-streams M3U catalog.\n"
    . "return json_decode(base64_decode('" . base64_encode($json) . "'), true, 512, JSON_THROW_ON_ERROR);\n";

if (file_put_contents($outputFile, $php, LOCK_EX) === false) {
    fwrite(STDERR, "Could not write the channel catalogue.\n");
    exit(1);
}

printf("Imported %d unique stations; skipped %d invalid stream URLs.\n", count($channels), $skipped);
