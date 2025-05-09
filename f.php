<!DOCTYPE html>
<html>
<head>
    <title>Дерево еволюції покемона - Темна тема</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #121212;
            color: #e0e0e0;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        h1 {
            color: #3498db;
            text-align: center;
        }
        form {
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #3498db;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #2a2a2a;
            color: #e0e0e0;
        }
        input[type="submit"] {
            background-color: #ee1515;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #cc0000;
        }
        .result {
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
            line-height: 1.5;
        }
        .evolution-result {
            margin-top: 10px;
            background-color: #252525;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #3a3a3a;
        }
        .result p {
            color: #ccc;
        }
        ::placeholder {
            color: #777;
        }
        :focus {
            outline: 2px solid #3498db;
        }
        h3 {
            color: #3498db;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        a {
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Дерево еволюції покемона</h1>
        
        <form method="post">
            <label for="pokemon_id">Введіть ID покемона:</label>
            <input type="number" id="pokemon_id" name="pokemon_id" min="1" required>
            <input type="submit" value="Показати еволюцію">
        </form>
        
        <div class="result">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Отримання ID від користувача
                $pokemon_id = isset($_POST["pokemon_id"]) ? intval($_POST["pokemon_id"]) : 0;
                
                if ($pokemon_id > 0) {
                    try {
                        // Підключення до бази даних
                        $servername = "localhost"; // Змініть на ваш хост
                        $username = "root";        // Змініть на вашого користувача
                        $password = "";            // Змініть на ваш пароль
                        $dbname = "pokemon";    // Змініть на назву вашої БД
                        
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // Виклик збереженої процедури
                        $stmt = $conn->prepare("CALL evolution_tree_pokemon(:pokemon_id)");
                        $stmt->bindParam(':pokemon_id', $pokemon_id, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        // Виведення результату
                        echo "<h3>Результат для покемона з ID: $pokemon_id</h3>";
                        
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($result && isset($result['result'])) {
                            echo "<div class='evolution-result'>" . $result['result'] . "</div>"; // Виводимо результат як HTML-код
                        } else {
                            echo "<p>Інформацію про еволюцію не знайдено.</p>";
                        }
                        
                        // Закриття підключення
                        $conn = null;
                        
                    } catch(PDOException $e) {
                        echo "<p style='color: #ff6b6b;'>Помилка: " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p>Будь ласка, введіть дійсний ID покемона.</p>";
                }
            } else {
                echo "<p>Введіть ID покемона та натисніть кнопку, щоб побачити його дерево еволюції.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>