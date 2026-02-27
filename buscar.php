<?php
include 'db.php';

$q = $_GET['q'] ?? '';
$q = $conexion->real_escape_string($q);

if ($q !== '') {
    $sql = "SELECT e.*, c.nombre AS categoria 
            FROM empresas e
            JOIN categorias c ON e.id_categoria = c.id_categoria
            WHERE e.nombre LIKE '%$q%' 
            LIMIT 10";

    $resultado = $conexion->query($sql);

    if($resultado && $resultado->num_rows > 0) {
        while($fila = $resultado->fetch_assoc()) {
            echo "<a href='empresas.php?empresa={$fila['id_empresa']}' style='text-decoration:none; display:block; padding:5px 0;'>
                    {$fila['nombre']} ({$fila['categoria']})
                  </a>";
        }
    } else {
        echo "<p>No se encontraron empresas.</p>";
    }
}
?>