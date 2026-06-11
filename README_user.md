# Guía rápida del usuario — Galería

Este documento explica cómo usar la galería `sfvalsep` desde la interfaz web.

1) Navegar la galería
- Abre la URL de la galería (`index.php`). Verás miniaturas en una cuadrícula.
- Haz clic en cualquier miniatura para abrir la vista en `pre.php` (visualizador ZboX/Zplayer integrado si está activo).

2) Seleccionar imágenes
- Marca las casillas en cada tarjeta para seleccionar imágenes.
- Cuando haya al menos una imagen seleccionada aparecerá un panel flotante (abajo a la derecha) con acciones.

3) Enviar por correo
- En el panel flotante pulsa "Enviar por mail".
- Rellena destinatario (email), nombre y mensaje en el modal.
- El sistema decidirá si adjuntar las imágenes individuales o crear un ZIP en función del umbral configurado (p. ej. 5).
- Se mostrará una barra de progreso y notificación al completarse.

4) Subir fotos
- Si el administrador habilitó las subidas, verás un formulario de subida en la página principal.
- Puedes subir múltiples archivos (imágenes, también audio/video según configuración). El sistema registrará las subidas y las añadirá a la galería.

5) Marcas de agua y previsualización
- Las miniaturas y la vista `pre.php` pueden mostrar una marca de agua si el administrador la ha activado.
- La marca se aplica en tiempo real en la generación de miniaturas (archivo `pre.php`). Si ves que no aparece, contacta con el administrador (puede necesitar convertir el logo SVG o habilitar la conversión externa).

6) Privacidad y borrado
- Las fotos "eliminadas" no se borran inmediatamente: el sistema mueve archivos a `.photosbak` o los marca como `bak` según configuración del administrador.

Soporte
- Si algo falla, toma nota del mensaje en pantalla y pásalo al administrador.

---

Ruta de interés (técnica): `pre.php`, `mini.php`, `subir.php`, `send_mail.php` (envío por mail) y `admin.php` (ajustes).