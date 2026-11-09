<?php
// Fuso horário do sistema (o XAMPP vem com Europe/Berlin no php.ini,
// o que deixava datas/horas e a validade dos links de e-mail erradas).
date_default_timezone_set('America/Porto_Velho');

define('APP', dirname(__FILE__));
define('URL', 'http://localhost/life');
define('APP_NOME','Life IFRO'); //outra forma de declarar constante
const APP_VERSAO = '1.1.0';
