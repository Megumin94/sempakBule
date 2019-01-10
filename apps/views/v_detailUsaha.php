<!DOCTYPE html>
<html lang="ID">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>| Detail Analisa |</title>

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

         <!--  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/core/css/grid/1200.css" />-->
        <!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/core/meteor.css" />  -->

</head>

<body style="background:#F7F7F7;">
    <div class="wrapper" style="width: 100%; height: 100px; background-color: orange;">
        <center style="color:white; padding:2px">
            <h1>MR.CATFISH</h1>
            <h5>Sistem Penunjang Keputusan dalam Perintisan Usaha Bisnis Budidaya Ikan Lele</h5>
        </center>
    </div>
    <div class="" style="margin-top: -100px;">
       <!--  <a class="hiddenanchor" id="toregister"></a>
        <a class="hiddenanchor" id="tologin"></a> -->

        <div id="wrapper">
            <div id="login" class="animate form">
                <section class="login_content">
                    <form method="POST" action="<?php echo base_URL();?>c_admin/loginProcess" role="form">
                        <h4>Detail Analisa</h4>
                        <div>
                            <label>Ukuran Kolam</label>
                            <input type="text" name="ukuran" class="form-control" placeholder="<?php echo $detail[0]['ukuran_kolam'] ?> Meter" readonly>
                        </div>
                        <div>
                            <label>Material Kolam</label>
                            <input type="text" class="form-control" placeholder="<?php echo $detail[0]['material_kolam'] ?>" readonly/>
                        </div>
                        <div>
                            <label>Musim</label>
                            <input type="text" class="form-control" placeholder="<?php echo $detail[0]['musim'] ?>" readonly/>
                        </div>
                        <div>
                            <label>Pilihan Pakan</label>
                            <input type="text" class="form-control" placeholder="<?php echo $detail[0]['pilihan_pakan'] ?>" readonly/>
                        </div>
                        <div>
                            <label>Rincian Analisa</label>
                            <textarea class="form-control" style="height:500px" readonly><?php echo $detail[0]['rincian_analisa']?></textarea> 
                        </div>
                        <div>
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
                                <h1><i class="fa fa-laptop" style="font-size: 26px;"></i> MR.Catfish</h1>
                                     
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