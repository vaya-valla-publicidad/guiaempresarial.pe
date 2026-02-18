<?php
include 'db.php';

if (!empty($_GET['q'])) {
    $buscar = $conexion->real_escape_string($_GET['q']);
    $sql = "SELECT e.id_empresa, e.nombre
            FROM empresas e
            JOIN categorias c ON e.id_categoria = c.id_categoria
            WHERE e.nombre LIKE '%$buscar%' 
               OR c.nombre LIKE '%$buscar%'";

    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        echo "<ul>";
        while($fila = $resultado->fetch_assoc()) {
            echo "<li>
                    <a href='empresas.php?id_empresa={$fila['id_empresa']}'>
                        {$fila['nombre']}
                    </a>
                  </li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='text-align:center; color:#D32F2F;'>No se encontraron resultados</p>";
    }
}
?>
