<!DOCTYPE html>
<html class="not-ie" lang="en-US">
    <head>

        <title>Mr. Catfish</title>
        <meta charset="UTF-8" />
        <meta name="description" content="Responsive HTML5 Template" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link rel="shortcut icon" type="image/png" href="<?php echo base_url(); ?>assets/core/images/favicon.png" />

        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=PT+Sans:regular,italic,700,700italic&amp;subset=latin" />
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Raleway:300,regular,500,600,700,800,900&amp;subset=latin" />

        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/core/css/grid/1200.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/core/meteor.css" />

        <!-- CSS FOR TABLE -->
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/cssForTable/jquery.dataTables.min.css" />
 
        <!-- <link rel="stylesheet" type="text/css" href="core/typography.css" /> -->

        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/json2.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/cssForTable/jquery-1.11.3.min.js"></script>
        <!-- <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.min.js"></script> -->
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/head.load.min.js"></script>

    </head>

    <body data-widescreen="true">

        <header role="banner" data-sticky="true" data-sticky-distance="500" data-sticky-min-logo-height="29" data-sticky-offset="18" data-sticky-mobile="false">
            <section id="nav-logo" data-nav-bottom-sep="15">
                <div class="container clearfix">
                    <div id="logo" class="hover-effect" data-align="left" style="padding: 36px 0;">
                        <a class="retina-hide" href="<?php echo base_url(); ?>"><b style="font-size:22px; font-family:Verdana, Geneva, sans-serif">MR. CATFISH</b></a>
                        <p style="font-size:12px; font-family:Verdana, Geneva, sans-serif; margin-bottom:-2px">Sistem Penunjang Keputusan dalam Perintisan Usaha Bisnis Budidaya Ikan Lele </p>
                        <a class="retina-show" href="<?php echo base_url(); ?>"><b style="font-size:22px; font-family:Verdana, Geneva, sans-serif">MR. CATFISH</b></a>
                    </div><!-- logo -->
                    <div id="nav-container" class="clearfix" data-align="right">
                        <ul id="navigation" class="hidden-phone fallback clearfix" data-align="right">
                            <li><a href="#">Who We Are</a>
                                <ul class="sub-menu">
                                    <li><a>M.Ismawan Zain</a></li>
                                    <li><a>Yuca Akbar</a></li>
                                    <li><a>M. Nahdhiana H</a></li>
                                    <li><a>Nining Syafiatul</a></li>
                                    <li><a>Dewangga Adtya</a></li>
                                </ul>
                            </li>
                            <!-- <li> -->
                            <!-- <a href="<?php echo base_url(); ?>index/logIn">Login Admin</a> -->
                            <!-- </li> -->
                            <!-- <li class="search hoverable last-child">
                                <form class="clearfix" action="http://google.co.id/" method="get">
                                    <p><input type="text" name="search" placeholder="Search" autocomplete="off" /></p> 
                                </form>
                            </li> -->
                        </ul><!-- navigation -->
                    </div><!-- nav-container -->
                </div><!-- .container -->
            </section><!-- nav-logo -->
        </header>

        <!-- + -->


        <section class="block fullwidth">
            <div class="container-full">
                <div class="meteor-slider slider-core" data-options="effect:fade, direction:horizontal, easing:easeInOutQuad, speed:750, autoplay, timeout:4500, pauseOnHover:false, reverse:false">
                    <ul class="slides">
                        <li data-title="PANEN!!! Bibit Lele Siap Jual" data-description="Bibit ikan lele siap jual" data-uri="#slide-1">
                            <img alt="" src="<?php echo base_url(); ?>assets/content/slide-fw-1-1600x566_c1.jpg" />
                        </li>
                        <li data-title="Persiapan Pemijahan Lele" data-description="Hal-hal yang perlu diperhatikan ketikan pemijahan" data-uri="#slide-2">
                            <img alt="" src="<?php echo base_url(); ?>assets/content/slide-fw-2-1600x566_c1.jpg" />
                        </li>
                        <li data-title="Bibit Lele Sehat" data-description="Tahukah Anda ciri ciri bibit ikan lele yang sehat..." data-uri="#slide-3">
                            <img alt="" src="<?php echo base_url(); ?>assets/content/slide-fw-3-1600x566_c1.jpg" />
                        </li>
                        <li data-title="Kolam Untuk Bisnis Anda" data-description="Kriteria kolam ikan lele standart" data-uri="#slide-4">
                            <img alt="" src="<?php echo base_url(); ?>assets/content/slide-fw-4-1600x566_c1.jpg" />
                        </li>
                    </ul>
                </div><!-- .meteor-slider -->
            </div><!-- .container -->
        </section>

        <!-- + -->

        <section class="block container">
            <div class="row">
                <div class="meteor-aside-posts span4" data-lightbox="true" data-click-behavior="lightbox">
                    <h2 class="title-heading">Analisa Perintisan Usaha Lele</h2>
                    <form class="meteor-form" method="POST" action="<?php echo base_url(); ?>" role="form" name="inputAI">
                        <p class="entry text">
                           <label for="panjang">Panjang lahan yang Anda miliki (Meter)</label>
                           <input type="number" step="any" min="0" max="1000" id="panjang" class="required" name="panjang" value="<?php echo ($this->session->userdata('panjang') != NULL) ? $this->session->userdata('panjang') : ''; ?>" placeholder="dalam satuan meter" style="width:100%"/>
                        </p><!-- .entry -->

                        <p class="entry text">
                            <label for="lebar">Lebar lahan yang Anda miliki (Meter)</label>
                            <input type="number" step="any" min="0" max="1000" id="lebar" class="required" name="lebar" value="<?php echo ($this->session->userdata('lebar') != NULL) ? $this->session->userdata('lebar') : ''; ?>" placeholder="dalam satuan meter" style="width:100%"/>
                        </p><!-- .entry -->
                        <div class="dual-container entry text clearfix">
                            <div class="half left">
                                <label for="material">Material yang Diinginkan</label>
                                <select id="material" name="material">
                                    <option value="m1" <?php echo ($this->session->userdata('material') != NULL && $this->session->userdata('material') == "m1") ? "selected" : ''; ?>>Bambu</option>
                                    <option value="m2" <?php echo ($this->session->userdata('material') != NULL && $this->session->userdata('material') == "m2") ? "selected" : ''; ?>>Bata</option>
                                </select>
                            </div><!-- .half -->
                            <div class="half right">
                                <label for="musim">Musim Saat ini</label>
                                <select id="musim" name="musim">
                                    <option value="s1" <?php echo ($this->session->userdata('musim') != NULL && $this->session->userdata('musim') == "s1") ? "selected" : ''; ?>>Kemarau</option>
                                    <option value="s2" <?php echo ($this->session->userdata('musim') != NULL && $this->session->userdata('musim') == "s2") ? "selected" : ''; ?>>Penghujan</option>
                                </select>
                            </div><!-- .half -->
                        </div><!-- .dual-container -->

                        <div class="entry checkbox">
                            <label>Konsentrasi pangan</label>
                            <label for="konsentrat"><input type="radio"1 id="konsentrat" name="pakan" value="f2" <?php echo ($this->session->userdata('pakan') != NULL && $this->session->userdata('pakan') == "f2") ? "checked" : ((!$this->session->userdata('pakan')) ? "checked" : ""); ?> /> Konsentrat</label>
                            <label for="daging"><input type="radio" id="daging" name="pakan" value="f3" <?php echo ($this->session->userdata('pakan') != NULL && $this->session->userdata('pakan') == "f3") ? "checked" : ''; ?> /> Daging</label>
                        </div><!-- .entry -->
                        <?php
                        if ($textarea == NULL) {
                            echo (' <p class="entry submit">
                                        <input type="submit" value="Proses Data" name="proses" data-size="medium"  class="btn meteor-button" style="width:100%"></button>
                                    </p>'
                            );
                        }
                        ?>
                        

                        <p class="textarea">
                            <label for="saran">Kiat Sukses Bisnis</label>
                            <textarea name="saran" class="autosize" rows="5" cols="10" readonly style="height:250px"><?php echo $textarea ?></textarea>
                        </p><!--.entry -->
                        <p class="entry submit">
                            <?php
                            if ($textarea != NULL) {
                                echo ('<input type="submit" value="Simpan Hasil" name="saveResult" data-size="small" class="btn meteor-button" style="width:50%"></button>');
                            }
                            ?>

                            <button style="float:right;"><a  href="<?php echo base_URL(); ?>">Bersihkan Form</a></button>
                        </p>
                    </form>
                </div><!-- .meteor-aside-posts -->

                <!-- + -->

                <div class="meteor-content post-content span8">
                    <h2 class="title-heading">Analisa yang Pernah Dijalankan Sistem</h2>
                    <table class="table table-striped lion" id="ReRe">
                                <!-- heading table -->
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:0%">Nomer</th>
                                    <th class="text-center" style="width:20%">Ukuran Kolam</th>
                                    <th class="text-center" style="width:20%">Material Kolam</th>
                                    <th class="text-center" style="width:15%">Musim</th>
                                    <th class="text-center" style="width:20%">Pilihan Pakan</th>
                                    <th class="text-center" style="width:20%">Rincian</th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($detail as $d) {   
                                ?>
                                <tr>
                                    <td class="text-center"><?= ++$i ?></td>
                                    <td class="text-center"><?= $d['ukuran_kolam'] ?></td>
                                    <td class="text-center"><?= $d['material_kolam'] ?></td>
                                    <td class="text-center"><?= $d['musim'] ?></td>
                                    <td class="text-center"><?= $d['pilihan_pakan'] ?></td>
                                    <td class="text-center"><a href="<?= base_url(). "index/viewDetail/".$d['id_hasil_analisa'] ?>">Detail</a></td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                                    
                            </table>
                </div><!-- .meteor-content -->

            </div><!-- .row -->
        </section><!-- .block -->

        <!-- + -->

        <footer role="contentinfo">
            <div class="bottom-bar">

                <div class="container">

                    <ul class="social-icons-mono tooltips clearfix" data-tooltip-options="animation: false, container: false, delay.show: 250, delay.hide: 80">
                        <li><a title="Pinterest" class="social-white-24 pinterest" href="#pinterest"></a></li>
                        <li><a title="Facebook" class="social-white-24 facebook" href="#facebook"></a></li>
                        <li><a title="Twitter" class="social-white-24 twitter" href="#twitter"></a></li>
                        <li><a title="Google Plus" class="social-white-24 google" href="#google-plus"></a></li>
                    </ul><!-- .social-icons-mono -->

                    <p>
                        Copyright &copy;2015 Mr. Catfish
                        &nbsp;|&nbsp; Create by <a href="http://null.co.id/">AkbarYuca @NullComputindo</a>
                    </p>

                </div><!-- .container -->

            </div><!-- .bottom-bar -->

        </footer>
        <!-- data Table -->
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/cssForTable/jquery.dataTables.min.js"></script>

        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.easing.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.color.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.touchwipe.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.autosize.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.imageloader.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/jquery/jquery.bootstrap-tooltip.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/underscore.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/underscore.string.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/backbone.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/hogan.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/lib/color.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.browser.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.cycle.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.ui.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.util.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.deps.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/core.responsive.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/client.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/client.header.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/client.media.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/core/js/client.load.js"></script>

        <!--[if IE]>
        <script type="text/javascript" src="core/js/legacy/ie.js?ver=1.0.45"></script>
        <![endif]-->


        <script type="text/javascript">
            (function ($) {

                // var defaultRoot = window.location.toString().split('/').slice(0, -1).join('/') + '/';

                // Core.version = "1.0.0";
                // Core.root = "" || defaultRoot;

                // Core.options = {
                //     native_video_support: true
                // }

            // //     Core.i18n = {
            // //         author: "Author",
            // //         admin: "Admin",
            // //         lt_minute_ago: "less than a minute ago",
            // //         abt_minute_ago: "about a minute ago",
            // //         minutes_ago: "%s minutes ago",
            // //         abt_hour_ago: "about an hour ago",
            // //         abt_hours_ago: "about %s hours ago",
            // //         one_day_ago: "1 day ago",
            // //         days_ago: "%s days ago"
            // //     }

                Core.initialize();
                $("#ReRe").DataTable();
            })(jQuery);

            
        </script>


        <!-- Live Preview Overrides -->

<!--
        <script type="text/javascript">
            document.FLICKR_API_URI = Hogan.compile(Core.util.format("%s/data/flickr-sample-data.json", Core.root));
            document.TWITTER_API_URI = Core.util.format("%s/data/twitter-sample-data.json", Core.root);
        </script>
-->
    </body>
</html>