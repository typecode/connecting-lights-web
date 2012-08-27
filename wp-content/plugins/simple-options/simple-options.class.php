<?php

/**
 * All fields should extend this.
 */
abstract class SimpleOptionsField{
	/**
	 * Render the field.
	 *
	 * @param $name
	 * @param $id
	 * @param $value
	 * @param $field
	 */
	abstract static function render($name, $id, $value, $field);
	
	/**
	 * Validate and process the text field.
	 *
	 * @param $value
	 * @param $field
	 */
	static function validate($field, $value){
		return $value;
	}
	
	/**
	 * Convers the value as it comes from post into a value
	 * @static
	 * @param $post
	 */
	static function post_to_value($post){
		return $post;
	}

	/**
	 * An opportunity for fields to do miscellaneous processing.
	 *
	 * @param array $field
	 * @param string $page_name
	 * @param string $field_name
	 * @param mixed $value
	 */
	static function handle($field, $page_name, $field_name, $value){}
}

/**
 * The Text field
 */
class SimpleOptionsField_Text extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		?><input type="text" name="<?php print $name ?>" id="<?php print $id ?>" <?php if(!empty($value)) : ?>value="<?php print esc_attr($value) ?>"<?php endif ?> /><?php
	}
}

/**
 * The textarea field
 */
class SimpleOptionsField_Textarea extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		?><textarea name="<?php print $name ?>" id="<?php print $id ?>" class="widefat" rows="<?php print !empty($field['rows']) ? $field['rows'] : 3 ?>"><?php print esc_textarea($value) ?></textarea><?php
	}
}

/**
 * The number field
 */
class SimpleOptionsField_Number extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		SimpleOptionsField_Text::render($name, $id, $value, $field);
	}
	
	static function validate($field, $value){
		if(!is_numeric($value)) throw new Exception('This needs to be a number');
		return $value;
	}
}

/**
 * The checkbox field
 */
class SimpleOptionsField_Checkbox extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		?><input type="checkbox" name="<?php print $name ?>" id="<?php print $id ?>" <?php checked($value) ?> /><?php
		if(!empty($field['placeholder'])){ ?> <label for="<?php print $id ?>"><?php print $field['placeholder'] ?></label><?php }
	}
	
	static function validate($field, $value){
		return !empty($value);
	}
}

/**
 * The Select field
 */
class SimpleOptionsField_Select extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		$value = (array) $value;

		?>
		<select
			<?php if(!empty($field['multiple'])) print 'multiple="true"' ?>
			<?php if(!empty($field['placeholder'])) print 'data-placeholder="' . $field['placeholder'] . '"' ?>
			name="<?php print $name.(!empty($field['multiple']) ? '[]' : '') ?>"
			id="<?php print $id ?>">
	
			<?php if(empty($field['default'])) : ?><option></option><?php endif ?>
			<?php foreach($field['options'] as $k => $v) : ?>
			<option
				value="<?php print $k ?>" <?php selected(in_array($k, $value) || $value == $k); ?>><?php print $v ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}

/**
 * The taxonomy select field
 */
class SimpleOptionsField_TaxonomySelect extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		$terms = get_terms($field['taxonomy'], array(
			'count' => -1
		));

		if(is_array($value) && isset($value['order'])) $value = array_map('trim',explode(',', $value['order']));

		?>
	<select
		<?php if(!empty($field['multiple'])) print 'multiple="true"' ?>
		<?php if(!empty($field['placeholder'])) print 'data-placeholder="'.$field['placeholder'].'"' ?>
		<?php if(!empty($field['order'])) print 'data-order="true"' ?>
		name="<?php print $name . (!empty($field['multiple']) ? '[]' : '') ?>"
		id="<?php print $id ?>">

		<?php if(empty($field['default'])) : ?><option></option><?php endif ?>
		<?php foreach($terms as $term) : ?>
		<option value="<?php print $term->term_id ?>" <?php selected(in_array($term->term_id, $value) || $term->term_id == $value); ?>><?php print $term->name ?></option>
		<?php endforeach; ?>
	</select>

	<?php if(!empty($field['order'])) : ?>
		<input type="hidden" name="<?php print $name ?>[order]" value="<?php if(!empty($value) && is_array($value)) print implode(',', $value) ?>" />
		<?php endif; ?>
	<?php
	}
}

/**
 * The post select field
 */
class SimpleOptionsField_PostSelect extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		if(empty($field['query'])){
			$query = array(
				'numberposts' => -1,
			);
		}
		else $query = $field['query'];
		$posts = get_posts($query);

		if(is_array($value) && !empty($value['order'])) {
			$value = array_map('trim', explode(',', $value['order']));
		}
		else $value = (array) $value;

		?>
		<select
			<?php if(!empty($field['multiple'])) print 'multiple="true"' ?>
			<?php if(!empty($field['placeholder'])) print 'data-placeholder="' . $field['placeholder'] . '"' ?>
			<?php if(!empty($field['order'])) print 'data-order="true"' ?>
			name="<?php print $name . (!empty($field['multiple']) ? '[]' : '') ?>"
			id="<?php print $id ?>">
	
			<?php if(empty($field['default'])) : ?><option></option><?php endif ?>
			<?php foreach($posts as $post) : ?>
			<option
				value="<?php print $post->ID ?>" <?php selected(in_array($post->ID, $value) || $post->ID == $value); ?>><?php print $post->post_title ?></option>
			<?php endforeach; ?>
		</select>
	
		<?php if(!empty($field['order'])) : ?>
			<input type="hidden" name="<?php print $name ?>[order]"
				   value="<?php if(!empty($value) && is_array($value)) print implode(',', $value) ?>" />
			<?php endif; ?>
		<?php
	}
}

/**
 * The Media field
 */
class SimpleOptionsField_Media extends SimpleOptionsField{
	static function render($name, $id, $value, $field){
		if(is_array($value)){
			$value = $value['attachment_id'];
		}

		$attachment = get_post($value);
		if(!empty($attachment)){
			$img = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
			if($img[1] > 96 || $img[2] > 96){
				$img[1]/=2;
				$img[2]/=2;
			}
			?>
			<div class="current">
				<a href="<?php print add_query_arg(array('attachment_id' => $attachment->ID, 'action' => 'edit'), admin_url('media.php')) ?>" target="_blank">
					<img src="<?php print $img[0] ?>" width="<?php print round($img[1]) ?>" height="<?php print round($img[2]) ?>" />
				</a>
			</div>
			<div class="delete">
				<label><input type="checkbox" name="<?php print $name ?>[delete]"> <?php _e('Delete', 'origin') ?></label>
			</div>
			<?php
		}

		if(is_wp_error($value)) $value = null;

		?>
		<input type="hidden" class="media-upload-input" name="<?php print $name ?>[attachment_id]" value="<?php print esc_attr($value) ?>" />
		<input type="file" name="<?php print preg_replace('/[\[\]]+/', '_', $name) ?>upload" />
		<?php
	}
	
	static function handle($field, $page_name, $field_name, $value){
		if(!empty($_POST['options'][$page_name][$field_name]['delete']) && !empty($value)){
			$post = get_post($value);
			if(!empty($post) && $post->post_type == 'attachment' && empty($post->post_parent)){
				wp_delete_attachment($post->ID);
			}
			$_POST['options'][$page_name][$field_name] = $p = null;
		}

		$file_field = 'options_' . $page_name . '_' . $field_name . '_upload';
		if(!empty($_FILES[$file_field]['tmp_name'])){
			$attachment_id = media_handle_upload(
				$file_field,
				null,
				array('post_title' => $field['title'])
			);

			if(!is_wp_error($attachment_id)){
				// Delete the old attachment if it exists
				if(!empty($value) ){
					$post = get_post($value);
					if(!empty($post) && $post->post_type == 'attachment' && empty($post->post_parent)){
						wp_delete_attachment($post->ID);
					}
				}

				$_POST['options'][$page_name][$field_name] = $attachment_id;
			}
			else{
				throw new SimpleOptionsField_Exception($page_name, $field_name, 'Upload problem: '.$attachment_id->get_error_message());
			}
		}
	}
}

/**
 * A Origin options field exception.
 */
class SimpleOptionsField_Exception extends Exception{
	public $page;
	public $field;

	function __construct($page, $field, $message){
		parent::__construct($message);
		$this->page= $page;
		$this->field = $field;
		$this->message = $message;
	}

	function getPage(){
		return $this->page;
	}

	function getField(){
		return $this->field;
	}
}