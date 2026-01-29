// js/horas_trabajo_diario.js
$(document).ready(function() {
        // Cálculo automático de horas al cambiar hora entrada/salida
        function calcularHorasHTD() {
            var entrada = $('#hora_entrada').val();
            var salida = $('#hora_salida').val();
            var feriado = $('#feriadoHTD').val();
            var fecha = new Date().toISOString().slice(0,10); // fallback si no hay campo fecha
            if ($('#fecha_solicitud').length) {
                fecha = $('#fecha_solicitud').val() || fecha;
            }
            if (!entrada || !salida) {
                $('#cantidad_horas_normal').val(0);
                $('#cantidad_horas_extra').val(0);
                $('#cantidad_horas_extraordinaria').val(0);
                return;
            }
            var h1 = entrada.split(':');
            var h2 = salida.split(':');
            var t1 = parseInt(h1[0],10) + parseInt(h1[1],10)/60;
            var t2 = parseInt(h2[0],10) + parseInt(h2[1],10)/60;
            var diff = t2 - t1;
            if (diff < 0) diff += 24;
            // Restar 1h de almuerzo si la jornada es mayor a 5h
            if (diff > 5) diff -= 1;
            var normales = 0, extra = 0, extraordinaria = 0;
            var diaSemana = (new Date(fecha)).getDay(); // 0=domingo, 6=sabado
            if (feriado == '1' || diaSemana == 0) {
                extraordinaria = diff > 0 ? diff : 0;
            } else if (diaSemana == 6) {
                extra = diff > 0 ? diff : 0;
            } else {
                if (diff <= 8) {
                    normales = diff > 0 ? diff : 0;
                } else {
                    normales = 8;
                    extra = diff - 8;
                }
            }
            $('#cantidad_horas_normal').val(normales.toFixed(2));
            $('#cantidad_horas_extra').val(extra.toFixed(2));
            $('#cantidad_horas_extraordinaria').val(extraordinaria.toFixed(2));
        }

        $('#hora_entrada, #hora_salida, #feriadoHTD').on('change', calcularHorasHTD);
        // Al abrir modal nuevo, inicializar en cero
        $('#btnAgregarHTD').on('click', function(){
            $('#cantidad_horas_normal').val(0);
            $('#cantidad_horas_extra').val(0);
            $('#cantidad_horas_extraordinaria').val(0);
        });
        // Al editar, recalcular si cambian horas
        $('#hora_entrada, #hora_salida, #feriadoHTD').on('change', calcularHorasHTD);
    var id_ejecucion_obra = getParameterByName('id_ejecucion_obra') || '';
    // Al cargar el formulario, obtener el valor de feriado de ejecucion_obra
    if (id_ejecucion_obra) {
        $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'getFeriado', id_ejecucion_obra: id_ejecucion_obra}, function(resp) {
            var r = JSON.parse(resp);
            if (r.success) {
                var feriado = r.feriado == 1 ? '1' : '0';
                $('#feriadoHTD').val(feriado);
                $('#feriadoHTD_hidden').val(feriado);
            }
        });
    }
    cargarEmpleados();
    cargarGrid();

    $('#btnAgregarHTD').click(function() {
        limpiarFormulario();
        // Cambiar el título del modal a NUEVO
        $('#formModalLabelHTD').text('NUEVO REGISTRO DE HORAS');
        $('#formModalHTD').addClass('show').show();
    });
    $('#btnCancelarHTD, #closexHTD').click(function() {
        $('#formModalHTD').removeClass('show').hide();
    });
    $('#formHorasTrabajoDiario').submit(function(e) {
        e.preventDefault();
        // Sincronizar feriado hidden con select (aunque sea readonly)
        $('#feriadoHTD_hidden').val($('#feriadoHTD').val());
        // Asegurar que el id_ejecucion_obra esté correcto en el hidden
        var id_ejecucion_obra_url = getParameterByName('id_ejecucion_obra') || '';
        $('#id_ejecucion_obra').val(id_ejecucion_obra_url);
        // Eliminar el campo fecha_solicitud si existe
        $(this).find('[name="fecha_solicitud"]').remove();
        var datos = $(this).serialize();
        var id = $('#id_horas_trabajo_diario_detalle').val();
        var accion = id ? 'editar' : 'agregar';
        // Si es editar, enviar el id como 'id' (no como id_horas_trabajo_diario_detalle)
        if (accion === 'editar') {
            datos += '&id=' + encodeURIComponent(id);
        }
        $.post('../procesos/ejecucion_obra/horasTrabajoDiario.php', datos + '&accion=' + accion, function(resp) {
            var r = JSON.parse(resp);
            if (r.success) {
                // Cerrar el modal correctamente (remover clase show y ocultar)
                $('#formModalHTD').removeClass('show').hide();
                // Mensaje de éxito tipo SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: accion === 'editar' ? 'Hora actualizada con éxito' : 'Hora agregada con éxito',
                    showConfirmButton: true,
                    confirmButtonColor: '#6c63ff',
                    confirmButtonText: 'OK'
                }).then(function(){
                    // Refrescar el grid y totales correctamente
                    cargarGrid();
                });
            } else {
                Swal.fire('Error', 'No se pudo guardar la hora de trabajo.', 'error');
            }
        });
    });
    $('#btnExportExcelHTD').click(exportarExcelHTD);
    $('#btnExportPDFHTD').click(exportarPDFHTD);

    function cargarGrid() {
        // Destruir DataTable antes de recargar datos para evitar errores de reinicialización
        if ($.fn.DataTable.isDataTable('#gridHorasTrabajoDiario')) {
            $('#gridHorasTrabajoDiario').DataTable().clear().destroy();
        }
        $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'listar', id_ejecucion_obra: id_ejecucion_obra}, function(resp) {
            var r = JSON.parse(resp);
            var html = '';
            var totalNormales = 0, totalExtra = 0, totalExtraordinarias = 0;
            if (r.success) {
                r.data.forEach(function(row) {
                    html += '<tr>' +
                        '<td>' + row.nombres_apellidos + '</td>' +
                        '<td>' + formatoHoraAMPM(row.hora_entrada) + '</td>' +
                        '<td>' + formatoHoraAMPM(row.hora_salida) + '</td>' +
                        '<td>' + row.cantidad_horas_normal + '</td>' +
                        '<td>' + row.cantidad_horas_extra + '</td>' +
                        '<td>' + row.cantidad_horas_extraordinaria + '</td>' +
                        '<td>' +
                        '<button class="btn btn-xs btn-warning btnEditarHTD" data-id="' + row.id_horas_trabajo_diario_detalle + '">Editar</button> ' +
                        '<button class="btn btn-xs btn-danger btnEliminarHTD" data-id="' + row.id_horas_trabajo_diario_detalle + '">Eliminar</button>' +
                        '</td>' +
                        '</tr>';
                    var n = parseFloat(row.cantidad_horas_normal);
                    var e = parseFloat(row.cantidad_horas_extra);
                    var ex = parseFloat(row.cantidad_horas_extraordinaria);
                    totalNormales += (!isNaN(n) && n > 0) ? n : 0;
                    totalExtra += (!isNaN(e) && e > 0) ? e : 0;
                    totalExtraordinarias += (!isNaN(ex) && ex > 0) ? ex : 0;
                });
            }
            $('#gridHorasTrabajoDiario tbody').html(html);
            $('#totalNormales').text(totalNormales.toFixed(2));
            $('#totalExtra').text(totalExtra.toFixed(2));
            $('#totalExtraordinarias').text(totalExtraordinarias.toFixed(2));
            // Inicializar DataTable después de cargar los datos
            var table = $('#gridHorasTrabajoDiario').DataTable({
                responsive: true,
                destroy: true,
                retrieve: false,
                order: [],
                // language: {
                //     url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                // }
            });
            // Actualizar totales al filtrar/buscar
            function recalcularTotales() {
                var data = table.rows({search:'applied'}).data();
                var totalNormales = 0, totalExtra = 0, totalExtraordinarias = 0;
                for (var i = 0; i < data.length; i++) {
                    var n = parseFloat($(data[i][3]).text() || data[i][3]);
                    var e = parseFloat($(data[i][4]).text() || data[i][4]);
                    var ex = parseFloat($(data[i][5]).text() || data[i][5]);
                    totalNormales += (!isNaN(n) && n >= 0) ? n : 0;
                    totalExtra += (!isNaN(e) && e >= 0) ? e : 0;
                    totalExtraordinarias += (!isNaN(ex) && ex >= 0) ? ex : 0;
                }
                $('#totalNormales').text(totalNormales.toFixed(2));
                $('#totalExtra').text(totalExtra.toFixed(2));
                $('#totalExtraordinarias').text(totalExtraordinarias.toFixed(2));
            }
            table.on('draw', recalcularTotales);
            // Forzar el cálculo de totales inmediatamente después de cargar los datos
            recalcularTotales();
        });
    }

    $(document).on('click', '.btnEditarHTD', function() {
        var id = $(this).data('id');
        $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'listar', id_ejecucion_obra: id_ejecucion_obra}, function(resp) {
            var r = JSON.parse(resp);
            if (r.success) {
                var row = r.data.find(x => x.id_horas_trabajo_diario_detalle == id);
                if (row) {
                    $('#id_horas_trabajo_diario_detalle').val(row.id_horas_trabajo_diario_detalle);
                    cargarEmpleadosConSeleccion(row.id_empleado, function() {
                        $('#id_empleado').val(row.id_empleado).trigger('change');
                    });
                    $('#hora_entrada').val(row.hora_entrada);
                    $('#hora_salida').val(row.hora_salida);
                    // Obtener feriado actualizado desde getFeriado (siempre desde ejecucion_obra)
                    $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'getFeriado', id_ejecucion_obra: id_ejecucion_obra}, function(respFeriado) {
                        var rF = JSON.parse(respFeriado);
                        var feriadoVal = (rF.success && typeof rF.feriado !== 'undefined') ? rF.feriado : '0';
                        $('#feriadoHTD').val(feriadoVal);
                        $('#feriadoHTD_hidden').val(feriadoVal);
                        $('#feriadoHTD').trigger('change');
                    });
                    // Cambiar el título del modal a EDITAR
                    $('#formModalLabelHTD').text('EDITAR REGISTRO DE HORAS');
                    $('#formModalHTD').addClass('show').show();
                }
            }
        });

        // Función auxiliar para recargar empleados y seleccionar uno
        function cargarEmpleadosConSeleccion(idSeleccionado, callback) {
            $.get('../procesos/empleados/listarEmpleados.php', function(resp) {
                var r = JSON.parse(resp);
                var html = '<option value="">Seleccione</option>';
                if (r.success) {
                    r.data.forEach(function(emp) {
                        html += '<option value="' + emp.id_empleados + '">' + emp.nombres_apellidos + '</option>';
                    });
                }
                $('#id_empleado').html(html);
                $('#id_empleado').val(idSeleccionado).trigger('change');
                if (typeof callback === 'function') callback();
            });
        }
    });
    $(document).on('click', '.btnEliminarHTD', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro que deseas eliminar este registro?',
            text: 'Se eliminará este registro de horas de trabajo de la base de datos en caso que presiones Sí, eliminar.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'eliminar', id: id}, function(resp) {
                    var r = JSON.parse(resp);
                    if (r.success) {
                        Swal.fire('Eliminado', 'El registro fue eliminado con éxito.', 'success').then(function(){
                            // Forzar recarga completa del grid y DataTable
                            setTimeout(function(){
                                $('#gridHorasTrabajoDiario').DataTable().clear().destroy();
                                cargarGrid();
                            }, 100);
                            });
                        } else {
                            Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                        }
                    });
                }
            });
        });

    function cargarEmpleados() {
        $.get('../procesos/empleados/listarEmpleados.php', function(resp) {
            var r = JSON.parse(resp);
            var html = '<option value="">Seleccione</option>';
            if (r.success) {
                r.data.forEach(function(emp) {
                    html += '<option value="' + emp.id_empleados + '">' + emp.nombres_apellidos + '</option>';
                });
            }
            $('#id_empleado').html(html);
            // Inicializar Select2 después de cargar las opciones
            $('#id_empleado').select2({
                width: '100%',
                placeholder: 'Seleccione un empleado',
                allowClear: true
            });
        });
    }
}); // fin document ready

// Hacer limpiarFormulario global para uso en otros scripts
function limpiarFormulario() {
    $('#formHorasTrabajoDiario')[0].reset();
    $('#id_horas_trabajo_diario_detalle').val('');
    // Al limpiar, volver a cargar el valor de feriado desde ejecucion_obra
    var id_ejecucion_obra = getParameterByName('id_ejecucion_obra') || '';
    if (id_ejecucion_obra) {
        $.get('../procesos/ejecucion_obra/horasTrabajoDiario.php', {accion: 'getFeriado', id_ejecucion_obra: id_ejecucion_obra}, function(resp) {
            var r = JSON.parse(resp);
            if (r.success) {
                var feriado = r.feriado == 1 ? '1' : '0';
                $('#feriadoHTD').val(feriado);
                $('#feriadoHTD_hidden').val(feriado);
                $('#feriadoHTD').trigger('change');
            }
        });
    }
}
    function exportarExcelHTD() {
        var dt = $('#gridHorasTrabajoDiario').DataTable();
        var data = dt.rows({search:'applied'}).data().toArray();
        var wb = new window.ExcelJS.Workbook();
        var ws = wb.addWorksheet('HORAS DE TRABAJO');
        ws.columns = [
            { header: 'Empleado', key: 'empleado', width: 32 },
            { header: 'Hora Entrada', key: 'entrada', width: 15 },
            { header: 'Hora Salida', key: 'salida', width: 15 },
            { header: '#. Horas Normales', key: 'normales', width: 15 },
            { header: '#. Horas Extra', key: 'extra', width: 15 },
            { header: '#. Horas Extraordinarias', key: 'extraord', width: 20 }
        ];
        var totalNormales = 0, totalExtra = 0, totalExtraordinarias = 0;
        data.forEach(row => {
            totalNormales += parseFloat(row[3]);
            totalExtra += parseFloat(row[4]);
            totalExtraordinarias += parseFloat(row[5]);
            ws.addRow({
                empleado: $(row[0]).text() || row[0],
                entrada: row[1],
                salida: row[2],
                normales: row[3],
                extra: row[4],
                extraord: row[5]
            });
        });
        // Fila de totales
        var totalRow = ws.addRow({
            empleado: 'Totales:',
            entrada: '',
            salida: '',
            normales: totalNormales.toFixed(2),
            extra: totalExtra.toFixed(2),
            extraord: totalExtraordinarias.toFixed(2)
        });
        // Sombrear la fila de totales en azul y texto blanco/negrita
        totalRow.eachCell(function(cell) {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FF2986C5' }
            };
            cell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
        });
        // Sombrear encabezados igual
        ws.getRow(1).eachCell(function(cell) {
            cell.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'FF2986C5' }
            };
            cell.font = { color: { argb: 'FFFFFFFF' }, bold: true };
        });
        ws.getRow(1).height = 20;
        ws.views = [{ state: 'frozen', ySplit: 1 }];
        wb.xlsx.writeBuffer().then(function(buffer) {
            saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'Horas_Trabajo_Diario.xlsx');
        });
    }
    function exportarPDFHTD() {
        const doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'A4' });
        doc.setFontSize(20);
        doc.setTextColor(40, 55, 71);
        const pageWidth = doc.internal.pageSize.getWidth();
        doc.text("HORAS DE TRABAJO ", pageWidth / 2, 40, { align: "center" });
        const columns = [
            { header: 'Empleado', dataKey: 'empleado' },
            { header: 'Hora Entrada', dataKey: 'entrada' },
            { header: 'Hora Salida', dataKey: 'salida' },
            { header: '#. Horas Normales', dataKey: 'normales' },
            { header: '#. Horas Extra', dataKey: 'extra' },
            { header: '#. Horas Extraordinarias', dataKey: 'extraord' }
        ];
        var dt = $('#gridHorasTrabajoDiario').DataTable();
        var data = dt.rows({search:'applied'}).data().toArray();
        var totalNormales = 0, totalExtra = 0, totalExtraordinarias = 0;
        var body = data.map(row => {
            totalNormales += parseFloat(row[3]);
            totalExtra += parseFloat(row[4]);
            totalExtraordinarias += parseFloat(row[5]);
            return {
                empleado: $(row[0]).text() || row[0],
                entrada: row[1],
                salida: row[2],
                normales: row[3],
                extra: row[4],
                extraord: row[5]
            };
        });
        // Agregar fila de totales
        body.push({
            empleado: 'Totales:',
            entrada: '',
            salida: '',
            normales: totalNormales.toFixed(2),
            extra: totalExtra.toFixed(2),
            extraord: totalExtraordinarias.toFixed(2)
        });
        doc.autoTable({
            columns: columns,
            body: body,
            startY: 70,
            styles: { fontSize: 10, cellPadding: 5 },
            headStyles: { fillColor: [41, 134, 197], textColor: [255,255,255], fontStyle: 'bold' },
            margin: { left: 40, right: 40 },
            didParseCell: function (data) {
                if (data.row.index === body.length - 1 && data.section === 'body') {
                    data.cell.styles.fillColor = [41, 134, 197];
                    data.cell.styles.textColor = [255,255,255];
                    data.cell.styles.fontStyle = 'bold';
                }
            }
        });
        const fecha = new Date().toLocaleString();
        doc.setFontSize(10);
        doc.setTextColor(127, 140, 141);
        doc.text(`Generado el: ${fecha}`, 40, doc.internal.pageSize.getHeight() - 30);
        doc.save('Horas_Trabajo_Diario.pdf');
    }
    $('#btnExportExcelHTD').off('click').on('click', exportarExcelHTD);
    $('#btnExportPDFHTD').off('click').on('click', exportarPDFHTD);
    function getParameterByName(name) {
        var url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }

// Formato hora 24h a 12h AM/PM
function formatoHoraAMPM(hora) {
    if (!hora) return '';
    var partes = hora.split(':');
    var h = parseInt(partes[0], 10);
    var m = partes[1];
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12; // 0 => 12
    return (h < 10 ? '0' : '') + h + ':' + m + ' ' + ampm;
}
