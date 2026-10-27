<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="112" height="112">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>Una sola contraseña para todo el sitio.</b><br>
  Un plugin de WordPress discreto y sin dependencias que mantiene un sitio privado mientras lo construyes.
</p>

<p align="center">
  <a href="README.md">English</a> ·
  <a href="README.fr.md">Français</a> ·
  <a href="README.es.md"><b>Español</b></a>
</p>

<p align="center">
  <a href="https://github.com/zigmoon/passwordprotect/releases/latest"><b>⬇️ Descargar</b></a> ·
  <a href="#instalación">Instalación</a> ·
  <a href="#la-página-de-acceso">La página de acceso</a> ·
  <a href="#seguridad">Seguridad</a> ·
  <a href="#desarrollo">Desarrollo</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 – 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 – 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Versión 1.1.0" src="https://img.shields.io/badge/version-1.1.0-0a66ff?style=flat-square">
  <img alt="Licencia GPL-2.0-or-later" src="https://img.shields.io/badge/licencia-GPL--2.0--or--later-0b0d10?style=flat-square">
  <img alt="163 aserciones" src="https://img.shields.io/badge/tests-163%20assertions-0b0d10?style=flat-square">
  <img alt="Sin dependencias" src="https://img.shields.io/badge/dependencias-ninguna-0b0d10?style=flat-square">
</p>

<p align="center">
  <img src="docs/gate-light.png" alt="La página de acceso de VaultGate" width="880">
</p>

---

## Lo esencial

- **Una contraseña, todo cerrado.** Páginas, archivos, feeds, sitemaps, API REST, AJAX anónimo y XML-RPC — cada superficie se puede activar o desactivar.
- **Tu logotipo, tu color.** Elige una imagen de la biblioteca de medios de WordPress; ocho colores predefinidos o cualquier color. Negro por defecto, contraste calculado automáticamente.
- **Las tipografías dentro del plugin.** Inter (la familia de SuperPhoto), Space Grotesk y JetBrains Mono se envían como WOFF2 dentro del plugin. Sin Google Fonts, sin CDN, sin ninguna petición saliente.
- **Nada que no hayas escrito.** Sin frase promocional por defecto, sin enlace de ayuda, sin pie de página. Un logotipo, un título, un campo y un botón.
- **Cookies firmadas, sin sesiones.** Token HMAC-SHA256 sin estado; un clic revoca a todos los visitantes.
- **Endurecido por defecto.** `wp_hash_password()`, límite de intentos por visitante, CSP con nonce, `no-store`, `noindex`, `Permissions-Policy`.
- **Autoalojado y discreto.** Sin Composer, sin npm, sin compilación, sin estadísticas, sin llamadas externas.
- **Inglés, francés, español.** Cadenas fuente en inglés; las dos traducciones se envían compiladas y verificadas.
- **163 aserciones.** Dos suites, ejecutadas en PHP 8.3 y PHP 8.5, sin necesidad de instalar WordPress.

## La página de acceso

| Claro | Oscuro |
|---|---|
| ![Claro](docs/gate-light.png) | ![Oscuro](docs/gate-dark.png) |

| Móvil (390 × 844) |
|---|
| <img src="docs/gate-mobile.png" alt="Móvil" width="330"> |

Una sola columna, centrada en cualquier anchura, desde 320 px. Una línea fina separa la cabecera del formulario, un trazo de acento subraya el título y el botón es la única superficie con color. El mismo lenguaje se usa en la pantalla de ajustes:

| Ajustes — logotipo, color, tema | Ajustes — protección |
|---|---|
| ![Ajustes de la página](docs/admin-appearance.png) | ![Ajustes de protección](docs/admin-protection.png) |

## Instalación

**Desde el archivo**

1. Descarga `vaultgate-1.1.0.zip` desde las [versiones](https://github.com/zigmoon/passwordprotect/releases/latest).
2. WordPress → **Plugins → Añadir nuevo → Subir plugin**.
3. Activa **VaultGate**.
4. Abre **Ajustes → VaultGate**.

**Desde un repositorio**

```bash
cp -r vaultgate /ruta/a/wp-content/plugins/
wp plugin activate vaultgate
wp vaultgate password 'una frase larga y secreta'
wp vaultgate enable
```

Requiere WordPress 6.5+ y PHP 8.2+. Ninguna extensión de PHP más allá de una instalación estándar de WordPress.

## Configuración

**Ajustes → VaultGate** tiene cinco pestañas.

| Pestaña | Contenido |
|---|---|
| Protección | Interruptor general, nueva contraseña, «cerrar la sesión de todos los visitantes», ayuda de desbloqueo |
| Página de acceso | **Logotipo**, título, mensaje opcional, **color de acento**, **tema** |
| Acceso | Duración de sesión, «mantener la sesión», capacidad de omisión, IP permitidas, confianza en el proxy, rutas excluidas |
| Avanzado | Interruptores REST / feeds / sitemaps / AJAX / XML-RPC, límites de fuerza bruta, registro, limpieza al desinstalar |
| Actividad | Los últimos 20 eventos, con direcciones IP anonimizadas |

### El logotipo

Pulsa **Elegir imagen** y se abre la biblioteca de medios de WordPress. VaultGate guarda el identificador del adjunto, no una copia:

- el archivo vive en `wp-content/uploads/AAAA/MM/`, como cualquier otro medio;
- vuelve con tus copias de seguridad de medios y puede reutilizarlo un tema;
- WordPress genera los tamaños, así que la página sirve una imagen adecuada;
- **Quitar** vacía el campo y reaparece la marca geométrica integrada.

La página de acceso limita el logotipo a 168 × 56 px CSS manteniendo la proporción: un logotipo ancho funciona mejor. Se aceptan PNG, JPEG, WebP y GIF; el SVG requiere un filtro `upload_mimes`, como en todo WordPress.

### El color

Ocho predefinidos — **Tinta** (por defecto), Pizarra, Azul, Verde, Ámbar, Rojo, Violeta, Rosa — o el selector nativo. El acento pinta el botón, el anillo de foco y la línea bajo el título. El texto del botón alterna entre blanco y negro según la luminancia relativa WCAG, y un acento casi negro pasa a blanco en el tema oscuro para que el botón nunca desaparezca.

### El tema

**Claro** (por defecto), **Oscuro**, o **Sistema** para seguir el sistema operativo del visitante.

## Seguridad

- **Contraseña** — `wp_hash_password()` / `wp_check_password()`. Nunca en texto plano, nunca en el registro, nunca enviada.
- **Cookie** — `expiración.firma`, HMAC-SHA256 sobre el contexto, la versión de sesión y la caducidad, con `wp_salt('auth')` y el `COOKIEHASH` del sitio. Verificada con `hash_equals()`.
- **Revocación** — cambiar la contraseña, alternar la protección o pulsar «cerrar la sesión de todos los visitantes» incrementa `vaultgate_auth_version`: todas las cookies emitidas quedan invalidadas al instante.
- **Atributos de la cookie** — `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS (o detrás de un proxy de confianza), ruta y dominio de WordPress.
- **Límite de intentos** — ventana deslizante por visitante en un transient cuya clave es un HMAC de la dirección. Bloqueo con cuenta atrás en el botón.
- **Formulario** — token firmado con caducidad, trampa invisible para robots, mensajes genéricos, redirección `303` tras el proceso.
- **Cabeceras** — `no-store`, `noindex, nofollow, noarchive`, `X-Frame-Options: DENY`, `nosniff`, `Referrer-Policy: no-referrer`, `Permissions-Policy` restrictiva, CSP con nonce por petición.
- **Administración** — `manage_options`, nonce en cada acción, validación en el servidor, salida escapada.

### Límites

- **Archivos estáticos.** Los archivos servidos directamente por el servidor (`wp-content/uploads/…`) nunca pasan por WordPress. Usa autenticación de servidor o medios privados si son sensibles.
- **Caché de página.** La página envía `no-store` y comprueba la cookie en cada petición, pero excluye el sitio de una caché externa mientras esté bloqueado.
- **`wp-login.php` sigue abierto.** Es necesario: es la puerta de vuelta. VaultGate es una puerta pública, no un sustituto de la autenticación de WordPress.
- **Una contraseña compartida.** No es un gestor de usuarios ni de roles.
- **Multisitio.** Los ajustes son por sitio.

## Acceso de emergencia

1. `wp-login.php` nunca se filtra, y las sesiones de administrador omiten siempre la página de acceso.
2. O, sin el panel, en `wp-config.php`:
   ```php
   define( 'VAULTGATE_DISABLED', true );
   ```
3. O con WP-CLI:
   ```bash
   wp vaultgate status
   wp vaultgate disable
   wp vaultgate password 'una nueva frase secreta'
   ```

Un visitante puede volver a bloquearse con `https://ejemplo.com/?vaultgate=logout`.

## WP-CLI

```text
wp vaultgate status              Estado completo: protección, contraseña, superficies, capacidad
wp vaultgate enable              Activa la protección (debe existir una contraseña)
wp vaultgate disable             Desactiva y conserva la contraseña
wp vaultgate password <clave>    Define la contraseña (8 caracteres mínimo)
wp vaultgate password --stdin    La lee de la entrada estándar
wp vaultgate logout-all          Revoca todas las cookies emitidas
wp vaultgate log                 Muestra el registro de actividad
wp vaultgate log --clear         Lo vacía
```

## Filtros

| Filtro | Función |
|---|---|
| `vaultgate_should_gate` | Permitir o rechazar la página de acceso en una petición |
| `vaultgate_client_ip` | Sustituir la dirección detectada |
| `vaultgate_bypass_capabilities` | Añadir o quitar capacidades de omisión |
| `vaultgate_blocked_endpoints` | Cambiar la lista de puntos de entrada directos bloqueados |
| `vaultgate_secure_cookie` | Forzar o desactivar el atributo `Secure` |
| `vaultgate_cookie_name` | Renombrar la cookie |
| `vaultgate_swatches` | Cambiar los colores predefinidos |
| `vaultgate_throttled_log_events` | Elegir los eventos limitados a una entrada por minuto |
| `vaultgate_log_size` | Número de entradas conservadas (20 por defecto) |

```php
// Dejar pasar un webhook de pago sin abrir todo el sitio.
add_filter(
	'vaultgate_should_gate',
	static function ( bool $gate ): bool {
		return str_starts_with( $_SERVER['REQUEST_URI'] ?? '', '/webhooks/stripe' ) ? false : $gate;
	}
);
```

## Desarrollo

PHP 8.2+ y nada más.

```bash
php tests/run.php                 # pruebas unitarias
php tests/smoke.php               # arranque, guard, REST, render del admin
php tools/build-translations.php  # .pot + fr_FR + es_ES, compilados y verificados
pwsh -File tools/build-images.ps1 # tamaños de icono desde assets/icon-master.png
pwsh -File tools/build-zip.ps1    # dist/vaultgate-<version>.zip, verificado por hash

# Vistas de diseño (sin WordPress)
php -S 127.0.0.1:8450 tests/preview-router.php
#   /                        página de acceso, tema claro
#   /?theme=dark             página de acceso, tema oscuro
#   /?accent=%230a66ff       página de acceso, acento personalizado
#   /?logo=1                 página de acceso con un logotipo de ejemplo
#   /admin-preview.php       pantalla de ajustes
```

Salida esperada:

```text
140 passed, 0 failed
23 passed, 0 failed
```

Las suites usan un pequeño conjunto de funciones de WordPress simuladas (`tests/bootstrap.php`): sin base de datos, sin servidor web, sin instalación de WordPress. Se ejecutan en **PHP 8.3.36** y **PHP 8.5.12**.

## Estructura

```text
passwordprotect/
├── vaultgate/                     el plugin, tal como se instala
│   ├── vaultgate.php              cabecera de WordPress + arranque
│   ├── includes/                  ajustes, reglas de acceso, tokens, limitador, guard, admin, CLI
│   ├── templates/gate.php         la página de acceso
│   ├── assets/css|js/             estilos y scripts, sin CDN
│   ├── assets/fonts/              Inter, Space Grotesk, JetBrains Mono (WOFF2, OFL)
│   └── languages/                 .pot, fr_FR, es_ES (fuentes, compiladas y verificadas)
├── assets/                        fuentes del icono, banners, vista social
├── docs/                          capturas usadas arriba
├── dist/                          archivo instalable versionado
├── tests/                         suites de pruebas + vistas de diseño
└── tools/                         traducciones, imágenes, archivo
```

## Compatibilidad

| | |
|---|---|
| WordPress | 6.5 → 7.1 (última estable al escribir: 7.1.3) |
| PHP | 8.2 → 8.5, pruebas en 8.3.36 y 8.5.12 |
| Base de datos | No crea tablas; solo `wp_options` |
| Multisitio | Sí, ajustes por sitio |
| Extensiones PHP | Ninguna más allá de una instalación estándar |
| Navegadores | Cualquiera actual; la página funciona sin JavaScript |

## FAQ

**¿Dónde se guarda el logotipo?**
En la biblioteca de medios de WordPress. VaultGate guarda el identificador del adjunto: el archivo se respalda con tus medios y se puede reutilizar en otro sitio.

**¿Cómo quito la frase bajo el título?**
No hay ninguna por defecto. El campo de mensaje de la pestaña **Página de acceso** es opcional; sin él, la página es una sola columna sobria.

**¿Puedo usar mi propia tipografía?**
Las tres familias están dentro del plugin. Para cambiarlas, sustituye los WOFF2 en `vaultgate/assets/fonts/` y actualiza las reglas `@font-face` de `vaultgate/assets/css/`. Nada se carga desde terceros.

**¿Funciona con una caché de página?**
Las cabeceras y la comprobación de cookie son correctas, pero excluye el sitio de una caché externa mientras esté bloqueado.

**¿Están protegidos los archivos subidos?**
No — esos archivos se saltan WordPress. Ver [Límites](#límites).

**¿Qué ocurre si desactivo el plugin?**
El sitio vuelve a ser público y todas las cookies emitidas se revocan: al reactivarlo se pedirá la contraseña de nuevo.

## Licencia

GPL-2.0-or-later — ver [LICENSE](LICENSE). Copyright © 2026 zigmoon.

Informes de seguridad: [SECURITY.md](SECURITY.md).
