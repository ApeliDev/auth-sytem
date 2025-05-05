<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Auth System</title>
    <link rel="stylesheet" href="css/index.css">
    <meta http-equiv="refresh" content="1; url=login.php">
    <script>
        // Redirect to login page after a brief delay
        window.onload = function() {
            setTimeout(function() {
                window.location.href = "login.php";
            }, 5000); 
        }
    </script>
</head>
<body>
    <div class="loader"></div>
    <p class="message">Redirecting to login page...</p>
</body>
</html>