<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function user_theme_pre_process_user_signin(&$variables) {

    $variables['return_url'] = Pyshnov::request()->getRequestUri();

}
