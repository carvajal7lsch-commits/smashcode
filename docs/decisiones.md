# Decisiones de alcance frente a `input.md`

Este documento registra los puntos en que la plataforma construida se aparta de la tabla de requisitos funcionales de `input.md`. En los tres casos la desviación es deliberada y anterior a este documento; se deja por escrito para que la revisión no los lea como defectos y para que `input.md` se actualice cuando corresponda.

## 1. Estructura de módulos (afecta RF-07, RF-09, RF-10, RF-11)

`input.md` describe **6 niveles alineados al MCER (A1 → B2)**, con un RAP por nivel.

Lo implementado son **4 módulos correspondientes a las fases del proyecto formativo SENA**, con los 6 RAPs repartidos entre ellos:

| Módulo | Fase | RAPs |
|---|---|---|
| 1 — Getting to Know Other People | Análisis | RAP 1 |
| 2 — Work Life Interaction | Planeación | RAP 2 y 3 |
| 3 — Work Place Communication | Ejecución | RAP 4 y 5 |
| 4 — Professional Practice | Evaluación | RAP 6 |

Esta estructura es la que definió el instructor del proyecto y está especificada en [`contenidos.md`](../contenidos.md), que la detalla módulo por módulo junto con los cuatro Momentos de cada uno (Preparación, Absorción, Práctica y Aplicación, Cierre). La base de datos y el recorrido del aprendiz la siguen exactamente: los cuatro Momentos corresponden a las etapas 0, 25, 50 y 75 de `sesion_practica`.

Lo que sí se conserva de RF-07: el administrador solo puede editar los atributos de un módulo. No existe forma de crear ni eliminar módulos ni RAPs.

## 2. Temas del nivel básico (afecta RF-15)

`input.md` exige para el nivel básico los temas Medical Vocabulary, Hospital Areas, Vital Signs y Medical Forms.

El instructor definió otro temario para el Módulo 1, recogido en `contenidos.md`: alfabeto y fonemas, números, saludos y despedidas, información personal (nombre, edad, nacionalidad, teléfono, correo) y el verbo *To Be* como píldora gramatical. El vocabulario cargado corresponde a ese temario.

Los temas de `input.md` no se descartaron por omisión: se sustituyeron por el temario que entregó el instructor.

## 3. Audio de pronunciación (afecta RF-17, RF-18, RF-20, RF-25)

`input.md` pide audio **pre-grabado en MP3/OGG con voz nativa**.

La plataforma usa la **síntesis de voz del navegador** (`window.speechSynthesis`) como fuente de audio por defecto, en vocabulario, glosario, diálogos y en el recurso de ayuda de los ejercicios. En los diálogos se diferencian las voces de enfermero y paciente ajustando tono, velocidad y selección de voz, y la línea activa se resalta mientras suena.

La carga de archivos **sigue disponible y tiene prioridad**: las columnas `vocabulario.audio_url` y `turno_dialogo.audio_url` se conservan, el panel del administrador permite subir MP3/OGG/WAV con validación de contenido, y cuando una entrada tiene archivo propio se reproduce ese en lugar de la síntesis.

La decisión evita depender de una producción de audio que no está contratada, sin cerrar la puerta a incorporarla después entrada por entrada.

## 4. Activación de cuenta cuando no hay correo (RF-01)

La activación por correo ya se exige: el auto-registro crea la cuenta sin verificar, manda un enlace de 24 horas de un solo uso, y el login la rechaza hasta que se confirme.

Con una salvedad deliberada: **si el correo no sale, la cuenta se habilita igual**. Aplica cuando `MAIL_ENABLED=false` o cuando el servidor SMTP falla. Sin esa salida, una instalación sin SMTP configurado —el entorno local, por ejemplo— no dejaría entrar a nadie nunca, y no habría forma de recuperarse desde la propia aplicación. Es la misma regla que ya seguía el correo de bienvenida y la que usa la entrega de claves temporales.

Dos consecuencias que conviene tener presentes:

- Las cuentas que crea el administrador **no** pasan por activación. Él responde por la persona y la clave temporal viaja a esa misma dirección, así que exigirla solo dejaría fuera a los instructores que él mismo dio de alta.
- La migración marca como verificadas todas las cuentas anteriores. Sin eso, activar la comprobación habría bloqueado a todos los usuarios existentes, porque `correo_verificado` nace en 0.

## Pendientes reales de código

- Ninguno. Los tres huecos detectados (RF-16 etiquetas, RF-34 recurso de ayuda, RF-01 activación) están implementados.

## Pendiente de contenido, no de código (RF-20)

El glosario cumple lo verificable del requisito: filtra por área clínica, nivel y categoría gramatical, busca por término y por etiquetas, y cada entrada tiene audio reproducible. El criterio de rendimiento se cumple con margen amplio: la consulta con filtro y búsqueda tarda **2 ms**, contra el segundo que exige el requisito. No se añadieron índices porque sobre 65 filas no aportarían nada medible; conviene revisarlo si el glosario crece a varios miles de términos, ya que la búsqueda usa `LIKE '%…%'` y esa forma no puede aprovechar un índice.

Lo que falta es contenido. De las 9 áreas clínicas del catálogo, **7 no tienen ni un término**:

| Área | Términos |
|---|---|
| General | 57 |
| Urgencias | 8 |
| Cardiología, Cirugía, Farmacología, Obstetricia, Pediatría, Traumatología, UCI | 0 |

Además, 57 de los 65 términos están agrupados en "General", que es la categoría por defecto de los seeds y no una clasificación real.

Mientras tanto, los filtros del glosario muestran cuántos términos tiene cada opción, para que filtrar por un área vacía se explique solo en vez de parecer un fallo. Clasificar el vocabulario existente y ampliarlo hasta cubrir las áreas del programa es trabajo del instructor, no de desarrollo: no se inventó vocabulario clínico para rellenar el hueco.
