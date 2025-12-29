<?php
session_start();
require_once "../../clases/Conexion.php"; 
require_once "../../clases/SolicitudMaterial.php";
require_once "../../clases/SolicitudMaterialDetalle.php";

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

$solicitudMaterial = new SolicitudMaterial();
$solicitudMaterialDetalle = new SolicitudMaterialDetalle();

switch ($action) {
    case 'listar':
        // FILTRO POR BODEGA SELECCIONADA
        $id_bodega = isset($_SESSION['bodega_seleccionada']) ? $_SESSION['bodega_seleccionada'] : null;
        echo json_encode($solicitudMaterial->listar($id_bodega));
        break;
    case 'obtener':
        $id = $_GET['id_solicitud_material'];
        echo json_encode($solicitudMaterial->obtener($id));
        break;
    case 'guardar':
        $id = $_POST['id_solicitud_material'] ?? null;
        $id_orden = $_POST['id_orden'];

        // Validar que NO existe otra solicitud activa para la misma orden
        if ($solicitudMaterial->existeSolicitudPorOrden($id_orden, $id)) {
            echo json_encode([
                'id_solicitud_material' => null,
                'success' => false,
                'mensaje' => 'Ya existe una solicitud para esta orden de trabajo.'
            ]);
            exit;
        }

        //Si solicitud ya existes
        if ($id > 0)
        {
          // Si se quiere cambiar a ENTREGADO (3), validar ANTES de guardar
          if (isset($_POST['id_estado_solicitud']) && $_POST['id_estado_solicitud'] == '3') {
            $conexion = (new conectar())->conexion();
            $id_bodega = $_SESSION['bodega_seleccionada'] ?? null;

            // Buscar nombre de la bodega activa
            $sqlBod = "SELECT nombrebodega FROM bodegas WHERE id_bodega = $id_bodega";
            $resBod = mysqli_query($conexion, $sqlBod);
            $rowBod = mysqli_fetch_assoc($resBod);
            $nombreBodega = $rowBod ? $rowBod['nombrebodega'] : 'Bodega desconocida';
 
            // Traer artículos COMPLETADO de la solicitud (id_detalle_articulo = 2)
            $sql_det = "SELECT sd.id_producto, a.nombre, sd.cantidad
                        FROM solicitud_materiales_detalle sd
                        JOIN articulos a ON a.id_producto = sd.id_producto
                        WHERE sd.id_solicitud_material = $id AND sd.id_detalle_articulo = 2";
            $res_det = mysqli_query($conexion, $sql_det);

            $articulos_faltantes = [];
            $articulos_completados = [];
            while($art = mysqli_fetch_assoc($res_det)) {
                $id_articulo = $art['id_producto'];
                $nombre = $art['nombre'];
                $cantidad = $art['cantidad'];
                $articulos_completados[] = $nombre;
                // Busca stock disponible en bodega activa
                $sql_stock = "SELECT cantidad FROM stock_productos_bodega WHERE id_bodega_principal = $id_bodega AND id_producto = $id_articulo";
                $res_stock = mysqli_query($conexion, $sql_stock);
                $row_stock = mysqli_fetch_assoc($res_stock);
                $stock = $row_stock ? floatval($row_stock['cantidad']) : 0;
                if ($stock < $cantidad) {
                    $articulos_faltantes[] = $nombre;
                }
            }
            // Si no hay artículos COMPLETADO, NO permite marcar como entregado
            if (count($articulos_completados) == 0) {
                echo json_encode([
                    'id_solicitud_material' => $id,
                    'success' => false,
                    'mensaje' => "La solicitud no se puede guardar como ENTREGADO porque no tiene ningún artículo marcado como COMPLETADO para descargar."
                ]);
                exit;
            }
            // Si hay artículos sin stock suficiente, NO permite marcar como entregado
            if (count($articulos_faltantes) > 0) {
                echo json_encode([
                    'id_solicitud_material' => $id,
                    'success' => false,
                    'mensaje' => "La solicitud no se puede marcar como ENTREGADO. Los siguientes artículos no tienen disponibilidad en la bodega $nombreBodega: " . implode(', ', $articulos_faltantes)
                ]);
                exit;
            }
          }

        //Editar SOlicitud
         // Si se acaba de marcar como ENTREGADO (id_estado_solicitud = 3), genera movimiento
        if (isset($_POST['id_estado_solicitud']) && $_POST['id_estado_solicitud'] == '3') {
            $conexion = (new conectar())->conexion();

            // 1. Obtén la solicitud
            $sqlSolicitud = "SELECT * FROM solicitud_materiales WHERE id_solicitud_material = $id";
            $datosSolicitud = mysqli_fetch_assoc(mysqli_query($conexion, $sqlSolicitud));

            // 2. Crea el movimiento de salida, subtipo DESCARGO
            $id_tipo_movimiento = 2; // SALIDA
            $id_sub_tipo_movimiento = 3; // DESCARGO
            $id_bodega = $datosSolicitud['id_bodega_principal'];
            $no_documento = 'SOL-'.$id;
            $fechaDocumento = date('Y-m-d');
            $observacion = 'MOVIMIENTO AUTOMATICO DE SOLICITUD DE MATERIAL #'.$id;

        // 1. Obtener el username de la sesión
        $username = $_SESSION['usuario'] ?? null;
        $id_usuario = null;

        // 2. Buscar el id_usuario en la base de datos
       if ($username) {
          $sqlGetUser = "SELECT id_usuario FROM usuarios WHERE email = '$username' LIMIT 1";
         $resUser = mysqli_query($conexion, $sqlGetUser);
        if ($rowUser = mysqli_fetch_assoc($resUser)) {
            $id_usuario = intval($rowUser['id_usuario']);
         }
         }

      // 3. Solo continuar si encontró el usuario
      if (!$id_usuario) {
       echo json_encode([
           'id_solicitud_material' => $id,
           'success' => false,
            'mensaje' => 'No se pudo asociar el usuario al movimiento. Contacte a soporte.'
        ]);
         exit;
        }

        // 4. Insertar MOVIMIENTOS
        $sql_mov = "INSERT INTO movimientos 
        (id_tipo_movimiento, id_sub_tipo_movimiento, id_bodega, no_documento, fechaDocumento, observacion, estado, guardarDefinitivo, id_usuario)
        VALUES ($id_tipo_movimiento, $id_sub_tipo_movimiento, $id_bodega, '$no_documento', '$fechaDocumento', '$observacion', 1, 1, $id_usuario)";
         mysqli_query($conexion, $sql_mov);
         $id_movimiento = mysqli_insert_id($conexion);

        if ($sql_mov > 0)
         {

           // 5. Agrega los artículos COMPLETADO al detalle del movimiento
           $sql_det = "SELECT sd.*, a.precio 
                        FROM solicitud_materiales_detalle sd
                        JOIN articulos a ON a.id_producto = sd.id_producto
                        WHERE sd.id_solicitud_material = $id AND sd.id_detalle_articulo = 2";
            $res_det = mysqli_query($conexion, $sql_det);

            /*Insertamos detalle de movimiento*/
            while($articulo = mysqli_fetch_assoc($res_det)) {
                $id_articulo = $articulo['id_producto'];
                $cantidad = $articulo['cantidad'];
                $precio = floatval($articulo['precio']);
                $subtotal = $cantidad * $precio;
                $total = $subtotal;
                $id_bodega_lugar = $id_bodega;

                 $sql_mov_det = "INSERT INTO movimientos_detalle (id_movimiento, id_articulo, id_bodega_lugar, precio, cantidad, lote, bonificacion, subtotal, decuento, iva, total, iva_porc, desc_porc, estado) 
                 VALUES ( $id_movimiento, $id_articulo, $id_bodega_lugar, $precio, $cantidad, NULL, NULL, $subtotal, 0, 0, $total, 0, 0, 1)";
                 $result  = mysqli_query($conexion, $sql_mov_det); 
		   }
            /*Validamos que el insert al detalle del producto se hizo con existo*/
			if ($result)
			{
                $fecha=date('Y-m-d');
				/*2. Actualizamos los sotosk de los productos*/ 
			    $sql2="INSERT into stock_productos(
			                                    id_movimiento,
				                                id_tipo_movimiento ,
				                                id_sub_tipo_movimiento , 
			                                	no_documento,
			                                	id_bodega_principal,
				                                id_bodega_secundaria, 
				                                tipo_movimiento ,
				                                sub_tipo_movimiento ,
                                                id_movimiento_detalle, 
                                                id_producto,
                                                producto,
                                                lote,
				                                precio,
				                                cantidad,
                                                subtotal,
                                                decuento,
                                                iva,
                                                total, 
                                                stock,
				                                id_stock_credito,
												id_usuario,
				                                fecha_captura
			                                 ) 
				        SELECT DISTINCT  det.id_movimiento,
                                mov.id_tipo_movimiento,
                                mov.id_sub_tipo_movimiento,
                                mov.no_documento,
                                mov.id_bodega idBodegaPrincipal,
								det.id_bodega_lugar IdBodegaSecundaria,
                                tm.tipo TipoMovimiento,
                                stm.sub_tipo SubTipoMovimiento,
                                det.id_movimiento_detalle,
	                            art.id_producto,
	                            art.nombre,
				                det.lote,
	                            det.precio,
				                det.cantidad,
				                det.subtotal,
							    det.decuento,
								det.iva,
				                det.total, 
								det.cantidad stock,
								0 id_stock_credito,
                               mov.id_usuario,
							   '$fecha' fecha_captura
		               from movimientos_detalle as det
                       inner join movimientos mov on det.id_movimiento = mov.id_movimiento
                       inner join tipo_movimiento tm on mov.id_tipo_movimiento = tm.id_tipo_movimiento
                       inner join sub_tipo_movimiento stm on mov.id_sub_tipo_movimiento = stm.id_sub_tipo_movimiento
		               inner join articulos art on det.id_articulo= art.id_producto
                       inner join bodegas bod on  det.id_bodega_lugar= bod.id_bodega
		               where det.id_movimiento='$id_movimiento'";
			          $resultadoStock =  mysqli_query($conexion,$sql2); 
            /*Validamos que se inserto el stock del producto*/
			if ($resultadoStock)
            { 
              // Calcular y actualizar subtotal_mto y total_mto del movimiento
              $sql_sum = "SELECT SUM(subtotal) AS subtotal_mto, SUM(total) AS total_mto 
              FROM movimientos_detalle 
              WHERE id_movimiento = $id_movimiento";
              $res_sum = mysqli_query($conexion, $sql_sum);
              $row_sum = mysqli_fetch_assoc($res_sum);
              $subtotal_mto = $row_sum ? floatval($row_sum['subtotal_mto']) : 0;
              $total_mto = $row_sum ? floatval($row_sum['total_mto']) : 0;

              $sql_update_mov = "UPDATE movimientos 
              SET subtotal_mto = $subtotal_mto, total_mto = $total_mto 
              WHERE id_movimiento = $id_movimiento";
              $resultUpdateMovimiento = mysqli_query($conexion, $sql_update_mov);
         
              if ($resultUpdateMovimiento)
              {
                 /*10. Actualizamos la disponbilidad de los articulos*/ 
		          $sql10="CALL actualizar_cantidad_articulos_por_movimiento($id_movimiento )";
                 mysqli_query($conexion,$sql10);
              }
                else{
                 /*6. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql6="DELETE FROM stock_productos   
				 where id_movimiento='$idmovimiento'";
			     mysqli_query($conexion,$sql6);  

                  /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos_detalle   
				 where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5);  

			     /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos   
			     where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5); 
                 
                 echo json_encode([
                  'id_solicitud_material' => $id,
                  'success' => false,
                  'mensaje' => 'Ocurrio un error al intentar guardar los cambios de la solicitud.'
                 ]);
                 exit;
                 } 
              } 
              else{
                
                 /*6. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql6="DELETE FROM stock_productos   
				 where id_movimiento='$idmovimiento'";
			     mysqli_query($conexion,$sql6);  

                  /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos_detalle   
				 where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5);  

			     /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos   
			     where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5); 
                 
                 echo json_encode([
                  'id_solicitud_material' => $id,
                  'success' => false,
                  'mensaje' => 'Ocurrio un error al intentar guardar los cambios de la solicitud.'
                 ]);
                 exit;
              }
           }  
           else{
                 /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos_detalle   
				 where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5);  

			     /*5. Se elimina detalle de producto en caso que se haga rollback*/
			     $sql5="DELETE FROM movimientos   
			     where id_movimiento='$id_movimiento'";
			     mysqli_query($conexion,$sql5);  

                  echo json_encode([
                  'id_solicitud_material' => $id,
                  'success' => false,
                  'mensaje' => 'Ocurrio un error al intentar guardar los cambios de la solicitud.'
                 ]);
                 exit;
           } 
          } 
        } 
        // EXITO: ACTUALIZA LA SOLICITUD
         $id = $solicitudMaterial->guardar($_POST);
        } 
        else{
          // EXOTP: NUEVA SOLICITUD
          $id = $solicitudMaterial->guardar($_POST);
        }
         
        echo json_encode([
            'id_solicitud_material' => $id,
            'success' => true,
            'mensaje' => $id ? 'Solicitud editada correctamente' : 'Solicitud agregada correctamente'
        ]);
        break;
    case 'bodegas':
        echo json_encode($solicitudMaterial->bodegas());
        break;
    case 'empleados':
        echo json_encode($solicitudMaterial->empleados());
        break;
    case 'estados':
        echo json_encode($solicitudMaterial->estados());
        break;
    case 'articulos':
        echo json_encode($solicitudMaterial->articulos());
        break;
    case 'listar_detalle':
        $id = $_GET['id_solicitud_material'];
        echo json_encode($solicitudMaterialDetalle->listarPorSolicitud($id));
        break;
    case 'guardar_detalle':
        $result = $solicitudMaterialDetalle->guardar($_POST);
        echo json_encode($result);
        break;
    case 'eliminar_detalle':
        $id = $_POST['id_solicitud_material_detalle'];
        $result = $solicitudMaterialDetalle->eliminar($id);
        echo json_encode($result);
        break;
    case 'buscar_productos':
        $q = $_GET['q'] ?? '';
        echo json_encode($solicitudMaterialDetalle->buscarProductos($q));
        break;
    case 'existe_solicitud':
        $id_orden = $_GET['id_orden'];
        $existe = $solicitudMaterial->existeSolicitudPorOrden($id_orden);
        echo json_encode(['existe' => $existe]);
        break;
    case 'estados_articulo':
        echo json_encode($solicitudMaterialDetalle->estadosArticulo());
        break;
    case 'listarorden':
        $id = $_GET['id_solicitud_material'];
        echo json_encode($solicitudMaterial->listarorden($id));
        break;
    case 'get_stock_producto_bodega':
    $id_producto = $_GET['id_producto'] ?? $_POST['id_producto'] ?? null;
    $id_bodega = $_SESSION['bodega_seleccionada'] ?? null;
    $stock = 0;
    if ($id_producto && $id_bodega) {
        $stock = $solicitudMaterialDetalle->getStockProductoBodega($id_producto, $id_bodega);
    }
    echo json_encode(['stock' => $stock]);
    break;
}
?>