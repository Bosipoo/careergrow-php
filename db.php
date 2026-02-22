<?php
function getDB() {
    $db = new PDO('sqlite:' . __DIR__ . '/careergrow.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS applications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        company TEXT NOT NULL,
        role TEXT NOT NULL,
        status TEXT DEFAULT 'Applied',
        next_action TEXT,
        notes TEXT,
        created_at TEXT DEFAULT (date('now'))
    )");
    return $db;
}