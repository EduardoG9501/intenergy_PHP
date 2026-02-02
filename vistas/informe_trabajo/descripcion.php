<?php
require_once "../../clases/Conexion.php";
require_once "../../clases/InformeTrabajo.php";
$id_informe = $_GET['id_informe'];
$informe = new InformeTrabajo();
$datos = $informe->obtener($id_informe);
$descripcion = isset($datos['descripcion']) ? $datos['descripcion'] : '';
?>
<div class="panel panel-default">
  <div class="panel-heading"><b>Descripción del Informe Diario</b></div>
  <div class="panel-body">
    <form id="formDescripcionInforme">
      <textarea name="descripcion" id="descripcion" class="form-control" rows="7" placeholder="Ingrese la descripción del informe..." style="font-size:16px;resize:vertical;"><?php echo htmlspecialchars($descripcion); ?></textarea>
      <input type="hidden" name="id_informe" value="<?php echo $id_informe; ?>">
      <br>
      <button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-floppy-disk"></i> Guardar</button>
    </form>
    <div id="msgDescripcion" style="margin-top:10px;"></div>
  </div>
</div>
<script>
$('#formDescripcionInforme').submit(function(e){
  e.preventDefault();
  $.ajax({
    url: '../procesos/informe_trabajo/informe_trabajo.php',
    type: 'POST',
    data: $(this).serialize() + '&action=actualizar_descripcion',
    success: function(resp){
      let r = JSON.parse(resp);
      if(r.success){
        $('#msgDescripcion').html('<span class="text-success">Descripción guardada correctamente.</span>');
      }else{
        $('#msgDescripcion').html('<span class="text-danger">Error al guardar la descripción.</span>');
      }
    },
    error: function(){
      $('#msgDescripcion').html('<span class="text-danger">Error de conexión.</span>');
    }
  });
});
</script>
