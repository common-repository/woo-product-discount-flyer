function sendFlyerMail(products, discounts, order_id) {
    var data = {
        'action': 'wooecommerceflyer_on_mail',
        'products': products,
        'discount': discounts,
        'order_id': order_id,
        'ajax_nonce': ajax_object.nonce
    };
    jQuery.post(ajax_object.ajax_url, data, function (response) {
    });
}