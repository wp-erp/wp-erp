<?php
namespace WeDevs\ERP;

/**
 * People Class
 */
class Upload {
	function upload_file( $image_only = false ) {

        // check if guest post enabled for guests
        if ( ! is_user_logged_in() ) {
            die( 'error' );
        }

        $upload = array(
            'name'     => $_FILES['file']['name'],
            'type'     => $_FILES['file']['type'],
            'tmp_name' => $_FILES['file']['tmp_name'],
            'error'    => $_FILES['file']['error'],
            'size'     => $_FILES['file']['size']
        );

        header('Content-Type: text/html; charset=' . get_option('blog_charset'));

        $attach = $this->handle_upload( $upload );

        if ( $attach['success'] ) {

            $response = array( 'success' => true );

            if ($image_only) {
                // $image_size = erp_get_option( 'insert_photo_size', 'erp_general', 'thumbnail' );
                // $image_type = wpuf_get_option( 'insert_photo_type', 'wpuf_general', 'link' );

                // if ( $image_type == 'link' ) {
                //     $response['html'] = wp_get_attachment_link( $attach['attach_id'], $image_size );
                // } else {
                    //$response['html'] = wp_get_attachment_image( $attach['attach_id'], $image_size );
                    $response['html'] = $this->attach_html( $attach['attach_id'] );
               // }

            } else {
                $response['html'] = $this->attach_html( $attach['attach_id'] );
            }

            return $response['html'];
        } else {
            return 'error';
        }


        // $response = array('success' => false, 'message' => $attach['error']);
        // echo json_encode( $response );
        exit;
    }

    /**
     * Generic function to upload a file
     *
     * @param string $field_name file input field name
     * @return bool|int attachment id on success, bool false instead
     */
    function handle_upload( $upload_data ) {

        $uploaded_file = wp_handle_upload( $upload_data, array('test_form' => false) );

        // If the wp_handle_upload call returned a local path for the image
        if ( isset( $uploaded_file['file'] ) ) {
			
			$file_loc  = $uploaded_file['file'];
			$file_name = basename( $upload_data['name'] );
			$file_type = wp_check_filetype( $file_name );

            $attachment = array(
				'post_mime_type' => $file_type['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
            );

			$attach_id   = wp_insert_attachment( $attachment, $file_loc );
			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return array('success' => true, 'attach_id' => $attach_id);
        }

        return array('success' => false, 'error' => $uploaded_file['error']);
    }

    public static function attach_html( $attach_id, $type = NULL ) {

        $attachment = get_post( $attach_id );

        if ( ! $attachment ) {
            return;
        }

        if (wp_attachment_is_image( $attach_id)) {
            $image = wp_get_attachment_image_src( $attach_id, 'thumbnail' );
            $image = $image[0];
        } else {
            $image = wp_mime_type_icon( $attach_id );
        }

        $html = '<li class="erp-image-wrap thumbnail">';
        $html .= sprintf( '<div class="attachment-name"><img src="%s" alt="%s" /></div>', $image, esc_attr( $attachment->post_title ) );

        // if ( wpuf_get_option( 'image_caption', 'wpuf_general', 'off' ) == 'on' ) {
        //     $html .= '<div class="wpuf-file-input-wrap">';
        //     $html .= sprintf( '<input type="text" name="wpuf_files_data[%d][title]" value="%s" placeholder="%s">', $attach_id, esc_attr( $attachment->post_title ), __( 'Title', 'wpuf' ) );
        //     $html .= sprintf( '<textarea name="wpuf_files_data[%d][caption]" placeholder="%s">%s</textarea>', $attach_id, __( 'Caption', 'wpuf' ), esc_textarea( $attachment->post_excerpt ) );
        //     $html .= sprintf( '<textarea name="wpuf_files_data[%d][desc]" placeholder="%s">%s</textarea>', $attach_id, __( 'Description', 'wpuf' ), esc_textarea( $attachment->post_content ) );
        //     $html .= '</div>';
        // }

        $html .= sprintf( '<input type="hidden" name="erp_files[%s][]" value="%d">', $type, $attach_id );
        $html .= sprintf( '<div class="caption"><a href="#" class="btn btn-danger btn-small attachment-delete" data-attach_id="%d">%s</a></div>', $attach_id, __( 'Delete', 'wp-erp' ) );
        $html .= '</li>';

        return $html;
    }

    function delete_file() {
        check_ajax_referer( 'erp_nonce', 'nonce' );

        $attach_id = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
        $attachment = get_post( $attach_id );

        //post author or editor role
        if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
            wp_delete_attachment( $attach_id, true );
            echo 'success';
        }

        exit;
    }
}