</head>
  <body>
    <div class="dmap container-fluid">
    <!--header -->
    <header class="page-header row-fluid">
	      <h1 class="pull-left"><a href="/">DemocracyMap</a></h1>
	      <div class="pull-right">
	        <a href="/" role="button" class="btn btn-info">About</a>
	        <a href="/" role="button" class="btn btn-info">API</a>
	
		<?php
		 if($this->user->validate_session()):
		?>
		       	<a href="/logout" role="button" class="btn btn-info">Logout</a>
		<?php
	 	 else:
		?>		
				 <a href="/login" role="button" class="btn btn-info">Login</a>
		<?php
		 endif;
		?>	
	      </div>
    </header>