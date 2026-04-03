<?php
$dbConfig = [
    'enabled' => true,
    'host' => 'db',            // หากใช้ Docker-compose ให้ใช้ 'db'
    'port' => 3306,
    'dbname' => 'service_center', 
    'user' => 'root',
    'pass' => 'root',          
    'table' => 'claims',
];

// URLs สำหรับแยก Front/Back (ใช้ Port ตาม docker-compose.yml)
define('BASE_URL_FRONTEND', 'http://localhost:8870/frontend');
define('BASE_URL_BACKEND', 'http://localhost:8871/backend');


function getServiceCenterPDO()
{
    global $dbConfig;
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['dbname']
    );

    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function getServiceCenterTable()
{
    global $dbConfig;
    return $dbConfig['table'];
}
