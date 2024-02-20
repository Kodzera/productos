<?php

$conn = new mysqli("localhost", "root", "", "redinco");


if ($conn->connect_error) {
    die("Error de conexión" . $conn->connect_error);
}
