<?php

require __DIR__.'/../vendor/autoload.php';
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;

$root = dirname(__DIR__);
$source = $root.'/.migration-backup/source';
$schema = file_get_contents($root.'/.migration-backup/schema.sql');
preg_match_all('/CREATE TABLE `[^`]+` \(.*?\) ENGINE=.*?;/s', $schema, $matches);
$creates = [];
foreach ($matches[0] as $sql) {
    $sql = preg_replace('/ AUTO_INCREMENT=\d+/', '', $sql);
    $creates[] = str_replace('CREATE TABLE ', 'CREATE TABLE IF NOT EXISTS ', $sql);
}
$parser = (new ParserFactory)->createForNewestSupportedVersion();
$finder = new NodeFinder;
$alters = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source)) as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), 'PHPMailer')) {
        continue;
    }
    $nodes = $parser->parse(file_get_contents($file->getPathname()));
    foreach ($finder->findInstanceOf($nodes, Node\Scalar\String_::class) as $string) {
        $sql = trim($string->value);
        if (preg_match('/^CREATE TABLE\s/i', $sql)) {
            $creates[] = $sql;
        }
        if (preg_match('/^ALTER TABLE\s/i', $sql) && ! str_contains($sql, '$')) {
            $alters[] = $sql;
        }
    }
}
$creates[] = str_replace('expires_at TIMESTAMP NOT NULL', 'expires_at DATETIME NOT NULL', file_get_contents($source.'/database/job_offers_table.sql'));
$alters = array_merge($alters, [
    'ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL',
    'ALTER TABLE users ADD COLUMN remember_token VARCHAR(100) NULL',
    'ALTER TABLE users ADD COLUMN has_multiple_branches TINYINT(1) NOT NULL DEFAULT 0',
    'ALTER TABLE users ADD COLUMN branch_location VARCHAR(255) NULL',
    'ALTER TABLE users ADD COLUMN receive_update_notifications TINYINT(1) NOT NULL DEFAULT 1',
    'ALTER TABLE events ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0',
    'ALTER TABLE events ADD COLUMN archived_at DATETIME NULL',
    'ALTER TABLE interviews ADD COLUMN offer_id INT NULL',
    'ALTER TABLE interviews MODIFY application_id INT NULL, MODIFY job_id INT NULL',
]);
if (! is_dir($root.'/database/schema')) {
    mkdir($root.'/database/schema', 0755, true);
}
file_put_contents($root.'/database/schema/gradconn.json', json_encode(['create' => array_values(array_unique($creates)), 'alter' => array_values(array_unique($alters))], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo count($creates).' baseline/feature table definitions and '.count($alters)." versioned schema updates captured.\n";
