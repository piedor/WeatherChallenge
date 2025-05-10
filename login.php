<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accesso - Stazione Meteo</title>
        <meta name="description" content="WebApp previsioni meteo">
        <meta name="author" content="Pietro Dorighi">
        <link href="./favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon">
        <?php require_once './utils/style.php'; ?>
        <link rel="stylesheet" href="./assets/css/style_login.css">
        <link rel="stylesheet" href="./assets/css/style.css">
    </head>
    <body>

        <div class="limiter">
            <div class="container-login100" style="background-image: url('assets/img/liceo1.jpg');">
                <div class="wrap-login100">
                    <form class="login100-form validate-form" method="post" action="google_login.php">
                        <span class="login100-form-logo">
                            <a href="https://liceodavincitn.it/"><img src="assets\img\Logo-Liceo.jpg" alt="logo stazione" width="100%"/></a>
                        </span>

                        <h1 id="weather-title" style="font-family: 'Raleway', sans-serif; 
                                                            text-align: center; 
                                                            font-weight: bold; 
                                                            color:rgb(100, 173, 197);">
                            WeatherChallenge
                        </h1>

                        <div class="text-center p-t-27">
                            <div class="txt1">
                                Effettua l'accesso con il tuo account istituzionale per iniziare.
                            </div>
                        </div>

                        <div class="text-center pt-3">
                            <button class="google-btn button">
                                <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google Logo">
                                <span>Continua con Google</span>
                            </button>
                        </div>                        
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>