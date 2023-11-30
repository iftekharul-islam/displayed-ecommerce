<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choto.xyz</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .bgimg {
            background-image: url('https://www.w3schools.com/w3images/forestbridge.jpg');
            height: 100%;
            background-position: center;
            background-size: cover;
            position: relative;
            color: white;
            font-family: "Courier New", Courier, monospace;
            font-size: 25px;
        }

        .topleft {
            position: absolute;
            top: 0;
            left: 16px;
        }

        .bottomleft {
            position: absolute;
            bottom: 0;
            left: 16px;
        }

        .middle {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }

        hr {
            margin: auto;
            width: 40%;
        }
    </style>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-WH8TXT85');
    </script>
    <!-- End Google Tag Manager -->
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WH8TXT85"
                height="0" width="0" style="display:none;visibility:hidden">
        </iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="bgimg">
        <div class="topleft">
            <p>CHOTO.XYZ</p>
        </div>
        <div class="middle">
            <h1>YOR ARE HOLD ON</h1>
            <hr>
            <p>Hk01.top</p>
        </div>
        <div class="bottomleft">
            <p>{{ \Carbon\Carbon::today()->format('Y M d') }}</p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Simulate an API call (replace this with your actual API endpoint)
            setTimeout(function() {
                window.location.href = '{{ $url }}';
            }, 3000);
        });
    </script>
</body>
</html>
