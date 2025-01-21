<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
        }

        h1 {
            font-size: 60px;
            color: #ff4c4c;
        }

        p {
            font-size: 18px;
            color: #333;
        }

        .back-btn, .home-btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            font-size: 16px;
            color: #fff;
            background-color: #2ea2cc;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-btn:hover, .home-btn:hover {
            background-color: #1a88b0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>404</h1>
        <p>Oops! The page you're looking for is still under development or doesn't exist.</p>
        <button class="back-btn" onclick="goBack()">Go Back</button>
        <a href="./index.php" class="home-btn">Return to Home</a>
    </div>

    <script>
        // Go back to the previous page
        function goBack() {
            window.history.back();
        }
    </script>
</body>

</html>
