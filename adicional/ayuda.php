<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
	

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	


	<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />
	<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>



	<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.4/css/buttons.dataTables.css">
								 https://cdn.datatables.net/buttons/3.2.4/css/buttons.dataTables.min.css




	<script src="https://cdn.datatables.net/buttons/3.2.4/js/dataTables.buttons.js"></script>
	<--			 https://cdn.datatables.net/buttons/3.2.4/js/dataTables.buttons.min.js		

	<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.dataTables.js"></script>

				         


	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.print.min.js"></script>

//cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css

//cdn.datatables.net/2.3.2/js/dataTables.min.js

	
	<title>Document</title>
</head>
<body>
	
	<!-- Modal Body -->
	<!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
	<div
		class="modal fade"
		id="modalUsuario"
		tabindex="-1"
		data-bs-backdrop="static"
		data-bs-keyboard="false"
		
		role="dialog"
		aria-labelledby="modalTitleId"
		aria-hidden="true"
	>
		<div
			class="modal-dialog modal-dialog-scrollable modal-dialog-centered"
			role="document"
		>
			<div class="modal-content">
				<form action="" method="post" id="formUsuario">
					<div class="modal-header">
						<h5 class="modal-title" id="modalTitleId">
							Datos del usuario
						</h5>
						<button
							type="button"
							class="btn-close"
							data-bs-dismiss="modal"
							aria-label="Close"
						></button>
					</div>

					<form action="" method="post">
					<div class="modal-body">
				
					

						<div class="mb-3">
						
							<input
								type="hidden"
								class="form-control"
								name="id"
								id="id"
								placeholder=""
								
							/>
						</div>
				

						<div class="mb-3">
							<label for="" class="form-label">Name</label>
							<input
								type="text"
								class="form-control"
								name="nombre"
								id="nombre"
								placeholder="Nombre"
								required
							/>
						</div>

						<div class="mb-3">
							<label for="" class="form-label">Correo:</label>
							<input
								type="email"
								class="form-control"
								name="correo"
								id="correo"Nombre
								placeholder="Correo"
								required
							/>
						</div>

					</div>

					<div class="modal-footer">
						<button
							type="button"
							class="btn btn-secondary"
							data-bs-dismiss="modal"
						>
							Close
						</button>
						<button type="submit" class="btn btn-primary">Save</button>
					</div>

				</form>
			</div>
		</div>
	</div>
	
	<!-- Optional: Place to the bottom of scripts -->
	<script>

		window.addEventListener('load',function(){
			//const modal = new bootstrap.Modal(document.getElementById("modalUsuario"));
		});

			
		
	</script>
	
	<div class="container">
		
		<table id="tabla_usuario" class="display">
			<thead>
				<tr>
					<th>ID</th>
					<th>NOMBRE</th>
					<th>CORREO</th>
				</tr>
			</thead>
			<tbody>
			
			</tbody>
		</table>
	
	</div>

	<script>	
		window.addEventListener('load',function(){
			const modal = new bootstrap.Modal(document.getElementById("modalUsuario"));

			let tabla = new DataTable('#tabla_usuario',{

				layout: {
					bottomStart: {
						buttons: ['copy', 'csv', 'excel', 'pdf', 'print',{
							text:'Agregar usuario',
							className:'btn btn-success',
							init:function(dt,node,config){
								node.removeClass('dt-button');
							},
							action:function(e,dt,node,config){
								document.getElementById('formUsuario').reset();
								document.getElementById('id').value = '';
								modal.show();
							}
						}]
					}
				},

				processing:true, /*
						1.-Muestra un indicador de "Procesando..." mientras se realiza una petición AJAX.
						2.-Solo es visual, no cambia el comportamiento funcional de la tabla.
				*/

				serverSide:true, /*
						Le dice a DataTables que no cargue todos los datos de golpe, sino que:
							-Pida los datos en bloques (paginación),
							-Aplique el filtro y ordenamiento en el servidor, y
							-Use AJAX para comunicarse con tu backend (PHP, Python, etc).
				*/
				pageLength:2,
				paging:true,
				lengthMenu:[2,5,10,25,30],
				language:{
					url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'

					//url:'//cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
			
				},
				ajax:'data.php',
				columns:[
					{data:'id'},
					{data:'nombre'},
					{data:'corre'},
					{
						data:null,
						render:function(data,type,row){
							var botones;
							botones = '<button class="btn btn-info btnEditar" data-id="'+row.id +'" data-nombre="'+row.nombre +'" data-corre="'+row.corre +'">Editar</button> ';
							botones += '<button class="btn btn-danger btnEliminar" data-id="'+ row.id + '">ELiminar</button>';
							return botones;
						},
					}
				]

			});

			document.getElementById('tabla_usuario').addEventListener('click', function(e) {
  				if (e.target.classList.contains('btnEliminar')) {
    				if(confirm('Estas seguro de eliminar')){

					var datos = new FormData();
					datos.append('eliminar',true);
					datos.append('id',e.target.getAttribute('data-id'));

					fetch('crud.php',{
						method: 'POST',
						body: datos
					})
					.then(response => response.json())
					.then(data =>{
						console.log(data['mensaje']);
						tabla.ajax.reload();
					})
					//alert(e.target.getAttribute('data-id'));
				}
				//console.log(e.target.getAttribute('data-id'));
 				}else if(e.target.classList.contains('btnEditar')){
					document.getElementById('id').value = e.target.getAttribute('data-id');
					document.getElementById('nombre').value = e.target.getAttribute('data-nombre');
					document.getElementById('correo').value = e.target.getAttribute('data-corre');
					modal.show();
					
				}
			});
			


			var formulario = document.getElementById('formUsuario');
			formulario.addEventListener('submit',function(e){
				e.preventDefault();
				var datos = new FormData(document.getElementById('formUsuario'));
				
				fetch('crud.php',{
					method: 'POST',
					body: datos
				})
				.then(response=>response.json())
				.then(data=>{
					modal.hide();
					tabla.ajax.reload();
					console.log(data['mensaje']);
				})


			});

		});


	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	
</body>
</html>




