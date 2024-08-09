<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "jamur");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$sql = "SELECT temperature, humidity FROM sensor_readings ORDER BY reading_time DESC LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc() ?? [];

$temperature = $row['temperature'] ?? "N/A";
$humidity = $row['humidity'] ?? "N/A";
$diffuser_status = $humidity !== "N/A" && $humidity < 70 ? "Hidup" : "Mati";
$status = $humidity !== "N/A" && $humidity < 70 ? "Kumbung Tidak Normal" : "Kumbung Normal";

$sql_all = "SELECT reading_time, temperature, humidity FROM sensor_readings ORDER BY id DESC";
$result_all = $conn->query($sql_all);
$data = $result_all->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Suhu dan Kelembapan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <h2>Monitoring</h2>
        <ul>
            <li><a href="#home"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="#grafik"><i class="fas fa-chart-line"></i> Grafik</a></li>
            <li><a href="#tabel"><i class="fas fa-table"></i> Tabel</a></li>
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <section id="home">
            <h1>Monitoring Suhu dan Kelembapan</h1>
            <div class="cards-container">
                <div class="cards-row">
                    <div class="card temperature"><h2><i class="fas fa-thermometer-half"></i> Suhu</h2><p id="temperature"><?= htmlspecialchars($temperature) ?> °C</p></div>
                    <div class="card humidity"><h2><i class="fas fa-tint"></i> Kelembapan</h2><p id="humidity"><?= htmlspecialchars($humidity) ?> %</p></div>
                </div>
                <div class="cards-row">
                    <div class="card diffuser-status"><h2><i class="fas fa-toggle-on"></i> Status Difuser</h2><p id="status-difuser"><?= htmlspecialchars($diffuser_status) ?></p></div>
                    <div class="card status"><h2><i class="fas fa-info-circle"></i> Status Kumbung</h2><p id="status"><?= htmlspecialchars($status) ?></p></div>
                </div>
            </div>
        </section>

        <section id="grafik">
            <h1>Grafik</h1>
            <canvas id="myChart"></canvas>
        </section>

        <section id="tabel">
            <h1>Tabel Data</h1>
            <table>
                <thead>
                    <tr><th>Waktu</th><th>Suhu (&deg;C)</th><th>Kelembapan (%)</th><th>Status Difuser</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['reading_time']) ?></td>
                        <td><?= htmlspecialchars($row['temperature']) ?></td>
                        <td><?= htmlspecialchars($row['humidity']) ?></td>
                        <td><?= $row['humidity'] < 70 ? "Hidup" : "Mati" ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        $(document).ready(function() {
            function fetchData() {
                $.getJSON('fetch_latest_data.php', function(data) {
                    $('#temperature').text(data.temperature + ' °C');
                    $('#humidity').text(data.humidity + ' %');
                    $('#status-difuser').text(data.diffuser_status);
                    $('#status').text(data.status);
                });
            }

            fetchData();
            setInterval(fetchData, 5000);

            var ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?= json_encode(array_column($data, 'reading_time')) ?>,
                    datasets: [
                        {
                            label: 'Suhu (°C)',
                            data: <?= json_encode(array_column($data, 'temperature')) ?>,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1,
                            fill: false
                        },
                        {
                            label: 'Kelembapan (%)',
                            data: <?= json_encode(array_column($data, 'humidity')) ?>,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            fill: false
                        }
                    ]
                },
                options: {
                    scales: {
                        x: { display: true, title: { display: true, text: 'Waktu' } },
                        y: { display: true, title: { display: true, text: 'Nilai' } }
                    }
                }
            });
        });
    </script>
</body>
</html>
