<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_user_logged_in() ) {
	return;
}

?>
<form class="woocommerce-form woocommerce-form-login login" id="myform" method="post">
	<?php do_action( 'woocommerce_login_form_start' ); ?>
	<?php if (isset ($_POST["next_step"], $_POST["mobile_number"]) and $_POST["next_step"] == "add_username"): ?>
			<h2><?php echo ('نام کاربری انگلیسی برای خود انتخاب کنید'); ?></h2>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row-wide">
				<label for="username">نام کاربری:<span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="" 
					placeholder="English Username" autocomplete="false" 
					onkeydown="return /[a-z]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13 ;"  />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row-wide">
				<input type="hidden" name="next_step" id="next_step" value = "do_login"/>
				<input type="hidden" name="mobile_number" id="mobile_number" value= "<?php esc_html_e ( $_POST['mobile_number']); ?>"/>
			</p>
	<?php elseif (isset ($_POST["next_step"],$_POST["mobile_number"]) and $_POST["next_step"] == "verify_code"): ?>
		<h2><?php echo ('تایید کد ارسالی'); ?></h2>
		<?php 
		$mobile_number = $_POST["mobile_number"];
		$verification_code = mt_rand (10000,99999);
		$_SESSION['verification_code'] = $verification_code;
		//!!! echo '<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">verification code is: '.$verification_code.'</p>';
		 SendVerificationCode($mobile_number, $verification_code);
		?>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row-wide">
				<label for="verification_code"><?php echo( 'کد پنج رقمی ارسالی به موبایل خود را وارد کنید'); ?>&nbsp;<span class="required">*</span></label>
				<input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="verification_code" id="verification_code" value="" maxlength=5 
				onkeydown="return /[0-9]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13 ;" />
			</p>
			<input type="hidden" name="next_step" id="next_step" value = "add_username" />
			<input type="hidden" name="mobile_number" id="mobile_number" value= "<?php esc_html_e ($mobile_number); ?>"/>

		<?php // else: ?>
		
		<?php //endif; ?>
	<?php else: ?>
		<h2><?php echo ('ورود یا ثبت نام'); ?></h2>
		<p class="woocommerce-form-row woocommerce-form-row--wide form-first-row ">
			<label for="mobile_number"><?php echo ('شماره موبایل:'); ?>&nbsp;<span class="required">*</span></label>
			<input type="tel" name="mobile_number" id="mobile_number" placeholder="09123456789" pattern = "09[0-9]{9}" maxlength=11 
			onkeydown="return /[0-9]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13 ;" /> <?php // @codingStandardsIgnoreLine ?>
		</p>
		<input type="hidden" name="next_step" id="next_step" value = "verify_code"/>

		
		<?php do_action( 'woocommerce_login_form' ); ?>

	<?php endif; ?>
		<p class="form-row form-row-last">
			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"><?php esc_html_e( 'Log in', 'woocommerce' ); ?></button>
		</p>
	<?php do_action( 'woocommerce_login_form_end' ); ?>

</form>


