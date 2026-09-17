<?php
// Los scripts de mantenimiento nunca deben ejecutarse por una petición web.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este comando solo está disponible en consola.');
}
