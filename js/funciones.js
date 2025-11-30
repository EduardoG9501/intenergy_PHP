function validarFormVacio(formulario){
		datos=$('#' + formulario).serialize();
		d=datos.split('&');
		vacios=0;
		for(i=0;i< d.length;i++){
				controles=d[i].split("=");
				if(controles[1]=="A" || controles[1]==""){
					vacios++;
				}
		}
		return vacios;
	}
	function validarFormVacioArticulosInsert(formulario){
		datos=$('#' + formulario).serialize();
		d=datos.split('&');
		vacios=0;
		for(i=0;i< d.length;i++){
				controles=d[i].split("="); 
				if (controles[0] != "codigobarra")
				{
                  if(controles[1]=="A" || controles[1]=="")
					{
					    vacios++;
				   }
				}
				
		}
		return vacios;
	}
	function validarFormVacioArticulosActualizar(formulario){
		datos=$('#' + formulario).serialize();
		d=datos.split('&');
		vacios=0;
		for(i=0;i< d.length;i++){
				controles=d[i].split("="); 
				if (controles[0] != "codigobarraU") 
				{
                  if(controles[1]=="A" || controles[1]=="")
					{
					    vacios++;
				   }
				}
				
		}
		return vacios;
	}
	function validarFormVacioMovimiento(formulario){
		datos=$('#' + formulario).serialize();
		d=datos.split('&');
		vacios=0;
		for(i=0;i< d.length;i++){
				controles=d[i].split("="); 
				if (controles[0] != "observacion")
				{
                  if(controles[1]=="A" || controles[1]=="")
					{
					    vacios++;
				   }
				}
				
		}
	}