<?php

/**
 * App-friendly entry point for the radio player.
 *
 * Usage: app-player.php?audience=kids or app-player.php?audience=adult
 * Set a fixed audience in the admin panel for child-facing deployments.
 */

declare(strict_types=1);

$audience = $_GET['audience'] ?? null;
if (!in_array($audience, ['kids', 'adult'], true)) {
    unset($_GET['audience']);
}

require __DIR__ . '/index.php';
