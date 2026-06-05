<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * @var array<string, mixed>
     */
    public array $default = [
        'DSN'        => '',
        'hostname'   => '',
        'username'   => '',
        'password'   => '',
        'database'   => 'postgres',
        'schema'     => 'public',
        'DBDriver'   => 'Postgre',
        'DBPrefix'   => '',
        'pConnect'   => false,
        'DBDebug'    => true,
        'charset'    => 'utf8',
        'sslmode'    => 'require',
        'swapPre'    => '',
        'failover'   => [],
        'port'       => 5432,
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];


    //    /**
    //     * Sample database connection for SQLite3.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'database'    => 'database.db',
    //        'DBDriver'    => 'SQLite3',
    //        'DBPrefix'    => '',
    //        'DBDebug'     => true,
    //        'swapPre'     => '',
    //        'failover'    => [],
    //        'foreignKeys' => true,
    //        'busyTimeout' => 1000,
    //        'synchronous' => null,
    //        'dateFormat'  => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for Postgre.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'public',
    //        'DBDriver'   => 'Postgre',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'port'       => 5432,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for SQLSRV.
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => '',
    //        'hostname'   => 'localhost',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'database'   => 'ci4',
    //        'schema'     => 'dbo',
    //        'DBDriver'   => 'SQLSRV',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'utf8',
    //        'swapPre'    => '',
    //        'encrypt'    => false,
    //        'failover'   => [],
    //        'port'       => 1433,
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    //    /**
    //     * Sample database connection for OCI8.
    //     *
    //     * You may need the following environment variables:
    //     *   NLS_LANG                = 'AMERICAN_AMERICA.UTF8'
    //     *   NLS_DATE_FORMAT         = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_FORMAT    = 'YYYY-MM-DD HH24:MI:SS'
    //     *   NLS_TIMESTAMP_TZ_FORMAT = 'YYYY-MM-DD HH24:MI:SS'
    //     *
    //     * @var array<string, mixed>
    //     */
    //    public array $default = [
    //        'DSN'        => 'localhost:1521/XEPDB1',
    //        'username'   => 'root',
    //        'password'   => 'root',
    //        'DBDriver'   => 'OCI8',
    //        'DBPrefix'   => '',
    //        'pConnect'   => false,
    //        'DBDebug'    => true,
    //        'charset'    => 'AL32UTF8',
    //        'swapPre'    => '',
    //        'failover'   => [],
    //        'dateFormat' => [
    //            'date'     => 'Y-m-d',
    //            'datetime' => 'Y-m-d H:i:s',
    //            'time'     => 'H:i:s',
    //        ],
    //    ];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',  // Needed to ensure we're working correctly with prefixes live. DO NOT REMOVE FOR CI DEVS
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? env('DATABASE_URL'));
        if (is_string($databaseUrl) && $databaseUrl !== '') {
            $parts = parse_url(trim($databaseUrl));
            if (is_array($parts)) {
                $this->default['hostname'] = $parts['host'] ?? $this->default['hostname'];
                $this->default['username'] = isset($parts['user']) ? urldecode($parts['user']) : $this->default['username'];
                $this->default['password'] = isset($parts['pass']) ? urldecode($parts['pass']) : $this->default['password'];
                $this->default['database'] = isset($parts['path']) ? ltrim($parts['path'], '/') : $this->default['database'];
                $this->default['port'] = $parts['port'] ?? $this->default['port'];
            }
        }

        $this->default['hostname'] = getenv('DATABASE_DEFAULT_HOSTNAME') ?: ($_ENV['DATABASE_DEFAULT_HOSTNAME'] ?? $_SERVER['DATABASE_DEFAULT_HOSTNAME'] ?? $this->default['hostname']);
        $this->default['username'] = getenv('DATABASE_DEFAULT_USERNAME') ?: ($_ENV['DATABASE_DEFAULT_USERNAME'] ?? $_SERVER['DATABASE_DEFAULT_USERNAME'] ?? $this->default['username']);
        $this->default['password'] = getenv('DATABASE_DEFAULT_PASSWORD') ?: ($_ENV['DATABASE_DEFAULT_PASSWORD'] ?? $_SERVER['DATABASE_DEFAULT_PASSWORD'] ?? $this->default['password']);
        $this->default['database'] = getenv('DATABASE_DEFAULT_DATABASE') ?: ($_ENV['DATABASE_DEFAULT_DATABASE'] ?? $_SERVER['DATABASE_DEFAULT_DATABASE'] ?? $this->default['database']);
        $this->default['sslmode'] = getenv('DATABASE_DEFAULT_SSLMODE') ?: ($_ENV['DATABASE_DEFAULT_SSLMODE'] ?? $_SERVER['DATABASE_DEFAULT_SSLMODE'] ?? $this->default['sslmode']);

        $legacyHost = env('database.default.hostname');
        $legacyUser = env('database.default.username');
        $legacyPassword = env('database.default.password');
        $legacyDatabase = env('database.default.database');
        $legacySslMode = env('database.default.sslmode');
        $legacyPort = env('database.default.port');

        if (is_string($legacyHost) && trim($legacyHost) !== '') {
            $this->default['hostname'] = trim($legacyHost);
        }
        if (is_string($legacyUser) && trim($legacyUser) !== '') {
            $this->default['username'] = trim($legacyUser);
        }
        if (is_string($legacyPassword) && trim($legacyPassword) !== '') {
            $this->default['password'] = trim($legacyPassword);
        }
        if (is_string($legacyDatabase) && trim($legacyDatabase) !== '') {
            $this->default['database'] = trim($legacyDatabase);
        }
        if (is_string($legacySslMode) && trim($legacySslMode) !== '') {
            $this->default['sslmode'] = trim($legacySslMode);
        }
        if (is_numeric($legacyPort)) {
            $this->default['port'] = (int) $legacyPort;
        }

        $portOverride = getenv('DATABASE_DEFAULT_PORT') ?: ($_ENV['DATABASE_DEFAULT_PORT'] ?? $_SERVER['DATABASE_DEFAULT_PORT'] ?? null);
        if (is_numeric($portOverride)) {
            $this->default['port'] = (int) $portOverride;
        }

        // Ensure that we always set the database group to 'tests' if
        // we are currently running an automated test suite, so that
        // we don't overwrite live data on accident.
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }
    }
}

