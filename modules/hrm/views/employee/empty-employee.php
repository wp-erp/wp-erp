<style>
    #erp-employee-new{
        display: none;
    }
    img.employee-list {
        width: 100%;
        max-width: 650px;
        margin: 50px auto 0;
        display: block;
    }

    .tmpl-buttons {
        text-align: center;
    }

    .tmpl-container .tmpl-buttons a {
        display: inline-block;
        padding: 10px 20px;
        height: auto;
        margin: 10px;
        font-size: 18px;
    }

    .feature-headline {
        font-size: 20px;
        color: #23282d;
        text-align: center;
        line-height: 2;
    }

    .features-box {
        max-width: 768px;
        margin: auto;
    }

    .feature-box {
        float: left;
        width: 33.33333%;
    }

    .feature-content {
        max-width: 230px;
        /*padding: 0 15px;*/
        margin: auto;
    }

    .feature-box-img {
        max-width: 230px;
        width: 100%;
        display: block;
        margin: 10px auto;
    }

    .feature-content h3 {
        color: #23282d;
    }

    .feature-box p {
        color: #919191;
    }

    /**
	 * Media quries
	 */

    @media (max-width: 767px) {

        .feature-box {
            width: 50%;
        }

    }

    @media (max-width: 575px) {

        .feature-headline {
            line-height: 28px;
            padding-top: 20px;
        }

        .feature-box {
            width: 100%;
        }

        .feature-content,
        .feature-box-img {
            max-width: 350px;
        }

        .feature-title {
            text-align: center;
        }

    }

</style>
<div class="tmpl-container">

    <img class="employee-list" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/employee-list@2x.png' ); ?>"
         alt="Employee List">

    <div class="tmpl-buttons">
        <a href="#" onclick="jQuery('#erp-employee-new').click()"
           class="button button-primary"><?php esc_html_e( 'Add your first employee!', 'erp' ); ?></a>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=erp-tools&tab=import' ) ); ?>"
           class="button"><?php esc_html_e( 'Import employee from CSV', 'erp' ); ?></a>
    </div>

    <h2 class="feature-headline"><?php esc_html_e( 'What You Can Do With A Complete HR Solution', 'erp' ); ?></h2>

    <ul class="features-box">

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/employee-details@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Employee details', 'erp' ); ?></h3>
                <p><?php esc_html_e( 'WP ERP allows you to create employees and organize all necessary information.', 'erp' ); ?></p>
            </div>
        </li>

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/notes@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Notes', 'erp' ); ?></h3>
                <p><?php esc_html_e( 'You can also take notes on each employee and highlight important facts that you have noticed.', 'erp' ); ?></p>
            </div>
        </li>

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/performance@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Performance', 'erp' ); ?> </h3>
                <p><?php esc_html_e( 'Review your employee performance, comments and measure their activities individually.', 'erp' ); ?></p>
            </div>
        </li>

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/departments@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Departments', 'erp' ); ?></h3>
                <p><?php esc_html_e( 'Create and organize departments for your business or company so that you can accommodate employees.', 'erp' ); ?></p>
            </div>
        </li>

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/announcement@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Announcement', 'erp' ); ?></h3>
                <p><?php esc_html_e( 'Publish important announcements for your company or individual employees.', 'erp' ); ?></p>
            </div>
        </li>

        <li class="feature-box">
            <img class="feature-box-img" src="<?php echo esc_html( WPERP_HRM_ASSETS . '/images/reports@2x.png' ); ?>"
                 alt="Feature Image">
            <div class="feature-content">
                <h3 class="feature-title"><?php esc_html_e( 'Reports', 'erp' ); ?></h3>
                <p><?php esc_html_e( 'Generate useful reports to get detailed analytics on your company, employees, departments etc.', 'erp' ); ?></p>
            </div>
        </li>

    </ul> <!-- .features-box -->

</div> <!-- .tmpl-container -->

