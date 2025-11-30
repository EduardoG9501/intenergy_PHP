<?php
session_start();
if(isset($_SESSION['usuario'])){
    $id_informe = $_GET['id_informe'] ?? 0;
    require_once "../../clases/Conexion.php";
    require_once "../../clases/Reportes.php";
    $reporte = new Reporte();
    $filtros = ['fecha_desde' => '', 'fecha_hasta' => '', 'id_bodega_principal' => '', 'id_bodega_secundaria' => '', 'tipo_informe'=>'general'];
    // Solo uno
    $informes = array_filter($reporte->listarReporteGeneralInformesDiarios($filtros), function($inf) use($id_informe){ return $inf['id_informe'] == $id_informe; });
    $informe = count($informes) ? array_values($informes)[0] : null;
?>
<div>
    <h4>Detalle General del Informe</h4>
    <?php if($informe): ?>
    <table class="table table-bordered">
        <tr>
            <th>ID Informe</th>
            <th>Orden Trabajo</th>
            <th>Fecha</th>
            <th>Lugar</th>
            <th>Bodega Principal</th>
            <th>Bodega Secundaria</th>
            <th>Observación</th>
        </tr>
        <tr>
            <td><?= $informe['id_informe'] ?></td>
            <td><?= htmlspecialchars($informe['identificador']) ?></td>
            <td><?= $informe['fecha'] ?></td>
            <td><?= htmlspecialchars($informe['lugar']) ?></td>
            <td><?= htmlspecialchars($informe['bodega_principal']) ?></td>
            <td><?= htmlspecialchars($informe['bodega_secundaria']) ?></td>
            <td><?= htmlspecialchars($informe['observacion']) ?></td>
        </tr>
        <tr>
            <td colspan="7">
                <div style="display:flex;gap:32px;flex-wrap:wrap">
                    <div>
                        <b>Artículos:</b>
                        <ul>
                        <?php foreach($informe['articulos'] as $art): ?>
                            <li><?= htmlspecialchars($art['nombre']) ?> (<?= $art['cantidad'] ?>) [Lote: <?= htmlspecialchars($art['lote']) ?>]</li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <b>Empleado Interno:</b>
                        <ul>
                        <?php foreach($informe['empleados_internos'] as $ei): ?>
                            <li><?= htmlspecialchars($ei) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <b>Empleado Externo:</b>
                        <ul>
                        <?php foreach($informe['empleados_externos'] as $ee): ?>
                            <li><?= htmlspecialchars($ee) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                    <div>
                        <b>Imágenes:</b><br>
                        <?php foreach($informe['imagenes'] as $img): ?>
                            <img src="../<?= htmlspecialchars($img) ?>" alt="img" style="max-width:70px;max-height:50px;margin:2px;">
                        <?php endforeach; ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <?php else: ?>
        <div>No se encontró el informe.</div>
    <?php endif; ?>
</div>
<?php
} else {
    header("location:../../index.php");
}
?>