<?php
// Target destination
$redirectUrl = "../NSBM-NewsDesk/pages/home.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSBM NewsDesk</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8faf8;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }

        .loader-container {
            text-align: center;
        }

        .logo {
            font-size: 34px;
            font-weight: 800;
            color: #1B5E20;
            letter-spacing: -1px;
        }

        .logo span {
            color: #333;
        }

        .subtitle {
            margin-top: 10px;
            font-size: 13px;
            color: #777;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .loader {
            width: 55px;
            height: 55px;
            border: 4px solid #dcebdc;
            border-top: 4px solid #1B5E20;
            border-radius: 50%;
            margin: 35px auto 20px;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-text {
            color: #555;
            font-size: 13px;
        }

        .progress {
            width: 180px;
            height: 4px;
            background: #e5eae5;
            margin: 15px auto;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background: #1B5E20;
            animation: load 2s ease forwards;
        }

        @keyframes load {
            to { width: 100%; }
        }

        .percentage {
            font-size: 12px;
            color: #1B5E20;
        }

        body.fade-out {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
</head>

<body>

    <div class="loader-container">
        <div class="logo">
            NSBM<span> NewsDesk</span>
        </div>

        <div class="subtitle">University News Portal</div>

        <div class="loader"></div>

        <div class="loading-text">
            Loading NewsDesk...
        </div>

        <div class="progress">
            <div class="progress-bar"></div>
        </div>

        <div class="percentage" id="percentage">0%</div>
    </div>

    <script>
        let progress = 0;
        const percentage = document.getElementById("percentage");

        const interval = setInterval(() => {
            progress += 2;

            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);

                setTimeout(() => {
                    document.body.classList.add("fade-out");

                    setTimeout(() => {
                        window.location.href = "<?php echo $redirectUrl; ?>";
                    }, 500);

                }, 200);
            }

            percentage.textContent = progress + "%";

        }, 40);
    </script>

</body>
</html>
