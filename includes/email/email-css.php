<?php
/**
 * Email Styles
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load colours
$bg              = get_option( 'erp_email_background_color', '#f5f5f5' );
$body            = get_option( 'erp_email_body_background_color', '#ffffff' );
$base            = get_option( 'erp_email_base_color', '#444444' );
$base_text       =  '#202020';
$text            = get_option( 'erp_email_text_color', '#444444' );

$text_lighter_20 = '#555555';

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
#wrapper {
    background-color: <?php echo esc_attr( $bg ); ?>;
    margin: 0;
    padding: 70px 0 20px 0;
    -webkit-text-size-adjust: none !important;
    width: 100%;
}

.button {
    background-color:#4CAF50;
    border-radius:3px;
    color:#ffffff;
    display:inline-block;
    font-family:sans-serif;
    font-size:13px;
    font-weight:bold;
    line-height: 150%;
    text-align:center;
    text-decoration:none;
    -webkit-text-size-adjust:none;
    padding: 8px 20px;
}

.button.sm {
    padding: 5px 10px;
}

.button.green {
    background-color: #4CAF50;
}

.button.orange {
    background-color: #FF9800;
}

.button.blue {
    background-color: #2196F3;
}

#template_container {
    background-color: <?php echo esc_attr( $body ); ?>;
    border: 1px solid #eee;
    margin-bottom: 25px;
}

#template_header {
    color: #444;
    border-bottom: 0;
    font-weight: bold;
    line-height: 100%;
    vertical-align: middle;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_footer td {
    padding: 0;
}

#template_footer #credit {
    border:0;
    color: #999;
    font-family: Arial;
    font-size:12px;
    line-height:125%;
    text-align:center;
    padding: 0 48px 48px 48px;
}

#body_content {
    background-color: <?php echo esc_attr( $body ); ?>;
}

#body_content table td {
    padding: 30px 48px 48px 48px;
}

#body_content table td td {
    padding: 12px;
}

#body_content table td th {
    padding: 12px;
}

#body_content p {
    margin: 0 0 20px;
}

#body_content_inner {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 15px;
    line-height: 170%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.td {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    border: none;
}

.text {
    color: <?php echo esc_attr( $text ); ?>;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

.link {
    color: <?php echo esc_attr( $base ); ?>;
}

#header_wrapper {
    padding: 36px 48px 0;
    display: block;
}

h1 {
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 30px;
    font-weight: 300;
    line-height: 150%;
    margin: 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
    -webkit-font-smoothing: antialiased;
}

h2 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 18px;
    font-weight: bold;
    line-height: 130%;
    margin: .5em 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

h3 {
    color: <?php echo esc_attr( $base ); ?>;
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
    line-height: 130%;
    margin: .5em 0;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#body_content_inner h1 {
    margin: 0 0 .5em 0;
}

#body_content_inner p + h1,
#body_content_inner p + h2,
#body_content_inner p + h3,
#body_content_inner p + h4 {
    margin-top: 2em;
}

a {
    color: <?php echo esc_attr( $base ); ?>;
    font-weight: normal;
    text-decoration: underline;
}

img {
    border: none;
    display: inline;
    font-size: 14px;
    font-weight: bold;
    height: auto;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    text-transform: capitalize;
}
