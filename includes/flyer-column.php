<?php
if (!defined('ABSPATH'))
exit;
/**
 * @snippet       Add Column to Orders Table (e.g. Visited Product Flyer) - WooCommerce
 */
add_filter( 'manage_edit-shop_order_columns', 'wooecommerceflyer_add_new_order_admin_list_column', 2 );
 
function wooecommerceflyer_add_new_order_admin_list_column( $columns ) {
    $columns['product_flyer'] = 'Visited Product Flyer';
    return $columns;
}
 
add_action( 'manage_shop_order_posts_custom_column', 'wooecommerceflyer_add_new_order_admin_list_column_content' );
 
function wooecommerceflyer_add_new_order_admin_list_column_content( $column ) {

    global $post;
 	if ( 'product_flyer' === $column ) {

        $order = new WC_Order($post->ID);
        $discountData = get_option('flyer_discounts');
        $flyer_discounts = array();
        $flyer_amounts = array();
        foreach ($discountData as $amount => $discount) {
            array_push($flyer_discounts, $discount);
            array_push($flyer_amounts, $amount);
        }
        $size = count($flyer_amounts);
        $diff = abs($flyer_amounts[0] - $order->get_total());
        $dis = $flyer_discounts[0];

        if ($size > 0) {
            //loop through the rest of the array
            for ($i = 1; $i < $size; $i++) {
                $temp = abs($flyer_amounts[$i] - $order->get_total());
                if ($temp < $diff) {
                    //set new difference and closest element
                    $diff = $temp;
                    $dis = $flyer_discounts[$i];
                }
            }
            
        } 
        $ids = get_post_meta( $post->ID, 'last_product_ids', true);
        if(!empty($ids)) {
	        $product_ids = implode(',', $ids);
            $mailproducts = '['.$product_ids.']';
            $url = add_query_arg(
                [
                    'dis' => $dis,
                    'products'   => $product_ids,
                    'nonce'  => wp_create_nonce('wooecommerceflyer_pdf_flyer'),
                    'wooecommerceflyer-preview-pdf' => 1
                ],
                //WOOECOMMERCEFLYER_PATH."includes/export_pdf.php"
                get_site_url()
            );
	     	echo "<a href='" . esc_url($url) . "'><img src='".WOOECOMMERCEFLYER_PATH."assets/images/pdf.png'></a>";
            echo "<a href='javascript:void(0);' onclick='sendFlyerMail(".$mailproducts.",".$dis.",".$post->ID.")'><img src='".WOOECOMMERCEFLYER_PATH."assets/images/email.png'></a>";
	       	
	      }
    }
}
add_action( 'wp_ajax_wooecommerceflyer_on_mail', 'wooecommerceflyer_on_mail' );
add_action( 'wp_ajax_nopriv_wooecommerceflyer_on_mail', 'wooecommerceflyer_on_mail' );
function wooecommerceflyer_on_mail() {
    if(isset($_POST) && isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ajax_nonce']), 'ajax-nonce' )){
        $args = array(
            'post_type' => 'product',
            'include' => sanitize_text_field($_POST['products'])
        );
        $order = new WC_Order( sanitize_text_field($_POST['order_id']) );
        $email = $order->billing_email;
        $posts = get_posts($args);
        $content = '<html xmlns="http://www.w3.org/1999/xhtml">
            <head>
           
            <style type="text/css">
            body {
                margin: 0 !important;
                padding: 0 !important;
                -webkit-text-size-adjust: 100% !important;
                -ms-text-size-adjust: 100% !important;
                -webkit-font-smoothing: antialiased !important;
                font-family: "PT Sans", sans-serif;
            }
            img {
                border: 0 !important;
                outline: none !important;
            }
            p {
                Margin: 0px !important;
                Padding: 0px !important;
            }
            table {
                border-collapse: collapse;
                mso-table-lspace: 0px;
                mso-table-rspace: 0px;
            }
            td, a, span {
                border-collapse: collapse;
                mso-line-height-rule: exactly;
            }
            .ExternalClass * {
                line-height: 100%;
            }
             
            </style>
            </head><body style="margin:0px; padding:0px;" bgcolor="#ffffff">
            <!--Full width table start-->
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
              <tr>
                <td align="center">
                    <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="table-layout:fixed;">
                    <tr>
                      <td align="center" valign="top">
                        <table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
                            <tr>
                                <td valign="top" align="center" background="'.WOOECOMMERCEFLYER_PATH.'assets/images/background.jpg" width="100%" height="400" style="background-repeat:no-repeat; background:url('.WOOECOMMERCEFLYER_PATH.'assets/images/background.jpg);">
                                    <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tr><td>&nbsp;</td></tr>
                                            <tr><td>&nbsp;</td></tr>
                                            <tr>
                                                <td style="text-align: center; background: rgba(255,255,255,0.6);">
                                                    <h2 style="font-size: 64px; line-height: 68px; margin: 20px 0 25px; font-style: italic; font-weight: bold; font-family: "Fira Sans Extra Condensed", sans-serif; letter-spacing: 3px;"></h2>
                                                    <h4 style="font-size: 54px; line-height: 68px; font-weight: bold; margin: 0 0 25px;">'.sanitize_text_field($_POST['discount']).'%</h4>
                                                    <h5 style="text-transform: uppercase; font-size: 24px; letter-spacing: 3px; font-weight: bold;">Discount On</h5>
                                                </td>
                                            </tr>
                                        </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="center" >
                                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" style="margin-top: 20px; margin-bottom: 20px;">
                                        <tr>';
        foreach( $posts as $post ) {
            $product = wc_get_product( $post->ID );
                    $content .= '<td align="center" valign="top" width="280" style="text-align: center;">
                                    <a href="'.get_permalink($post->ID).'/?dis='.base64_encode($_POST['discount']).'" target="_blank" style="text-decoration: none;">
                                    <table width="280" border="0" cellspacing="0" cellpadding="0" align="center">
                                        <tr>
                                            <td align="center" valign="top">
                                                <img src="'.get_the_post_thumbnail_url( $post->ID, 'full' ).'" alt="" title="" style="width: 100%; height: auto; max-width: 160px;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <h5 style=" font-size: 20px; font-weight: bold; margin: 20px 0;">'.$post->post_title.'</h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="font-size: 16px; line-height: 24px; font-weight: bold;">Price:&nbsp;<span>&euro;'.$product->get_regular_price().'</span></p>
                                            </td>
                                        </tr>
                                    </table>
                                    </a>
                                </td>';
        }
                    $content .= '</tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
            </body>
            </html>';
        $to = $email;
        $subject = "Discount On Products";
        $mailResult = false;
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . get_option('blogname') . ' <'. get_option('admin_email') .'>';
        add_action('phpmailer_init', 'EmbedImageInMail');
        $mailResult = wp_mail($to, $subject, $content, $headers);
        remove_action('phpmailer_init', 'EmbedImageInMail');
        echo $mailResult;
        exit;
    }
}