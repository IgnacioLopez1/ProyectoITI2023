<?php

require_once '../config/conexion.php';

class Pedido_Encarga_Menu {
    private $con;

    public function __construct() {
        $db = new DataBase();
        $this->con = $db->conectar();
    }

    // Método para insertar un nuevo registro en la tabla Pedido_Encarga_Menu
    public function insertarPedidoMenu($idMenu, $idPedido, $cantidad, $descripcion) {
        $query = "INSERT INTO Pedido_Encarga_Menu (IDMenu, IDPedido, Cantidad, Descripcion) VALUES (:IDMenu, :IDPedido, :Cantidad, :Descripcion)";

        // Preparar la consulta
        $stmt = $this->con->prepare($query);

        // Bind de los parámetros
        $stmt->bindParam(":IDMenu", $idMenu);
        $stmt->bindParam(":IDPedido", $idPedido);
        $stmt->bindParam(":Cantidad", $cantidad);
        $stmt->bindParam(":Descripcion", $descripcion);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function listadoPedidos() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['botonAceptar'])) {
                $IDPedido = $_POST['botonAceptar'];
                $this->cambiarEstado($IDPedido, 'Enviado');
            }
        }
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['botonDesechar'])) {
                $IDPedido = $_POST['botonDesechar'];
                $this->cambiarEstado($IDPedido, 'Rechazado');
            }
        }
        
        $pedido = $this->con->prepare("SELECT pem.IDMenu, pem.IDPedido, pem.Cantidad, pem.Descripcion, m.Nombre AS NombreMenu FROM pedido_encarga_menu AS pem INNER JOIN estado_pedido AS ep ON pem.IDPedido = ep.ID INNER JOIN menu AS m ON pem.IDMenu = m.ID WHERE ep.Estado = 'Solicitado';");
        $pedido->execute();
        $resultado = $pedido->fetchAll(PDO::FETCH_ASSOC);

        $pedido_array = [];
      
        foreach ($resultado as $row) {
            $nombre = $row['NombreMenu'];
            $IDMenu = $row['IDMenu'];
            $IDPedido = $row['IDPedido'];
            $Cantidad = $row['Cantidad'];
            $Descripcion = $row['Descripcion'];

            if (!in_array($pedido, $pedido_array)) {
                echo '<form method="POST">';
                echo '<tr>';
                echo '<th >'.$nombre .'</th> ';   
                echo '<th >'.$IDMenu .'</th> ';   
                echo '<th >'.$IDPedido.'</th> ';
                echo '<th >'.$Descripcion.'</th> ';   
                echo '<th >'.$Cantidad.'</th> '; 
                echo '<th><button class="botonAceptar" name="botonAceptar" value="'.$IDPedido.'">Completar</button></th>';
                echo '<th><button class="botonDesechar" name="botonDesechar" value="'.$IDPedido.'">Desechar</button></th>';
                echo '</tr>';
                echo '</form>';

            }
        }
    }

    public function controlPedidos($estadoSeleccionado = 'todos'){
        $control = $this->con->prepare("SELECT t1.ID, ep.Fecha AS FechaEstado, t1.IDCliente, ep.Estado FROM pedido AS t1 INNER JOIN estado_pedido AS ep ON t1.ID = ep.ID WHERE ep.Estado != 'Entregado' AND ep.Estado != 'Rechazado';");
        $control->execute();
        $resultado = $control->fetchAll(PDO::FETCH_ASSOC);
        
        $control_array=[];
    
        echo '<form method="POST">';
        echo '<select class="tablaArriba" name="estado" id="estado">';
        echo '<option value="todos">Todos los pedidos</option>';
        echo '<option value="solicitado">Solicitado</option>';
        echo '<option value="confirmado">Confirmado</option>';
        echo '<option value="enviado">Enviado</option>';
        echo '<option value="entregado">Entregado</option>';
        echo '<option value="rechazado">Rechazado</option>';
        echo '</select>';
        echo '<input type="submit" value="Filtrar">';
        echo '</form';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $estadoSeleccionado = isset($_POST['estado']) ? $_POST['estado'] : ""; 
        }
    
        foreach ($resultado as $row) {
            $IDCliente = $row['IDCliente'];
            $IDPedido = $row['ID'];
            $fecha = $row['FechaEstado'];
            $estado = strtolower($row['Estado']);   
    
            if ($estadoSeleccionado == 'todos' || $estado == $estadoSeleccionado) {
                echo '<tr>';
                echo '<th>'.$IDCliente.'</th>';
                echo '<th>'.$IDPedido.'</th>';
                echo '<th>'.$fecha.'</th>';
                echo '<th>'.$estado.'</th>';
                echo '</tr>';
            }
        }
    }
    
    public function cambiarEstado($IDPedido, $nuevoEstado) {
        date_default_timezone_set('America/Montevideo');
        $fecha = date("Y-m-d H:i:s");
    
        $sql = "UPDATE estado_pedido SET Estado = :nuevoEstado, Fecha = :fechaCambio WHERE ID = :IDPedido";
        $stmt = $this->con->prepare($sql);
        $stmt->bindParam(':nuevoEstado', $nuevoEstado, PDO::PARAM_STR);
        $stmt->bindParam(':fechaCambio', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':IDPedido', $IDPedido, PDO::PARAM_INT);
        $stmt->execute();
    }
    

}