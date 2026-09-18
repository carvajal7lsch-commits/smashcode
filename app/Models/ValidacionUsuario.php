<?php
namespace App\Models;

use App\Core\Model;

class ValidacionUsuario extends Model {
    public static function entrada($valor): string {
        return limpiar(is_string($valor) ? $valor : '');
    }

    public static function errores(string $nombre,string $correo,string $ficha,string $rol,array $datos=[]): array {
        $errores=[];
        foreach (['id','nombre_completo','correo','ficha_sena','rol','programa_id','contrasena'] as $campo) {
            if (isset($datos[$campo]) && !is_string($datos[$campo])) $errores[]='El campo '.$campo.' no es válido.';
        }
        if ($nombre==='' || mb_strlen($nombre)>255) $errores[]='El nombre es obligatorio y admite hasta 255 caracteres.';
        if (!filter_var($correo,FILTER_VALIDATE_EMAIL) || mb_strlen($correo)>255) $errores[]='Ingresa un correo válido de hasta 255 caracteres.';
        if (mb_strlen($ficha)>50) $errores[]='La ficha admite hasta 50 caracteres.';
        if (!in_array($rol,['aprendiz','instructor','admin'],true)) $errores[]='Selecciona un rol válido.';
        return $errores;
    }

    public static function programaPermitido(string $id,?string $actual=null): bool {
        if ($id==='') return true;
        $stmt=self::obtenerConexion()->prepare('SELECT activo FROM programa_formacion WHERE id=? AND eliminado=0');
        $stmt->execute([$id]);$activo=$stmt->fetchColumn();
        return $activo!==false && ((int)$activo===1 || $id===$actual);
    }
}
