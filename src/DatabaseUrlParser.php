<?php
class DatabaseUrlParser {
    public function toRedBean($databaseUrl) {
        $parsed = $this->parse($databaseUrl);
        return [
            'connection' => "mysql:host={$parsed['host']}:{$parsed['port']};dbname={$parsed['db']}",
            'user' => $parsed['user'],
            'pass' => $parsed['pass']
        ];
    }

    public function toPhinxEnvVars($databaseUrl) {
        $parsed = $this->parse($databaseUrl);
        return [
            "PHINX_DBHOST={$parsed['host']}",
            "PHINX_DBPORT={$parsed['port']}",
            "PHINX_DBNAME={$parsed['db']}",
            "PHINX_DBUSER={$parsed['user']}",
            "PHINX_DBPASS={$parsed['pass']}"
        ];
    }

    private function parse($url) {
        $parsed = parse_url($url);
        return array_merge($parsed, [ 'db' => ltrim($parsed['path'], '/') ]);
    }
}
