<?php 
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 14400); // 4 horas en segundos
    session_set_cookie_params(14400);         // 4 horas para la cookie de sesión
    session_start();
}
$usuario = $_SESSION['usuario'];
require_once "dependencias.php";  
require_once "../clases/Usuarios.php";
require_once "../clases/Conexion.php"; 

$c = new conectar(); 
$conexion = $c->conexion();
 
// 1. Obtener el id_usuario a partir del email/nombre de usuario
$sqlId = "SELECT id_usuario FROM usuarios WHERE email = '$usuario' and estado = 1 LIMIT 1";
$resId = mysqli_query($conexion, $sqlId);
$id_usuario = null;
if ($row = mysqli_fetch_assoc($resId)) {
    $id_usuario = $row['id_usuario'];
}

// 2. Obtener los id_menu permitidos usando la clase
$menusPermitidos = [];
if ($id_usuario) {
    $usuarioObj = new Usuarios();
    $menusPermitidos = $usuarioObj->obtenerPermisosUsuario($id_usuario); // Retorna array de id_menu
}

// 3. Obtener info completa de menús permitidos (incluyendo iconos)
$menus = [];
if (count($menusPermitidos) > 0) {
    $ids = implode(",", array_map('intval', $menusPermitidos));
    $sqlMenus = "SELECT menu_padre, menu_hijo, opcion, icono_padre, icono_hijo 
                 FROM menu 
                 WHERE id_menu IN ($ids) AND estado = 1 and menu_padre <> 'PERMISOS ESPECIALES'";
    $resMenus = mysqli_query($conexion, $sqlMenus);
    while ($row = mysqli_fetch_assoc($resMenus)) {
        $menus[] = $row;
    }
}

// 4. Agrupar por padre (retorna 'icono_padre' y 'hijos')
function agruparMenusPorPadre($menus) {
    $grupos = [];
    foreach ($menus as $menu) {
        $padre = $menu['menu_padre'];
        if (!isset($grupos[$padre])) {
            $grupos[$padre] = [
                'icono_padre' => $menu['icono_padre'],
                'hijos' => []
            ];
        }
        $grupos[$padre]['hijos'][] = $menu;
    }
    return $grupos;
}

$gruposMenus = agruparMenusPorPadre($menus);

// 5. Obtener bodegas asignadas al usuario (o todas si admin)
$bodegas = [];
if ($id_usuario) {
    if ($usuario === "admin" || $id_usuario == 1) {
        // Mostrar todas las bodegas activas para el sysadmin
        $sqlBodegas = "SELECT id_bodega, nombreBodega FROM bodegas WHERE estado = 1";
    } else {
        $sqlBodegas = "SELECT b.id_bodega, b.nombreBodega
                       FROM bodegas b
                       INNER JOIN permiso_bodega ub ON ub.id_bodega = b.id_bodega
                       WHERE ub.id_usuario = $id_usuario AND b.estado = 1";
    }
    $resBodegas = mysqli_query($conexion, $sqlBodegas);
    while ($row = mysqli_fetch_assoc($resBodegas)) {
        $bodegas[] = $row;
    }
}

// --- 6. Selección de bodega: prioriza localStorage (por JS), luego sesión, luego la primera
$selected_bodega = null;
if (
    isset($_SESSION['bodega_seleccionada']) &&
    in_array($_SESSION['bodega_seleccionada'], array_column($bodegas, 'id_bodega'))
) {
    $selected_bodega = $_SESSION['bodega_seleccionada'];
} elseif (count($bodegas) > 0) {
    $selected_bodega = $bodegas[0]['id_bodega'];
    $_SESSION['bodega_seleccionada'] = $selected_bodega;
}

// 7. Si el usuario cambia la bodega (por POST/AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bodega_seleccionada'])) {
    $_SESSION['bodega_seleccionada'] = $_POST['bodega_seleccionada'];
    exit;
}
?> 
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <?php require_once "dependencias.php"; ?>
</head>
<body> 
  <!-- Begin Navbar -->
  <div id="nav">
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="inicio.php"><img class="img-responsive img-thumbnail" src="../img/InteNergy.jpg" alt="" width="150px" height="150px"></a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right"> 
            <li class="active"><a href="inicio.php"><span class="glyphicon glyphicon-home"></span> Inicio</a></li>
            <?php foreach ($gruposMenus as $padre => $datos): ?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <?php if (!empty($datos['icono_padre'])): ?>
                    <span class="<?php echo $datos['icono_padre']; ?>"></span>
                  <?php endif; ?>
                  <?php echo strtoupper($padre); ?> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                  <?php foreach ($datos['hijos'] as $menu): ?>
                    <li>
                      <a href="<?php echo $menu['opcion']; ?>">
                        <?php if (!empty($menu['icono_hijo'])): ?>
                          <span class="<?php echo $menu['icono_hijo']; ?>"></span>
                        <?php endif; ?>
                        <?php echo $menu['menu_hijo']; ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
            <!-- Usuario, selección de bodega y salir -->
            <li class="dropdown">
              <a href="#" style="color: #ebb268" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon glyphicon-user"></span> Usuario: <?php echo $_SESSION['usuario']; ?><span class="caret"></span>
              </a>
              <ul class="dropdown-menu" style="min-width:220px;">
                <li style="padding:10px;">
                  <?php if (count($bodegas) > 0): ?>
                  <form id="formBodega" method="post" style="margin-bottom:0;">
                    <label for="bodega_seleccionada" style="color:#042f97;">Bodega activa:</label>
                    <select name="bodega_seleccionada" id="bodega_seleccionada" class="form-control input-sm">
                        <?php foreach ($bodegas as $bod): ?>
                        <option value="<?= $bod['id_bodega'] ?>"
                            <?= ($selected_bodega == $bod['id_bodega']) ? 'selected' : '' ?>>
                            <?= $bod['nombreBodega'] ?>

                        </option>
                        <?php endforeach; ?>
                    </select>
                  </form>
                  <script>
                  $(document).ready(function(){
                      // Al cargar, si hay bodega guardada en localStorage y existe en la lista, selecciónala
                      var bodegaLS = localStorage.getItem('bodega_seleccionada');
                      if(bodegaLS && $('#bodega_seleccionada option[value="'+bodegaLS+'"]').length > 0) {
                          $('#bodega_seleccionada').val(bodegaLS);
                      }

                      // Al cambiar el select, guarda en localStorage y en la sesión
                      $('#bodega_seleccionada').change(function(){
                          var idBodega = $(this).val();
                          localStorage.setItem('bodega_seleccionada', idBodega);
                          $.post('', {bodega_seleccionada: idBodega}, function(){
                              location.reload();
                          });
                      });
                  });
                  </script>
                  <?php else: ?>
                    <div style="color:#a94442;">No tiene bodegas asignadas</div>
                  <?php endif; ?>
                </li>
                <li role="separator" class="divider"></li>
                <li><a style="color: red" href="../procesos/salir.php"><span class="glyphicon glyphicon-off"></span> Salir</a></li>
              </ul> 
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>