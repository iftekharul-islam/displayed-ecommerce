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

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-WPJG32QJ');
    </script>
    <!-- End Google Tag Manager -->

</head>

<body>
    <div id="message">Verifying you are a human......</div>
    ​<!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WPJG32QJ" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
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
