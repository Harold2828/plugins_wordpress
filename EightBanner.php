<?php
/**
* Plugin Name: Banner para mercado repuesto
* Description: Este plugin esta elaborado para mercado respuesto, ideal para crear banners.
* Version:     1.5
* Plugin URI: https://www.eaigtha.com
* Author:      Code8a
* Author URI: https://www.eaigtha.com
* License:     GPLv1 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wpbc
* Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( '¡Sin trampas!' );

require plugin_dir_path( __FILE__ ) . 'includes/metabox-p1.php';

function wpbc_custom_admin_styles() {
    wp_enqueue_style('custom-styles', plugins_url('/css/styles.css', __FILE__ ));
	}
add_action('admin_enqueue_scripts', 'wpbc_custom_admin_styles');


function wpbc_plugin_load_textdomain() {
load_plugin_textdomain( 'wpbc', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'wpbc_plugin_load_textdomain' );


global $wpbc_db_version;
$wpbc_db_version = '1.1.0'; 


function wpbc_install()
{
    global $wpdb;
    global $wpbc_db_version;

    $table_name = $wpdb->prefix . '8a'; 


    $sql = "CREATE TABLE " . $table_name . " (
        id int(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR (100) NOT NULL,
        description VARCHAR (100) NOT NULL,
        image VARCHAR (100) NOT NULL,
        promocion VARCHAR (20) NOT NULL,
        ocultar VARCHAR (20) NOT NULL,
        PRIMARY KEY  (id)
    );";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    add_option('wpbc_db_version', $wpbc_db_version);

    $installed_ver = get_option('wpbc_db_version');
    if ($installed_ver != $wpbc_db_version) {
        $sql = "CREATE TABLE " . $table_name . " (
            id int(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR (100) NOT NULL,
            description VARCHAR (100) NOT NULL,
            image VARCHAR (100) NOT NULL,
            promocion VARCHAR (20) NOT NULL,
            ocultar VARCHAR (20) NOT NULL,
            PRIMARY KEY  (id)
        );";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('wpbc_db_version', $wpbc_db_version);
    }
}

register_activation_hook(__FILE__, 'wpbc_install');


function wpbc_install_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . '8a'; 

}

register_activation_hook(__FILE__, 'wpbc_install_data');


function wpbc_update_db_check()
{
    global $wpbc_db_version;
    if (get_site_option('wpbc_db_version') != $wpbc_db_version) {
        wpbc_install();
    }
}

add_action('plugins_loaded', 'wpbc_update_db_check');



if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


class Custom_Table_Example_List_Table extends WP_List_Table
 { 
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'banner',
            'plural'   => 'banners',
        ));
    }


    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }


    function column_phone($item)
    {
        return '<em>' . $item['phone'] . '</em>';
    }


    function column_name($item)
    {

        $actions = array(
            'edit' => sprintf('<a href="?page=contacts_form&id=%s">%s</a>', $item['id'], __('Editar', 'wpbc')),
            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Borrar', 'wpbc')),
        );

        return sprintf('%s %s',
            $item['nombre'],
            $this->row_actions($actions)
        );
    }


    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', 
            'name'      => __('Nombre', 'wpbc'),
            'description'  => __('Descripcion', 'wpbc'),
            'image' => __('Url','wpbc'),
            'ocultar'=>__('Ocultar/Mostrar','wpbc'),
            'promocion'=>__('promocion','wpbc'),
            //'promocion'=>__('promocion','wpbc'),
            // 'imagen'     => __('Imagen', 'wpbc'),
            

        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id'      => array('id', true),
            'name' => array('nombre',true),
            'description'  => array('descripcion', true),
            'image' => array('image',true),
            'promocion'=> array('promocion',true),
            'ocultar'=>array('url',true),
            //'importancia'=>array('importancia',true),
            // 'imagen'     => array('imagen', true),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    function process_bulk_action()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . '8a'; 

        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE id IN($ids)");
            }
        }
    }

    function prepare_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . '8a'; 

        $per_page = 10; 

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
       
        $this->process_bulk_action();

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");


        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'nombre';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';


        $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);


        $this->set_pagination_args(array(
            'total_items' => $total_items, 
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page) 
        ));
    }
}

function wpbc_admin_menu()
{
    /* el  segundo (es el titulo del botón)  'activate_plugins', 'contacts', 'wpbc_contacts_page_handler <- es la función del botón'   */
    add_menu_page(__('Banner', 'wpbc'), __('BannerPrincipal', 'wpbc'), 'activate_plugins', 'contacts', 'wpbc_contacts_page_handler','dashicons-images-alt2' );
   
    add_submenu_page('contacts', __('todos', 'wpbc'), __('Todos los banners', 'wpbc'), 'activate_plugins', 'contacts', 'wpbc_contacts_page_handler');
   
    add_submenu_page('contacts', __('Add new', 'wpbc'), __('Nuevo Banner', 'wpbc'), 'activate_plugins', 'contacts_form', 'wpbc_contacts_form_page_handler');
}

add_action('admin_menu', 'wpbc_admin_menu');


function wpbc_validate_contact($item)
{
    $messages = array();

    if (empty($item['name'])) $messages[] = __('Titulo es requerido', 'wpbc');
    if (empty($item['description'])) $messages[] = __('Descripción es requerido', 'wpbc');
    if (empty($item['image'])) $messages[] = __('Url es requerido', 'wpbc');
    if (empty($item['ocultar'])) $messages[] = __('Ocultar/mostrar es requerido', 'wpbc');
    // if (empty($item['imagen'])) $messages[] = __('imagen es requerido', 'wpbc');

    if (empty($messages)) return true;
    return implode('<br />', $messages);
}


function wpbc_languages()
{
    load_plugin_textdomain('wpbc', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'wpbc_languages');