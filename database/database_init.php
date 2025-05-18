<?php

$pdo = new PDO('sqlite:database/database.sqlite');

$links = "CREATE TABLE IF NOT EXISTS links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    long_url VARCHAR(2048),
    shortcode CHAR(6)
);";

$clicks = "CREATE TABLE clicks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    link_id INTEGER NOT NULL,
    utc_timestamp DATETIME NOT NULL,
    source_ip VARCHAR(45) NOT NULL,
    referrer TEXT NOT NULL,
    CONSTRAINT fk_link FOREIGN KEY (link_id) REFERENCES links(id)
);";


$pdo->exec($links);
$pdo->exec($clicks);