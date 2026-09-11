<?php

/**
 * Plugin Name: SGFP — Sistema de Gestão Financeira Pessoal
 * Description: Backend financeiro pessoal sobre WordPress REST API.
 * Version: 1.0.0
 * Author: SGFP
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * License: Proprietary
 * Text Domain: sgfp
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use SGFP\Plugin;

const SGFP_PLUGIN_FILE = __FILE__;
const SGFP_PLUGIN_DIR = __DIR__;
const SGFP_VERSION = '1.0.0';

Plugin::boot();

register_activation_hook(__FILE__, [Plugin::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);
