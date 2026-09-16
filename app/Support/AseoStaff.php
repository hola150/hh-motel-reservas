<?php

namespace App\Support;

/**
 * Lista fija de personas de aseo -- placeholder hasta que exista un padrón
 * real de personal (cuentas de usuario con rol aseo). Cuando eso exista, este
 * listado se reemplaza por una consulta a esa tabla sin tocar el resto del
 * flujo (el formulario y la validación ya trabajan con nombres sueltos).
 */
class AseoStaff
{
    public const NAMES = ['Aseo 1', 'Aseo 2', 'Aseo 3'];
}
