<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

/**
 * Created by PhpStorm.
 * User: aleksandr
 * Date: 24.05.17
 * Time: 0:16
 */

function testInstall()
{
    faqCreateDateBase();
}

/**
 * Создает таблицу расширения в БД
 */
function faqCreateDateBase() {
    \Pyshnov\Core\DB\DB::create([
        '`id`' => "int(11) NOT NULL AUTO_INCREMENT COMMENT 'Порядковый номер записи'",
        '`active`' => "TINYINT NOT NULL DEFAULT 0",
        '`question`' => "text NOT NULL COMMENT 'Вопрос'",
        '`answer`' => "text NOT NULL COMMENT 'Ответ'",
        '`sort`' => "int(11) NOT NULL COMMENT 'Порядок'",
        '`author`' => "VARCHAR(200) NOT NULL COMMENT 'Имя автора'",
        '`author_email`' => "VARCHAR(200) NOT NULL COMMENT 'email автора'",
        'PRIMARY KEY (`id`)' => ''
    ], DB_PREFIX . '_test', 'ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')->execute();
}