<?php
function wpbc_contacts_page_handler()
{
    global $wpdb;

    $table = new Custom_Table_Example_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'wpbc'), count($_REQUEST['id'])) . '</p></div>';
    }
    ?>
<div class="wrap">

    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Mis banner', 'wpbc')?> <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts_form');?>"><?php _e('Nuevo Banner', 'wpbc')?></a>
    </h2>
    <?php echo $message; ?>

    <form id="contacts-table" method="POST">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
        <?php $table->display() ?>
    </form>

</div>
<?php
}


function wpbc_contacts_form_page_handler()
{
    global $wpdb;
    $table_name = $wpdb->prefix . '8a'; 

    $message = '';
    $notice = '';


    $default = array(
        'id' => 0,
        'name'      => '',
        'description'  => '',
        'image'     => '',
        'promocion' =>'',
        'ocultar' => '',
    );


    if ( isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
        
        $item = shortcode_atts($default, $_REQUEST);     

        $item_valid = wpbc_validate_contact($item);
        if ($item_valid === true) {
            if ($item['id'] == 0) {
                $result = $wpdb->insert($table_name, $item);
                $item['id'] = $wpdb->insert_id;
                if ($result) {
                    $message = __('El Banner se cargó correctamente', 'wpbc');
                } else {
                    $notice = __('Hubo un error mientras se guardaba el Banner', 'wpbc');
                    print($result);
                }
            } else {
                $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                if ($result) {
                    $message = __('El Banner se actualizó correctamente', 'wpbc');
                } else {
                    $notice = __('Hubo un error mientras se actualizaba el Banner', 'wpbc');
                }
            }
        } else {
            
            $notice = $item_valid;
        }
    }
    else {
        
        $item = $default;
        if (isset($_REQUEST['id'])) {
            $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
            if (!$item) {
                $item = $default;
                $notice = __('Item not found', 'wpbc');
            }
        }
    }

    
    add_meta_box('contacts_form_meta_box', __('Información para el banner', 'wpbc'), 'wpbc_contacts_form_meta_box_handler', 'contact', 'normal', 'default');

    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2><?php _e('Banner', 'wpbc')?> <a class="add-new-h2"
                                href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=contacts');?>"><?php _e('Volver', 'wpbc')?></a>
    </h2>

    <?php if (!empty($notice)): ?>
    <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif;?>
    <?php if (!empty($message)): ?>
    <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif;?>

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        
        <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    
                    <?php do_meta_boxes('contact', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Guardar', 'wpbc')?>" id="submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
<?php
}

function wpbc_contacts_form_meta_box_handler($item)
{
    ?>
<tbody >
		
	<div class="formdatabc">		
		
    <form >
		<div class="form2bc">
        <p>			
		    <label for="name"><?php _e('Nombre del banner', 'wpbc')?></label>
		<br>	
            <input id="name" name="name" type="text" value="<?php echo esc_attr($item['name'])?>"
                    required>
		</p><p>	
		</div>
        <div>		
			<p>
		    <label for="description"><?php _e('Descripción:', 'wpbc')?></label> 
		<br>
            <textarea id="description" name="description" cols="35" rows="3" maxlength="240"><?php echo esc_attr($item['description'])?></textarea>
		</p><p>  
           
		</div>	
		<div class="form2bc">
		<p>
      <p>	  
            <label for="image"><?php _e('imagen:', 'wpbc')?></label> 
		<br> 
       
		<input id="upload-button" type="button" class="button" value="Upload Image"  />
        <input id="image" type="text" name="image"   value="<?php echo esc_attr($item['image'])?> " style="border:none;display: block;"/>

        </p>
		</div> 
		
        <div class="form2bc"> 
		<p>
      <p>	  
            <label for="ocultar"><?php _e('¿ Es visible ?:', 'wpbc')?></label> 
		<br>
			<input id="image" name="image" type="text" value="<?php echo esc_attr($item['image'])?>">
		</p>
		</div>
		<!--Paquete de promocion-->
        <div class="form2bc">
            <label for="">Promoción</label>
            <select id="promocion" name='promocion'>
            <option value='Mostrar'>Promocionar</option>
            <option value='Ocultar'>No promocionar</option>
            </select>
            <br>
		</div>	
        <!--Paquete de ocultar-->
        <div class="form2bc">
            <label for="">Ocultar</label>
            <select id="ocultar" name='ocultar'>
            <option value='Mostrar'>Sí</option>
            <option value='Ocultar'>No</option>
            </select>
            <br>
		</div>	
        <!--Paquete , imagen-->
        <div class="form2bc">
            <label for="">Imagen</label>
            <input type="file" accept="image/*" name="imagen" placeholder="" id="imagen" class="form-control-file" >
            <br>   
        </div>
        
		</form>
		</div>
</tbody>
<?php
}
/*
<!--
    Listar los banner           x 
    Cambiar contacto por banner x
    Nuevo banner *

    Los botones ocultar         x
                /promocion      x
                /imagen
    --->
    */