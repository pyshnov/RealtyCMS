<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

function data_theme_pre_process_data_admin_edit(&$variables) {

    $variables['category']->setValue($variables['object']['topic_id']);
}