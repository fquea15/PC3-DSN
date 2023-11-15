<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
            background-color: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        h2 {
            color: #555;
        }

        nav {
            margin: 20px 0;
            background-color: #333;
            padding: 10px;
            border-radius: 5px;
        }

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        nav a.active {
            text-decoration: underline;
            font-weight: bolder;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .chart-container {
            width: 80%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
        }

        #home,
        #ratings,
        #distribution,
        #top-users {
            display: none;
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #logo {
            cursor: pointer;
        }

        #home img {
            width: 100%;
            max-width: 600px;
            height: auto;
            border-radius: 10px;
        }

        #home p {
            color: #777;
        }
    </style>

    <!-- Agrega la referencia a Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <h1>Ratings API Results</h1>

    <nav>
        <a href="#home" class="active">Home</a>
        <a href="#ratings">Ratings</a>
        <a href="#distribution">Distribution</a>
        <a href="#top-users">Top Users</a>
    </nav>

    <div id="home">
        <h2>Welcome to the Ratings API Visualization Page</h2>
        <p>Explore the different views using the navigation above. This page provides visualizations for ratings data obtained from the API.</p>
    </div>

    <?php
    // Obtener datos de la API
    $api_endpoint = getenv('API_ENDPOINT');
    $api_endpointss = "http://localhost:5000/api/";
    $api_data = file_get_contents($api_endpoint . 'ratings');

    // Decodificar los datos JSON
    $ratings = json_decode($api_data, true);

    // Verificar si se obtuvieron datos
    if ($ratings) {
        // Mostrar los datos en una tabla
        echo '<div id="ratings" style="display: none;">';
        echo '<h2>Ratings Table</h2>';
        echo '<table>';
        echo '<tr><th>Movie ID</th><th>User ID</th><th>Rating</th><th>Timestamp</th></tr>';

        foreach ($ratings as $rating) {
            echo '<tr>';
            echo '<td>' . $rating['movieId'] . '</td>';
            echo '<td>' . $rating['userId'] . '</td>';
            echo '<td>' . $rating['rating'] . '</td>';
            echo '<td>' . date("Y-m-d H:i:s", $rating['timestamp']) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';

        // Mostrar el gráfico de distribución de ratings
        echo '<div id="distribution" class="chart-container" style="display: none;">';
        echo '<h2>Ratings Distribution</h2>';
        echo '<canvas id="ratings-distribution"></canvas>';
        echo '</div>';

        // Mostrar el gráfico de distribución de calificaciones por usuario
        echo '<div id="user-distribution" class="chart-container" style="display: none;">';
        echo '<h2>User Ratings Distribution</h2>';
        echo '</div>';

        // Obtener datos de Top Users desde la API
        $top_users_api_endpoint = $api_endpoint . 'top-users';
        $top_users_data = file_get_contents($top_users_api_endpoint);
        $top_users = json_decode($top_users_data, true);

        // Verificar si se obtuvieron datos
        if ($top_users) {
            echo '<div id="top-users" style="display: none;">';
            echo '<h2>Top Users</h2>';
            echo '<table>';
            echo '<tr><th>User ID</th><th>Ratings</th></tr>';

            foreach ($top_users as $user) {
                echo '<tr>';
                echo '<td>' . $user['userId'] . '</td>';
                echo '<td>' . $user['rating'] . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</div>';
        } else {
            // Mostrar un mensaje si no se pudieron obtener datos
            echo '<p>No se pudieron obtener datos de Top Users.</p>';
        }
    } else {
        // Mostrar un mensaje si no se pudieron obtener datos
        echo '<p>No se pudieron obtener datos de la API.</p>';
    }
    ?>

    <!-- Script para obtener y mostrar la distribución de ratings -->
    <script>
        fetch('<?php echo $api_endpointss; ?>ratings/distribution')
            .then(response => response.json())
            .then(data => {
                // Crea el gráfico de barras usando Chart.js
                var ctx = document.getElementById('ratings-distribution').getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data),
                        datasets: [{
                            label: 'Count',
                            data: Object.values(data),
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            x: { type: 'linear', position: 'bottom' },
                            y: { beginAtZero: true }
                        }
                    }
                });
            })
            .catch(error => console.error('Error:', error));
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Manejar la navegación
            var navLinks = document.querySelectorAll('nav a');
            var contentDivs = document.querySelectorAll('div[id]');

            navLinks.forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    var targetId = this.getAttribute('href').substring(1);

                    // Buscar el elemento por ID
                    var targetElement = document.getElementById(targetId);

                    // Verificar si se encontró el elemento antes de intentar acceder a sus propiedades
                    if (targetElement) {
                        // Ocultar todos los divs
                        contentDivs.forEach(function (div) {
                            div.style.display = 'none';
                        });

                        // Mostrar el div correspondiente al enlace seleccionado
                        targetElement.style.display = 'block';
                    } else {
                        console.error('Elemento no encontrado con ID: ' + targetId);
                    }

                    // Agregar la clase "active" al enlace seleccionado y quitarla de los demás
                    navLinks.forEach(function (navLink) {
                        navLink.classList.remove('active');
                    });
                    link.classList.add('active');
                });
            });
        });

        // Función para mostrar la sección correspondiente al hacer clic en el logo
        function showSection(sectionId) {
            var contentDivs = document.querySelectorAll('div[id]');
            // Ocultar todos los divs
            contentDivs.forEach(function (div) {
                div.style.display = 'none';
            });
            // Mostrar la sección correspondiente al logo
            var targetElement = document.getElementById(sectionId);
            if (targetElement) {
                targetElement.style.display = 'block';
            } else {
                console.error('Elemento no encontrado con ID: ' + sectionId);
            }
            // Quitar la clase "active" de todos los enlaces
            navLinks.forEach(function (navLink) {
                navLink.classList.remove('active');
            });
            // Agregar la clase "active" al enlace "Home"
            document.querySelector('a[href="#home"]').classList.add('active');
        }
    </script>

</body>

</html>
