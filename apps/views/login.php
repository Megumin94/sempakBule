<!DOCTYPE html>
<html lang="ID">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>| MR.Catfish Administrator's LOGIN! |</title>

    <!-- Bootstrap core CSS -->

    <link href="<?php echo base_URL();?>assets/admin/css/bootstrap.min.css" rel="stylesheet">

    <link href="<?php echo base_URL();?>assets/admin/fonts/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo base_URL();?>assets/admin/css/animate.min.css" rel="stylesheet">

    <!-- Custom styling plus plugins -->
    <link href="<?php echo base_URL();?>assets/admin/css/custom.css" rel="stylesheet">
    <link href="<?php echo base_URL();?>assets/admin/css/icheck/flat/green.css" rel="stylesheet">


    <script src="<?php echo base_URL();?>assets/admin/js/jquery.min.js"></script>

    <!--[if lt IE 9]>
        <script src="../assets/js/ie8-responsive-file-warning.js"></script>
        <![endif]-->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->

</head>

<body style="background:#F7F7F7;">
    
    <div class="">
        <a class="hiddenanchor" id="toregister"></a>
        <a class="hiddenanchor" id="tologin"></a>

        <div id="wrapper">
            <div id="login" class="animate form">
                <section class="login_content">
                    <form method="POST" action="<?php echo base_URL();?>c_admin/loginProcess" role="form">
                        <h1>LOG IN</h1>
                        <div>
                            <input type="text" class="form-control" placeholder="Username" required="" />
                        </div>
                        <div>
                            <input type="password" class="form-control" placeholder="Password" required="" />
                        </div>
                        <div>
                            <button class="btn btn-default btn-sm submit">Log in</button>
                            <a class="reset_pass" href="<?php echo base_URL(); ?>">Back To Main Page</a>
                        </div>
                        <div class="clearfix"></div>
                        <div class="separator">

                            <!-- <p class="change_link">New to site?
                                <a href="#toregister" class="to_register"> Create Account </a>
                            </p> -->
                            <div class="clearfix"></div>
                            <br />
                            <div>
                                <h1><i class="fa fa-laptop" style="font-size: 26px;"></i> MR.Catfish Administrator</h1>
                                     
                                <p>©2015 All Rights Reserved. MR.Catfih Project</p>
                            </div>
                        </div>
                    </form>
                    <!-- form -->
                </section>
                <!-- content -->
            </div>
        </div>
    </div>

</body>

</html>