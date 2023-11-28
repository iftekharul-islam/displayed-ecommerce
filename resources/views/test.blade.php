<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Making Money Made Easy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }

        ​ #message {
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9Y6ZH4TCM3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());​
        gtag('config', 'G-9Y6ZH4TCM3');
    </script>
</head>

<body>
    <div id="message">Verifying you are a human......</div>
    ​
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Simulate an API call (replace this with your actual API endpoint)
            var apiEndpoint = 'https://api.lotto60.com/api/v1/auth/GeoLocation'; // Example API endpoint
            ​
            // Make an AJAX request
            var xhr = new XMLHttpRequest();
            xhr.open('GET', apiEndpoint, true);​
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    var connecting_country = JSON.parse(xhr.response).result.country;
                    // Request was successful, redirect to www.lotto60.com
                    if (connecting_country == 'BR') {
                        window.location.href = "https://joya.casino/?promo=brazilmm";
                    } else if (connecting_country == 'US') {
                        window.location.href = "https://tickets.love";
                    } else {
                        window.location.href = "https://www.lotto60.com";
                    }
                } else {
                    // Request failed, handle error
                    console.error('API request failed:', xhr.statusText);
                    document.getElementById('message').innerHTML = 'Failed to load. Please try again.';
                }
            };​
            xhr.onerror = function() {
                // Handle network errors
                console.error('Network error during API request');
                document.getElementById('message').innerHTML =
                    'Failed to load. Please check your network connection.';
            };​
            // Send the request
            xhr.send();
        });
    </script>
</body>

</html>
