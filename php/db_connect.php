<?php
// db_connect.php - Підключення до бази даних

// Налаштування підключення
$db_host = 'localhost';     // Хост бази даних
$db_user = 'root';          // Ім'я користувача (замінити на ваше)
$db_password = '';          // Пароль (замінити на ваш)
$db_name = 'pokemon';       // Назва бази даних

// Створення підключення
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Перевірка підключення
if ($conn->connect_error) {
    die("Помилка підключення до бази даних: " . $conn->connect_error);
}

// Встановлення кодування UTF-8
$conn->set_charset("utf8mb4");