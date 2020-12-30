<?php
$this->load->view('layout/header');
?>
<!-- begin #page-container -->

<!-- begin login -->
<div class="login login-with-news-feed">
    <!-- begin news-feed -->
    <div class="news-feed">
        <div class="news-image">
            <img src="<?php echo base_url(); ?>assets/img/login-bg/bg1.jpg" data-id="login-cover-image" alt="" />
        </div>
         <div class="news-caption">
            <h4 class="caption-title"><i class="fa fa-diamond text-success"></i> <?php echo SEO_TITLE; ?></h4>
            <p>
               <?php echo SEO_DESC; ?>
            </p>
        </div>
    </div>
    <!-- end news-feed -->
    <!-- begin right-content -->
    <div class="right-content">
        <!-- begin login-header -->
        <div class="login-header" >
            <div class="brand">

                <img src="<?php echo SITE_LOGO; ?>" style="height: 70px;">  
                <small><?php echo SITE_NAME; ?> Admin Control Panel </small>
            </div>
            <div class="icon" >
                <i class="fa fa-sign-in"></i>
            </div>
        </div>
        <!-- end login-header -->
        <!-- begin login-content -->
        <div class="login-content">

            <div class="error1">
                <div class="error-code1 m-b-10 f_40">404 <i class="fa fa-warning"></i></div>
                <div class="error-content1">
                    <div class="error-message1">We couldn't find it...</div>
                    <div class="error-desc1 m-b-20">
                        The page you're looking for doesn't exist. <br />
                        Perhaps, there pages will help find what you're looking for.
                    </div>
                    <div>
                        <a href="<?php echo site_url("/");?>" class="btn btn-success">Go Back to Home Page</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- end login-content -->
    </div>
    <!-- end right-container -->
</div>
<!-- end login -->



<?php
$this->load->view('layout/footer');
?>
