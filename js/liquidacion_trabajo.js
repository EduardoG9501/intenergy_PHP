(function(){
    var endpoint = window.REPORTE_EJECUCION_ENDPOINT || '../procesos/reportes/liquidacion_trabajo.php';
    var idLiquidacionActual = 0;
    var imagenesPendientes = [];
    var timerRecargaFiltros = null;

    function safeParse(data) {
        try {
            return typeof data === 'string' ? JSON.parse(data) : data;
        } catch (e) {
            return null;
        }
    }

    function obtenerFiltrosLiquidacion() {
        return {
            id_orden: ($('#id_orden').val() || '').trim()
        };
    }

    function renderImagenes(imagenes) {
        var $contenedor = $('#previewImagenesLiquidacion');
        $contenedor.html('');

        if (!Array.isArray(imagenes) || imagenes.length === 0) {
            return;
        }

        imagenes.forEach(function(img) {
            var html = '<div class="img-liquidacion-item">' +
                '<img src="../' + (img.ruta || '') + '" alt="Imagen" />' +
                '<div style="font-size:10px; margin-top:3px; word-break:break-all;">' + (img.nombre || '') + '</div>' +
                '<button type="button" class="btn btn-danger btn-xs btnEliminarImagenLiquidacion" data-id="' + (img.id_liquidacion_trabajo_imagen || 0) + '" style="margin-top:4px; width:100%;">Eliminar</button>' +
                '</div>';
            $contenedor.append(html);
        });
    }

    function renderImagenesPendientes() {
        var $contenedor = $('#listaImagenesPendientes');
        $contenedor.html('');

        if (!imagenesPendientes.length) {
            return;
        }

        imagenesPendientes.forEach(function(file, index) {
            var html = '<div class="item-pendiente-imagen">' +
                '<span>' + (file.name || 'imagen') + '</span>' +
                '<button type="button" class="btn btn-danger btn-xs btnQuitarImagenPendiente" data-index="' + index + '">Quitar</button>' +
                '</div>';
            $contenedor.append(html);
        });
    }

    function limpiarPendientes() {
        imagenesPendientes = [];
        $('#imagenes_liquidacion').val('');
        renderImagenesPendientes();
    }

    function cargarLiquidacion() {
        var filtros = obtenerFiltrosLiquidacion();
        if (!filtros.id_orden) {
            idLiquidacionActual = 0;
            $('#txtManoObra').val('');
            $('#txtImportante').val('');
            renderImagenes([]);
            limpiarPendientes();
            return;
        }

        $.post(endpoint, {accion: 'obtener_liquidacion', filtros: filtros}, function(data) {
            var res = safeParse(data);
            if (!res || !res.success || !res.data) {
                idLiquidacionActual = 0;
                $('#txtManoObra').val('');
                $('#txtImportante').val('');
                renderImagenes([]);
                limpiarPendientes();
                return;
            }

            idLiquidacionActual = res.data.id_liquidacion_trabajo || 0;
            $('#txtManoObra').val(res.data.mano_obra || '');
            $('#txtImportante').val(res.data.importante || '');
            renderImagenes(res.data.imagenes || []);
        });
    }

    function recargarGridsReporte() {
        if (typeof cargarMateriales === 'function') {
            cargarMateriales();
        }
        if (typeof cargarHoras === 'function') {
            cargarHoras();
        }
        if (typeof cargarInformes === 'function') {
            cargarInformes();
        }
    }

    function recargarTodoPorFiltros() {
        recargarGridsReporte();
        cargarLiquidacion();
    }

    function guardarLiquidacion() {
        var filtros = obtenerFiltrosLiquidacion();
        if (!filtros.id_orden) {
            alertify.warning('Debe ingresar el ID Orden.');
            return;
        }

        var manoObra = ($('#txtManoObra').val() || '').trim();
        var importante = ($('#txtImportante').val() || '').trim();

        if (!manoObra) {
            alertify.warning('Debe ingresar una descripción en MANO DE OBRA.');
            $('#txtManoObra').focus();
            return;
        }

        if (!importante) {
            alertify.warning('Debe ingresar una descripción en IMPORTANTE.');
            $('#txtImportante').focus();
            return;
        }

        var formData = new FormData();
        formData.append('accion', 'guardar_liquidacion');
        formData.append('id_orden', filtros.id_orden || '');
        formData.append('mano_obra', manoObra);
        formData.append('importante', importante);

        var filesInput = document.getElementById('imagenes_liquidacion');
        if (filesInput && imagenesPendientes.length > 0) {
            for (var i = 0; i < imagenesPendientes.length; i++) {
                formData.append('imagenes[]', imagenesPendientes[i]);
            }
        }

        $.ajax({
            url: endpoint,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                var res = safeParse(resp);
                if (res && res.success) {
                    alertify.success(res.msg || 'Liquidación guardada correctamente');
                    limpiarPendientes();
                    cargarLiquidacion();
                } else {
                    alertify.error((res && res.msg) ? res.msg : 'No se pudo guardar la liquidación');
                }
            },
            error: function() {
                alertify.error('Error al guardar la liquidación');
            }
        });
    }

    $(document).on('click', '.btnEliminarImagenLiquidacion', function() {
        var idImagen = $(this).data('id') || 0;
        if (!idImagen) {
            return;
        }

        $.post(endpoint, {accion: 'eliminar_imagen_liquidacion', id_liquidacion_trabajo_imagen: idImagen}, function(resp) {
            var res = safeParse(resp);
            if (res && res.success) {
                alertify.success('Imagen eliminada');
                cargarLiquidacion();
            } else {
                alertify.error('No se pudo eliminar la imagen');
            }
        });
    });

    $(document).on('click', '.btnQuitarImagenPendiente', function() {
        var index = parseInt($(this).data('index'), 10);
        if (isNaN(index) || index < 0 || index >= imagenesPendientes.length) {
            return;
        }
        imagenesPendientes.splice(index, 1);
        renderImagenesPendientes();
    });

    $(document).ready(function() {
        $('#btnGuardarLiquidacion').on('click', guardarLiquidacion);

        $('#imagenes_liquidacion').on('change', function() {
            if (!this.files || !this.files.length) {
                return;
            }

            for (var i = 0; i < this.files.length; i++) {
                imagenesPendientes.push(this.files[i]);
            }
            $('#imagenes_liquidacion').val('');
            renderImagenesPendientes();
        });

        $('#formFiltrosEjecucionObra').on('submit', function() {
            setTimeout(recargarTodoPorFiltros, 250);
        });

        $('#id_orden').on('change', function() {
            clearTimeout(timerRecargaFiltros);
            timerRecargaFiltros = setTimeout(recargarTodoPorFiltros, 250);
        });

        $('#id_orden').on('keyup', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recargarTodoPorFiltros();
            }
        });

        renderImagenesPendientes();
        setTimeout(recargarTodoPorFiltros, 350);
    });
})();
