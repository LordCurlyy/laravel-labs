<?php
// 1. Инициализация переменных
$result = null;      // Здесь будет храниться ответ
$matrix = [];        // Здесь будут храниться введенные числа (для повторного заполнения формы)
$error = null;       // Для вывода ошибок
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST'); // Проверяем, отправлена ли форма

// 2. Обработка POST-запроса
if ($isPost) {
    // Получаем данные. Если форма отправлена из браузера, они придут в $_POST['matrix'].
    // Если через API (например, Python), они могут прийти просто в $_POST.
    $input = $_POST['matrix'] ?? $_POST;
    
    // Очищаем массив от пустых значений и переиндексируем
    $matrix = array_values(array_filter($input, function($val) {
        return $val !== '' && $val !== null;
    }));

    // Проверка: должно быть ровно 9 чисел
    if (count($matrix) === 9) {
        // Преобразуем все значения в числа с плавающей точкой (float)
        $matrix = array_map('floatval', $matrix);

        // 3. Вычисление определителя матрицы 3x3
        // Формула: a(ei − fh) − b(di − fg) + c(dh − eg)
        // Где матрица:
        // | a b c |
        // | d e f |
        // | g h i |
        
        $a = $matrix[0]; $b = $matrix[1]; $c = $matrix[2];
        $d = $matrix[3]; $e = $matrix[4]; $f = $matrix[5];
        $g = $matrix[6]; $h = $matrix[7]; $i = $matrix[8];

        $result = $a * ($e * $i - $f * $h) 
                - $b * ($d * $i - $f * $g) 
                + $c * ($d * $h - $e * $g);
    } else {
        $error = "Ошибка: необходимо передать ровно 9 чисел. Получено: " . count($matrix);
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Определитель матрицы 3x3</title>
    <style>
        /* Простые стили для красоты */
        body { font-family: Arial, sans-serif; padding: 20px; }
        .matrix-grid {
            display: grid;
            grid-template-columns: repeat(3, 70px); /* 3 колонки по 70px */
            gap: 10px;
            margin-bottom: 20px;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            text-align: center;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-left: 5px solid #4caf50;
            font-size: 18px;
        }
        .error-box {
            margin-top: 20px;
            padding: 15px;
            background: #ffebee;
            border-left: 5px solid #f44336;
            color: #c62828;
        }
    </style>
</head>
<body>

    <h1>Введите матрицу 3x3</h1>
    <p>Заполните 9 полей ниже. Определитель будет рассчитан автоматически.</p>

    <form method="POST" action="">
        <div class="matrix-grid">
            <?php
            // 4. Генерация формы с помощью цикла
            // Цикл выполнится 9 раз (от 0 до 8)
            for ($i = 0; $i < 9; $i++) {
                // Если мы уже отправляли форму, подставляем старые значения (чтобы не вводить заново)
                $val = $matrix[$i] ?? '';
                
                // name="matrix[]" говорит PHP, что это массив.
                // required - поле обязательно для заполнения.
                echo '<input type="number" step="any" name="matrix[]" value="' . htmlspecialchars($val) . '" required>';
            }
            ?>
        </div>
        <button type="submit">Вычислить</button>
    </form>

    <!-- 5. Блок вывода результата -->
    <?php if ($isPost): ?>
        <?php if ($error): ?>
            <div class="error-box"><?= $error ?></div>
        <?php elseif ($result !== null): ?>
            <div class="result-box">
                <strong>Определитель матрицы:</strong> <?= $result ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
