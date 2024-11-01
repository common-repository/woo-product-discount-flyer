<?php
if (!defined('ABSPATH'))
exit;
function wooecommerceflyer_settings_callback() {
	$discountData = get_option('flyer_discounts');
	if(isset($_REQUEST['deleteDis']) && !empty($_REQUEST['deleteDis']) && isset($_GET['nonce']) && wp_verify_nonce($_GET['nonce'], 'wooecommerceflyer_dis_delete')){
		unset($discountData[$_REQUEST['deleteDis']]);
		update_option('flyer_discounts', $discountData);
		echo "<script>location.href='admin.php?page=flyer-settings'</script>";
	}
?>
	<h1 class="wp-heading-inline">Flyer Settings</h1>
	<div class="wrap">
		<form method="post" id="flyer_setting">	
			<div class="form-group">
				<label for="order_amount">Order Amount: </label>
				<input type="text" class="form-control" name="order_amount" required placeholder="Amount is greater then">
			</div>
			<div class="form-group">
				<label for="order_amount">Discount Percentage: </label>
				<input type="text" class="form-control" name="discount_percentage" required placeholder="Discount Percent">
			</div>
			<?php wp_nonce_field('wooecommerceflyer_set_discount'); ?>
			<div class="form-group">
				<input type="submit" name='setting_submit' value='Submit' class="button button-primary">
			</div>
		</form>
	</div>
<?php
	$discountData = get_option('flyer_discounts');
	if(!empty($discountData)) {
?>
	<h3>Flyer Discount</h3>
	<table class="bordered" >
		<thead>
			<tr>
				<th>Amount greater than</th>
				<th>Discount %</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($discountData as $amount => $discount) { ?>
			<tr>
				<td><?php echo $amount; ?></td>
				<td><?php echo $discount; ?></td>
				<td><a href="admin.php?page=flyer-settings&deleteDis=<?php echo $amount;?>&nonce=<?php echo wp_create_nonce('wooecommerceflyer_dis_delete');?>"><img width="16" src='<?php echo WOOECOMMERCEFLYER_PATH; ?>assets/images/delete.png'></a></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
<?php
	}
}


add_action('init','wooecommerceflyer_submit_setting');
function wooecommerceflyer_submit_setting(){
    if( isset($_POST['setting_submit']) && !empty($_POST['setting_submit']) && isset($_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'wooecommerceflyer_set_discount' )) {
		$amount = sanitize_text_field($_POST['order_amount']);
		$discount = sanitize_text_field($_POST['discount_percentage']);
		$flyerData = array($amount => $discount);
		$discountData = get_option('flyer_discounts');
		if(!empty($discountData)) {
			update_option('flyer_discounts', $flyerData + $discountData);
		} else {
			add_option('flyer_discounts', $flyerData);
		}
	}
}
