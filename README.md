# Another Dispatch Plugin (ADP)

**Another Dispatch Plugin** es una solución integral de **Newsletter y Suscripciones** para WordPress. A diferencia de otros plugins que dependen de servicios externos, ADP gestiona todo el ciclo de vida del correo dentro de tu instalación, utilizando **Action Scheduler** para procesar envíos masivos en segundo plano sin colapsar el servidor.

Incluye sistema de **Doble Opt-in** (verificación de correo), soporte nativo para **SMTP**, editor visual de plantillas y múltiples frecuencias de envío (Instantáneo, Semanal o Mensual).

## 🚀 Características Principales

* **Gestión de Suscriptores:**
    * Sistema de *Double Opt-in* para cumplir con normativas de privacidad (GDPR).
    * Importación y Exportación de suscriptores vía CSV.
    * Filtrado por estado (Activos/Pendientes).
* **Motor de Envío Robusto:**
    * Integración con librería **Action Scheduler** para envíos por lotes (evita *timeouts* en PHP).
    * Configuración de *Throttling*: Define cuántos correos enviar por lote y la pausa entre ellos.
    * Conexión SMTP personalizada (soporta SSL/TLS).
* **Modos de Entrega:**
    * ⚡ **Instantáneo:** Envía una notificación a toda la lista al publicar un nuevo post.
    * 📅 **Resumen Semanal:** Recopila los posts de la semana y los envía el lunes.
    * 🗓 **Resumen Mensual:** Envía un boletín el primer día del mes con los posts del mes anterior.
* **Diseñador Visual (Customizer):**
    * Personaliza colores, logotipo, textos del pie de página y estilos sin tocar código.
    * Vista previa en tiempo real dentro del admin.
* **Diagnóstico:** Panel de debug para monitorear la cola de envíos y detectar fallos.

---

## 🛠 Instalación

1.  Sube la carpeta `another-dispatch-plugin-wp` al directorio `/wp-content/plugins/` de tu sitio.
2.  Activa el plugin desde el menú **Plugins**.
3.  El plugin creará automáticamente una tabla en la base de datos (`wp_adp_subscribers`) y configurará las tareas programadas.
4.  Verás un nuevo menú llamado **Dispatch** en la barra lateral.

> **Nota:** Este plugin requiere PHP 7.4 o superior y utiliza *Composer* para gestionar *Action Scheduler*. Asegúrate de que la carpeta `vendor` esté presente.

---

## 📖 Manual de Uso

### 1. Configuración Inicial

Ve a **Dispatch > Configuración Global**.

1.  **Configuración SMTP:** Es **altamente recomendado** configurar un servicio SMTP (como SendGrid, Mailgun o el SMTP de tu hosting) para asegurar que los correos lleguen a la bandeja de entrada y no a Spam.
2.  **Modo de Envío:** Elige con qué frecuencia deseas contactar a tus suscriptores.
3.  **Rendimiento (Throttle):** Si tienes un hosting compartido, configura lotes pequeños (ej: 50 correos cada 120 segundos) para no exceder los límites de recursos.

### 2. Personalizar el Diseño del Correo

Ve a **Dispatch > Mail Customizer**.

* **Identidad:** Sube el logotipo de tu marca.
* **Paleta de Colores:** Ajusta los colores del encabezado, fondo, botones y textos para que coincidan con tu sitio web.
* **Vista Previa:** Utiliza el panel derecho para ver cómo quedará tu correo (puedes alternar entre vista de "Single Post" o "Digest/Resumen").

### 3. Publicar Formulario de Suscripción

Tienes dos formas de mostrar el formulario de registro a tus visitantes:

**A. Vía Shortcode:**
Copia y pega este código en cualquier página, entrada o bloque de Gutenberg:
```shortcode
[adp_subscribe]

```

**B. Vía Widget:**
Ve a **Apariencia > Widgets** y busca el widget "Newsletter Form (ADP)". Arrástralo a tu barra lateral o pie de página.

### 4. Gestión de Suscriptores

En el **Dashboard** principal del plugin puedes:

* Ver el estado de crecimiento de tu lista.
* **Importar:** Sube un archivo CSV con una columna de emails para migrar usuarios de otras plataformas.
* **Exportar:** Descarga tu lista completa para copias de seguridad.
* **Limpieza:** Elimina suscriptores o reenvía correos de verificación a usuarios pendientes manualmente.

---

## ⚙️ Detalles Técnicos para Desarrolladores

### Base de Datos

El plugin utiliza una tabla personalizada para máximo rendimiento, evitando ensuciar la tabla `wp_options` o `wp_users`:

* Tabla: `wp_adp_subscribers`
* Columnas: `id`, `email`, `status` ('active', 'pending'), `activation_hash`, `created_at`.

### Hooks y Cron

* **Action Scheduler:** Se utiliza el grupo `adp_emails` para todas las tareas. Puedes ver los trabajos en curso en **Herramientas > Scheduled Actions** o en el panel de **Debug** del plugin.
* **Envío Asíncrono:** El envío no bloquea el editor de WordPress. Al publicar un post, se programa una tarea que se ejecuta en segundo plano.

### Plantillas

Las plantillas de correo se encuentran en `templates/emails/`. Si necesitas modificar el HTML estructuralmente (más allá de lo que permite el Customizer), puedes editar:

* `layout.php`: Estructura maestra (HTML, Head, Body).
* `single.php`: Cuerpo del correo para notificaciones instantáneas.
* `digest.php`: Cuerpo del correo para resúmenes semanales/mensuales.

---

## ❓ Preguntas Frecuentes

**¿Por qué mis correos no se envían?**
Revisa la pestaña **Debug / Logs**. Si ves tareas en estado "Pending" que no avanzan, asegúrate de que el CRON de WordPress esté funcionando. Si están en "Failed", verifica tus credenciales SMTP en la configuración.

**¿Funciona con constructores visuales (Elementor, Divi)?**
Sí, el shortcode `[adp_subscribe]` es compatible con cualquier maquetador visual mediante el módulo de "Shortcode" o "Texto".

**¿Cómo me doy de baja?**
Todos los correos enviados incluyen automáticamente un enlace de desuscripción al pie, cumpliendo con la normativa CAN-SPAM y cabeceras RFC 8058 (List-Unsubscribe).
