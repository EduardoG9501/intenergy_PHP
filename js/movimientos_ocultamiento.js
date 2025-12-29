// Script para ocultar/mostrar columnas y campos según el subtipo de movimiento
// Aplica tanto para la tabla de artículos como para los formularios de edición

function ocultarColumnasPorSubtipo(subtipo) {
    // Columnas de la tabla (por índice, 0-based)
    // ID, ARTICULO, BODEGA, CANTIDAD, PRECIO, SUBTOTAL, DESCUENTO, IVA, TOTAL, ACCIONES
    var columnasOcultar = [4, 6, 7, 8]; // PRECIO, DESCUENTO, IVA, TOTAL
    var mostrar = (subtipo === 'COMPRAS');
    var tabla = $('#tbDatosMaestrosDetalle').DataTable();
    columnasOcultar.forEach(function(idx) {
        tabla.column(idx).visible(mostrar);
    });
    // LOTE es campo, no columna
    // Ocultar totales row
    if (!mostrar) {
        $('.text-resumen').closest('.col-sm-2, .col-sm-4').hide();
    } else {
        $('.text-resumen').closest('.col-sm-2, .col-sm-4').show();
    }
}

function ocultarCamposFormularioPorSubtipo(subtipo) {
    var mostrar = (subtipo === 'COMPRAS');
    // En el modal editar producto
    if (!mostrar) {
        $('#precioU').closest('.form-group, .col-sm-6').hide();
        $('#loteU').closest('.form-group, .col-sm-6').hide();
    } else {
        $('#precioU').closest('.form-group, .col-sm-6').show();
        $('#loteU').closest('.form-group, .col-sm-6').show();
    }
    // En el modal descuento/iva
    if (!mostrar) {
        $('#porceIVAItem, #ckaplicarivaItem').closest('.form-group, .col-sm-12').hide();
        $('#porceDESCUENTOItem, #ckaplicardescuentoItem').closest('.form-group, .col-sm-12').hide();
    } else {
        $('#porceIVAItem, #ckaplicarivaItem').closest('.form-group, .col-sm-12').show();
        $('#porceDESCUENTOItem, #ckaplicardescuentoItem').closest('.form-group, .col-sm-12').show();
    }
}

// Detectar subtipo seleccionado y aplicar lógica
function aplicarOcultamientoPorSubtipo() {
    var subtipo = window.subtipoMovimientoActivo || '';
    ocultarColumnasPorSubtipo(subtipo);
    ocultarCamposFormularioPorSubtipo(subtipo);
}

// Exponer para uso global
window.aplicarOcultamientoPorSubtipo = aplicarOcultamientoPorSubtipo;
