
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css?t=<?php echo mt_rand(); ?>">
    
	<link rel="stylesheet" href="barra-menu/estilos/fonts.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/prism.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/custom.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/extra.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/formulario.css?t=<?php echo mt_rand(); ?>">

	<title>Document</title>
</head>
<body>php
    
		<div class="container-fluid bg-light pt-2 pb-2">
			<div class="container ps-0">
				<nav class="navbar navbar-expand-lg navbar-light bg-light">
  				<div class="container-fluid ps-0 pe-0">

						<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="true" aria-label="Toggle navigation">
							<span class="navbar-toggler-icon"></span>
						</button>

						<div class="collapse navbar-collapse" id="navbarMain">
							<ul class="navbar-nav me-auto mb-2 mb-lg-0">

								<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownHelp" role="button" data-bs-toggle="dropdown" aria-expanded="false">
										Help									</a>
									<ul class="dropdown-menu mt-lg-3" aria-labelledby="navbarDropdownHelp">
										<li><a class="dropdown-item" href="https://apps.mamp.info/remote-help/?ref=RHR_10_&amp;language=en" target="_blank">Documentation</a></li>
										<li><a class="dropdown-item" href="http://www.mamp.tv" target="_blank">Screencasts</a></li>
										<li><a class="dropdown-item" href="https://bugs.mamp.info" target="_blank">Bugbase</a></li>
									</ul>
								</li>

								<li class="nav-item">
									<a class="nav-link" href="https://www.mamp.info/en/mamp/mac/" target="_blank">MAMP Website</a>
								</li>
								
                      			<li class="nav-item"><a class="nav-link" href="http://localhost:8888" target="_blank">My Website</a></li>

							</ul>
							
							<div class="d-flex">
								<a href="https://www.mamp.info/macstore" target="_blank" class="btn btn-success btn-sm" role="button">Samuel Lujan</a>							
							</div>

						</div>
					</div>
				</nav>
			</div>
		</div>
		
		<div class="container-fluid text-center text-white bg-mamp pt-5 pb-4">
			<h1 class="display-4">Welcome to MAMP</h1>
			<p class="lead">
				Your version is 7.0&nbsp;→&nbsp;<a href="https://www.mamp.info/en/downloads/" class="text-white">Latest version: 7.1</a>				
			</p>
		</div>


		<?php
		
			//include'cuerpo.php';
		
		?>


	<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js?t=<?php echo mt_rand(); ?>"></script>
	<script src="barra-menu/javascript/prism.js?t=<?php echo mt_rand(); ?>"></script>

</body>
</html>