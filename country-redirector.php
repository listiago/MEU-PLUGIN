<?php
/*
Plugin Name: Country Redirector
Description: Redireciona se a origem não for do Brasil.
Version: 1.0
Author: Tiago Lins 
*/

// Chave da API ipstack
define('IPSTACK_API_KEY', 'bc780bba2a8ec2c641c1c95f0259bf53');

// Adiciona o gancho para verificar o país e redirecionar
add_action('init', 'country_redirector_init');

add_action('admin_menu', 'country_redirector_add_submenu');

function country_redirector_add_submenu() {
    add_submenu_page(
        'options-general.php',         // Slug do menu pai (Configurações)
        'Configurações de Redirecionamento',  // Título da página
        'Redirecionamento',             // Título do submenu
        'manage_options',               // Capacidade necessária para acessar
        'country_redirector_settings',  // Slug da página
        'country_redirector_settings_page'  // Função para renderizar a página
    );
}

function country_redirector_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configurações de Redirecionamento</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('country_redirector_settings');
            do_settings_sections('country_redirector_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Link Principal:</th>
                    <td><input type="text" name="country_redirector_main_link" value="<?php echo esc_attr(get_option('country_redirector_main_link')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Link de Redirecionamento (para países não-BR):</th>
                    <td><input type="text" name="country_redirector_redirect_link" value="<?php echo esc_attr(get_option('country_redirector_redirect_link')); ?>" /></td>
                </tr>
            </table>
            <?php
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function country_redirector_init() {
    if (!is_admin() && !defined('DOING_AJAX')) {
        $user_country = get_user_country();
        
        // Obtenha os links a partir das opções de configuração
        $main_link = get_option('country_redirector_main_link', 'http://example.com');
        $redirect_link = get_option('country_redirector_redirect_link', 'http://example.com');

        // Verifique se o país é diferente do Brasil
        if ($user_country && $user_country !== 'BR') {
            wp_redirect($redirect_link);
            exit;
        }
    }
}

// Função para obter o país do usuário com base no IP usando ipstack
function get_user_country() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $api_url = "http://api.ipstack.com/$ip?access_key=" . IPSTACK_API_KEY;

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    return isset($data->country_code) ? $data->country_code : false;
}

// Registra as configurações e os campos no painel de administração
add_action('admin_init', 'country_redirector_settings');

function country_redirector_settings() {
    register_setting('country_redirector_settings', 'country_redirector_main_link');
    register_setting('country_redirector_settings', 'country_redirector_redirect_link');
}
?>
