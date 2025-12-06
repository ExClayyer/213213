<?php
require_once 'config.php';

$country_id = $_GET['country_id'] ?? 0;
$cities = get_cities_by_country($country_id);

foreach ($cities as $city) {
    echo '<option value="' . $city['id'] . '">' . htmlspecialchars($city['name']) . '</option>';
}