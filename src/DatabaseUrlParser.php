<?php
class DatabaseUrlParser {
    public function toRedBean($databaseUrl) {
        $parsed = parse_url($databaseUrl);
        $dbname = ltrim($parsed['path'], '/');
        $connection = "mysql:host={$parsed['host']}:{$parsed['port']};dbname=$dbname";
        return [
            'connection' => $connection,
            'user' => $parsed['user'],
            'pass' => $parsed['pass']
        ];
    }
}
