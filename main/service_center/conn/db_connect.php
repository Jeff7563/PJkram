<?php
$dbConfig = [
    'enabled' => true,
    'host' => 'db',            
    'port' => 3306,
    'dbname' => 'service_center', 
    'user' => 'root',
    'pass' => 'root',          
    'table' => 'claims',
];


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
