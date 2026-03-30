<?php

function getPDOConnection(): ?PDO
{
    $dbHostCandidates = ['db', 'mysql_db', '127.0.0.1'];
    $dbName = 'binome_s6';
    $dbUser = 'nast';
    $dbPass = 'nast1311';

    foreach ($dbHostCandidates as $host) {
        try {
            return new PDO(
                "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $exception) {
            // Try next host.
        }
    }

    return null;
}
