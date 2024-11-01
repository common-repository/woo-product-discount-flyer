<?php


if (!defined('ABSPATH'))
    exit;

if (!class_exists('TCPDF')) {
    require_once(WOOECOMMERCEFLYER_DIR . '/includes/tcpdf/tcpdf.php');
}




if (isset($_GET['products']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'wooecommerceflyer_pdf_flyer')) {
    ob_start();
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    // set font
    $pdf->SetFont('dejavusans', '', 10);
    // add a page
    $pdf->AddPage();
    $img_file = WOOECOMMERCEFLYER_PATH . 'assets/images/background.jpg';
    $pdf->Image($img_file, 12, 10, 0, 0, '', '', '', false, 0, '', false, false, 0);
    $args = array(
        'post_type' => 'product',
        'include' => sanitize_text_field($_GET['products'])
    );
    $posts = get_posts($args);
    $content = '<head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0 " />
                <meta name="format-detection" content="telephone=no" />
                
                </head>
            <body style="margin:0px; padding:0px;" bgcolor="#ffffff">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">
              <tr>
                <td align="center">
                    <table width="600" border="0" cellspacing="0" cellpadding="0" align="center" style="table-layout:fixed;">
                    <tr>
                      <td align="center" valign="top">
                        <table width="600" border="0" cellspacing="0" cellpadding="0" align="center">
                            <tr>
                                <td valign="top" align="center" width="100%" height="400" style="background-repeat:no-repeat; background:url(' . WOOECOMMERCEFLYER_PATH . 'assets/images/background.jpg);">
                                        <table width="500" border="0" cellspacing="0" cellpadding="0" align="center">
                                            <tr><td>&nbsp;</td></tr>
                                            <tr><td>&nbsp;</td></tr>
                                            <tr>
                                                <td style="text-align: center; background: rgba(255,255,255,0.6); height: 292px;">
                                                    <h4 style="font-size: 54px; line-height: 68px; font-weight: bold; margin: 25px 0;">' . sanitize_text_field($_REQUEST['dis']) . '%</h4>
                                                    <h5 style="text-transform: uppercase; font-size: 24px; letter-spacing: 3px; font-weight: bold; margin: 20px 0;">Discount On</h5>
                                                </td>
                                            </tr>
                                        </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="center" >
                                    <table width="600" border="0" cellpadding="0" cellspacing="0" align="center" style="margin-top: 20px; margin-bottom: 20px;">
                                        <tr>';
    foreach ($posts as $post) {
        $product = wc_get_product($post->ID);
        $content .= '<td align="center" valign="top" width="240" style="text-align: center;">
                                    <table width="280" border="0" cellspacing="0" cellpadding="0" align="center">
                                        <tr>
                                            <td align="center" valign="top" width="280" >
                                                <img class="product-img" src="' . get_the_post_thumbnail_url($post->ID, 'full') . '" alt="" title="" width="100" height="100" border="0">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top" width="280" >
                                                <h5 style=" font-size: 20px; font-weight: bold; margin: 20px 0;">' . $post->post_title . '</h5>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top" width="280" >
                                                <p style="font-size: 16px; line-height: 24px; font-weight: bold;">Price:&nbsp;<span>&euro;' . $product->get_regular_price() . '</span></p>
                                            </td>
                                        </tr>
                                    </table>
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
    if (!empty($posts)) {
        // output the HTML content
        $pdf->writeHTML($content, true, false, true, false, '');
    }
    //Close and output PDF document
    $pdf->Output('recent_product_flyer.pdf', 'D');
    ob_end_flush();
    readfile('recent_product_flyer.pdf');
    $file = "recent_product_flyer.pdf";

    // header("Content-Type: application/pdf");
    // header("Content-Description: File Transfer");
    // header("Content-Disposition: attachment; filename=" . urlencode($file));
    // header("Content-Length: " . filesize($file));
    // header("Cache-control: private");
    exit;
}
