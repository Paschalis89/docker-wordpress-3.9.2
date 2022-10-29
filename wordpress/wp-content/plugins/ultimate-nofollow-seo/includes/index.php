<?php
function hgt_seo_my_plugin_options() {
	$get_form_seo_data = get_option( 'hgt_seo_form_data');
	if ( isset( $_POST["submit-button"] ) ) {
		$retrieved_nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce( $retrieved_nonce, 'add_nofollow_form' ) ) {
			die('Failed security check');
		}
		$form_data_array = [];
		$selector = sanitize_text_field ( trim ( $_POST['selector'] )  );
		$selector_name = sanitize_text_field ( trim ( $_POST['selector-name'] ) );
		$url = esc_url ( trim (  $_POST['url'] ) );
		$reverse = ( isset( $_POST['reverse-field'] ) ) ? $_POST['reverse-field'] : 'off';
		$form_data  = $selector.','.$selector_name.','.$url.','.$reverse;
		if ( $get_form_seo_data != false ) {
			array_push($get_form_seo_data, $form_data);
			update_option( 'hgt_seo_form_data', $get_form_seo_data );
		} else {
			array_push($form_data_array, $form_data );
			add_option( 'hgt_seo_form_data', $form_data_array, '', 'yes' );
		}
	} //end if
?>
<div class="hgt-uns-container-fluid">
	<div class="hgt-uns-row">
	<div class="heygoto-packages-wrapper no-show">
		<a href="https://heygotomarketing.com/affordable-local-seo-marketing-packages/" target="_blank">
			<img src="<?php echo plugins_url('assets/heygoto-banner.png', __DIR__ ); ?>" alt="HeyGoTo banner">
		</a>
		<span id="close-heygoto-packages-wrapper"> X </span>
	</div>
	<h1> Ultimate NoFollow SEO Plugin </h1>
	<div class="hgt-uns-col-xs-4">
		<h2>Add NoFollow to Links</h2>
		<form action="" method="POST" id="hgt-seo-plugin-form">
			<div class="hgt-seo-form-wrapper">
				<div class="single-block">
					<hr>
					<label for="">How do you want to target your links?</label>
					<select name="selector" id="selector" class="selector">
						<option value="tag">HTML Tag</option>
						<option value="id">Id</option>
						<option value="class">Class</option>
					</select>

					<label for="">What is the name of your HTML tag or selector?</label>
					<input type="text" name="selector-name" id="selector-name" class="selector-name" required>
					
					<label for="">What is the base URL you want to add NoFollow to?</label>
					<input type="text" name="url" id="url" class="url" required>
					
					<input type="checkbox" name="reverse-field" value="on"><span>Reverse</span>
				</div> 		<!-- Ending single block -->
			</div> 		<!-- Ending hgt-seo-form-wrapper -->
			<?php wp_nonce_field('add_nofollow_form'); ?>
			<button name="submit-button" type="submit" id="hgt-seo-plugin-form-submit">Add NoFollow to Links</button>
		</form>
	</div> 		<!-- Ending col-xs-4 -->
	<div class="hgt-uns-col-xs-8">
		<h2>Manage Your NoFollow Links</h2>
		<hr id="all-records-divider">
		<table id="hgt-seo-all-records">
			<thead>
				<tr>
					<th>Id</th>
					<th>Selector</th>
					<th>Base URL</th>
					<th>Reverse</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
			<?php
			if ( get_option('hgt_seo_form_data')  != false  ) {
			$get_form_seo_data = get_option('hgt_seo_form_data');
			$count = count ( $get_form_seo_data );
			for ( $i = 0 ; $i < $count ; $i++ ) {				
				$current_section_form_data = $get_form_seo_data[ $i ];
				$current_section_form_data = explode ( ',', $current_section_form_data );
			?>
			<tr>
				<td> <?= $i ?> </td>
				<td>
					<strong><?= ( isset( $current_section_form_data) && $current_section_form_data[0] ) ? $current_section_form_data[0] : null ?> </strong> : 
					<?= ( isset( $current_section_form_data) && $current_section_form_data[1] ) ? $current_section_form_data[1] : null ?>
				 </td>
				<td> <?= ( isset( $current_section_form_data) && $current_section_form_data[2] ) ? $current_section_form_data[2] : null ?> </td>
				<td> <?= ( isset( $current_section_form_data) && $current_section_form_data[3] ) ? $current_section_form_data[3] : 'off' ?> </td>
				<td>
					<a href="<?= $i; ?>" id="edit-form-value">Edit</a>
					<a href="<?= $i; ?>" id="delete-form-value"> Delete</a>
				</td>
			</tr> 
			<?php 
				}
			} else {
				echo '<span class="no-data-error">No records found</span>';
			}
			?>

			</tbody>
		</table>
	</div> 		<!-- Endign col-xs-8 -->
	</div> 		<!-- Endign row -->
	<form action="" id="update-form">
		<label for="">How do you want to target your links?</label>
		<select name="uns_selector" id="uns-selector">
			<option value="tag">HTML Tag</option>
			<option value="id">Id</option>
			<option value="class">Class</option>
		</select>
		<label for="">What is the name of your HTML tag or selector?</label>
		<input type="text" name="uns_element" id="uns-element">
		<label for="">What is the name of your HTML tag or selector?</label>
		<input type="text" id="uns-baseurl" name="uns_baseurl">	
		<input type="checkbox" value="on" id="uns-reserve" name="uns_reverse"> <span class="reversed">Reverse</span>
		<input type="hidden" id="uns-id" name="uns_id">
		<div class="buttons">
			<button id="uns-form-update-btn">Update</button>
			<button id="uns-form-cancel-btn">Cancel</button>
		</div>
	</form>
</div> 		<!-- Endign continaer -->
<?php
} //wp-plugin-options | Function
?>