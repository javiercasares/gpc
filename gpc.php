<?php
/*
Plugin Name: GPC
Description: Plugin para gestionar la Global Privacy Control (GPC).
Version: 1.0
Author: Tu Nombre
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

function gpc_options_page() {
    ?>
    <form action="options.php" method="post">
        <h1>Global Privacy Control</h1>
        <?php
        settings_fields('gpc_settings');
        do_settings_sections('gpc_settings');
        submit_button();
        ?>
    </form>
    <?php
}

function gpc_settings_section_callback() {
    echo __('Configura las opciones de Global Privacy Control.', 'gpc');
}

function gpc_enabled_render() {
    $options = get_option('gpc_settings');
    ?>
    <input type="checkbox" name="gpc_settings[gpc_enabled]" <?php checked(1, isset($options['gpc_enabled']) ? $options['gpc_enabled'] : 0, true); ?> value="1">
    <?php
}

function gpc_last_update_render() {
    $options = get_option('gpc_settings');
    $date = isset($options['gpc_last_update']) ? esc_attr($options['gpc_last_update']) : date('Y-m-d');
    ?>
    <input type="date" name="gpc_settings[gpc_last_update]" value="<?php echo $date; ?>">
    <?php
}

function gpc_settings_init() {
    register_setting('gpc_settings', 'gpc_settings');

    add_settings_section(
        'gpc_settings_section',
        __('Configuraciones de GPC', 'gpc'),
        'gpc_settings_section_callback',
        'gpc_settings'
    );

    add_settings_field(
        'gpc_enabled',
        __('GPC Activado', 'gpc'),
        'gpc_enabled_render',
        'gpc_settings',
        'gpc_settings_section'
    );

    add_settings_field(
        'gpc_last_update',
        __('Última Actualización', 'gpc'),
        'gpc_last_update_render',
        'gpc_settings',
        'gpc_settings_section'
    );
}
// Registrar las opciones
add_action('admin_init', 'gpc_settings_init');


function gpc_add_admin_menu() {
    add_tools_page(
        'Global Privacy Control', // Título de la página
        'Global Privacy Control', // Título del menú
        'manage_options',         // Capacidad
        'gpc',                    // Slug del menú
        'gpc_options_page'        // Función de callback
    );
}
// Registrar menú en el panel de administración
add_action('admin_menu', 'gpc_add_admin_menu');

function gpc_handle_well_known_route() {
    // Obtener la ruta solicitada
    $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($request_uri === '/.well-known/gpc.json') {
        // Verificar si el archivo existe en el sistema de archivos
        $file_path = ABSPATH . '.well-known/gpc.json';
        if (!file_exists($file_path)) {
            // Obtener las opciones guardadas
            $options = get_option('gpc_settings');
            $gpc = isset($options['gpc_enabled']) && $options['gpc_enabled'] ? true : false;
            $lastUpdate = isset($options['gpc_last_update']) ? $options['gpc_last_update'] : date('Y-m-d');

            // Crear el arreglo de respuesta
            $response = array(
                'gpc' => $gpc,
                'lastUpdate' => $lastUpdate,
            );

            // Enviar la respuesta JSON
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        // Si el archivo existe, WordPress no hará nada y el servidor lo servirá directamente
    }
}

// Manejar la ruta /.well-known/gpc.json
add_action('init', 'gpc_handle_well_known_route');
