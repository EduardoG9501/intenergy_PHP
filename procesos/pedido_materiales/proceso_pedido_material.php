<?php
header("Content-Type: application/json; charset=utf-8");
error_reporting(E_ALL);
ini_set("display_errors", 0);

try {
    $result = array();

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $result["debug"] = "session_ok";

    require_once "../../clases/Conexion.php";
    $result["debug2"] = "conexion_ok";

    require_once "../../clases/PedidoMateriales.php";
    $result["debug3"] = "pedido_ok";

    $action = isset($_GET["action"]) ? $_GET["action"] : "";
    $pedidoMateriales = new PedidoMateriales();
    $result["debug4"] = "action_" . $action;

    if ($action == "articulos") {
        echo json_encode($pedidoMateriales->articulos());
    } elseif ($action == "listar") {
        $idOrden = isset($_GET["id_orden"]) ? intval($_GET["id_orden"]) : null;
        $idBodegaSesion = isset($_SESSION["bodega_seleccionada"]) ? intval($_SESSION["bodega_seleccionada"]) : null;
        echo json_encode($pedidoMateriales->listar($idOrden, $idBodegaSesion));
    } elseif ($action == "obtener") {
        $id = $_GET["id_pedido_material"];
        echo json_encode($pedidoMateriales->obtener($id));
    } elseif ($action == "estados") {
        echo json_encode($pedidoMateriales->estados());
    } elseif ($action == "ordenes") {
        $idBodegaSesion = isset($_SESSION["bodega_seleccionada"]) ? intval($_SESSION["bodega_seleccionada"]) : null;
        $idOrdenEspecifica = isset($_GET["id_orden"]) ? intval($_GET["id_orden"]) : null;
        echo json_encode($pedidoMateriales->ordenes($idBodegaSesion, $idOrdenEspecifica));
    } elseif ($action == "listar_detalle") {
        $id = $_GET["id_pedido_material"];
        echo json_encode($pedidoMateriales->listarDetalle($id));
    } elseif ($action == "guardar_detalle") {
        $result = $pedidoMateriales->guardarDetalle($_POST);
        echo json_encode($result);
    } elseif ($action == "eliminar_detalle") {
        $id = $_POST["id_pedido_material_detalle"];
        $result = $pedidoMateriales->eliminarDetalle($id);
        echo json_encode($result);
    } elseif ($action == "get_stock_producto_bodega") {
        $id_producto = isset($_GET["id_producto"]) ? $_GET["id_producto"] : null;
        $id_bodega = isset($_SESSION["bodega_seleccionada"]) ? $_SESSION["bodega_seleccionada"] : null;
        $stock = 0;
        if ($id_producto && $id_bodega) {
            $stock = $pedidoMateriales->getStockProductoBodega($id_producto, $id_bodega);
        }
        echo json_encode(array("stock" => $stock));
    } elseif ($action == "cerrar_pedido") {
        $id = isset($_POST["id_pedido_material"]) ? $_POST["id_pedido_material"] : (isset($_GET["id_pedido_material"]) ? $_GET["id_pedido_material"] : null);
        if ($id) {
            $result2 = $pedidoMateriales->cerrarPedido($id);
            echo json_encode($result2);
        } else {
            echo json_encode(array("success" => false, "mensaje" => "ID de pedido no proporcionado"));
        }
    } elseif ($action == "guardar") {
        $idOrden = isset($_POST["id_orden"]) ? intval($_POST["id_orden"]) : 0;
        $idBodegaSesion = isset($_SESSION["bodega_seleccionada"]) ? intval($_SESSION["bodega_seleccionada"]) : 0;
        if ($idOrden <= 0 || $idBodegaSesion <= 0) {
            echo json_encode(array("id_pedido_material" => 0, "success" => false, "mensaje" => "Debe tener una bodega activa."));
            exit;
        }
        $c = new conectar();
        $conexion = $c->conexion();
        $sql = "SELECT COUNT(*) AS total FROM ordenes_trabajo WHERE id_orden = $idOrden AND id_bodega_principal = $idBodegaSesion AND estado = 1";
        $res = mysqli_query($conexion, $sql);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        $ok = $row ? intval($row["total"]) > 0 : false;
        if (!$ok) {
            echo json_encode(array("id_pedido_material" => 0, "success" => false, "mensaje" => "La orden no pertenece a la bodega activa."));
            exit;
        }
        $id = $pedidoMateriales->guardar($_POST);
        echo json_encode(array("id_pedido_material" => $id, "success" => $id ? true : false, "mensaje" => $id ? "Pedido guardado" : "Error al guardar"));
    } else {
        echo json_encode(array("success" => false, "mensaje" => "Accion no reconocida: " . $action));
    }
} catch (Exception $e) {
    echo json_encode(array("error" => $e->getMessage()));
} catch (Error $e) {
    echo json_encode(array("error" => $e->getMessage()));
}

