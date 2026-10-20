<?php

declare(strict_types=1);

// Uso: php tools/check-coverage.php [clover.xml] [mínimo en %]
$file = $argv[1] ?? __DIR__ . '/../build/coverage.xml';
$minimum = (float) ($argv[2] ?? 90);

$xml = is_file($file) ? simplexml_load_file($file) : false;
if ($xml === false) {
    fwrite(STDERR, "No se pudo leer {$file}\n");
    exit(2);
}

$metrics = $xml->project->metrics;
$statements = (int) $metrics['statements'];
$percent = $statements === 0 ? 0.0 : 100 * (int) $metrics['coveredstatements'] / $statements;

printf("Cobertura de líneas: %.1f%% (mínimo %.1f%%)\n", $percent, $minimum);
exit($percent >= $minimum ? 0 : 1);
