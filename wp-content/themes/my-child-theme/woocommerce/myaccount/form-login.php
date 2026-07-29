<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_customer_login_form' );
$redirect = wc_get_page_permalink( 'myaccount' );
$mobile_number = '';

if ( isset( $_POST['mobile_number'] ) ) {
	$mobile_number = my_normalize_mobile_number(
		wp_unslash( $_POST['mobile_number'] )
	);
}

$is_mobile_verified = false;

if ( $mobile_number !== '' ) {
	$is_mobile_verified = (
		get_transient(
			my_mobile_verified_key( $mobile_number )
		) === '1'
	);
}
?>

<form class="woocommerce-form woocommerce-form-login login" id="myform" method="post">

```
<?php do_action( 'woocommerce_login_form_start' ); ?>

<?php if (
	isset( $_POST['next_step'], $_POST['mobile_number'] ) &&
	$_POST['next_step'] === 'add_username' &&
	$is_mobile_verified
): ?>

	<h2>نام کاربری انگلیسی برای خود انتخاب کنید</h2>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row-wide">

		<label for="username">
			نام کاربری:<span class="required">*</span>
		</label>

		<input
			type="text"
			class="woocommerce-Input woocommerce-Input--text input-text"
			name="username"
			id="username"
			value=""
			placeholder="English Username"
			autocomplete="off"
			onkeydown="return /[a-z]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13;"
		/>

	</p>

	<input
		type="hidden"
		name="next_step"
		value="do_login"
	/>

	<input
		type="hidden"
		name="mobile_number"
		value="<?php echo esc_attr( $mobile_number ); ?>"
	/>

<?php elseif (
	isset( $_POST['next_step'], $_POST['mobile_number'] ) &&
	(
		$_POST['next_step'] === 'verify_code' ||
		(
			$_POST['next_step'] === 'add_username' &&
			! $is_mobile_verified
		)
	)
): ?>

	<h2>تایید کد ارسالی</h2>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-row-wide">

		<label for="verification_code">
			کد پنج رقمی ارسالی به موبایل خود را وارد کنید
			&nbsp;<span class="required">*</span>
		</label>

		<input
			type="tel"
			class="woocommerce-Input woocommerce-Input--text input-text"
			name="verification_code"
			id="verification_code"
			value=""
			maxlength="5"
			onkeydown="return /[0-9]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13;"
		/>

	</p>

	<input
		type="hidden"
		name="next_step"
		value="add_username"
	/>

	<input
		type="hidden"
		name="mobile_number"
		value="<?php echo esc_attr( $mobile_number ); ?>"
	/>

<?php else: ?>

	<h2>ورود یا ثبت نام</h2>

	<p class="woocommerce-form-row woocommerce-form-row--wide form-first-row">

		<label for="mobile_number">
			شماره موبایل:
			&nbsp;<span class="required">*</span>
		</label>

		<input
			type="tel"
			name="mobile_number"
			id="mobile_number"
			placeholder="09123456789"
			pattern="09[0-9]{9}"
			maxlength="11"
			onkeydown="return /[0-9]/i.test(event.key) || event.which==8 || event.which==46 || event.which==37 || event.which==39 || event.which==13;"
		/>

	</p>

	<input
		type="hidden"
		name="next_step"
		value="verify_code"
	/>

	<?php do_action( 'woocommerce_login_form' ); ?>

<?php endif; ?>

<p class="form-row form-row-last">

	<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
	<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

	<input
		type="hidden"
		name="redirect"
		value="<?php echo esc_url( $redirect ); ?>"
	/>
	<button
		type="submit"
		class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"
		name="login"
		value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"
	>
		<?php esc_html_e( 'Log in', 'woocommerce' ); ?>
	</button>

</p>

<?php do_action( 'woocommerce_login_form_end' ); ?>
```

</form>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
