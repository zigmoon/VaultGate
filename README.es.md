<p align="center">
  <img src="assets/icon-256.png" alt="VaultGate" width="92" height="92">
</p>

<h1 align="center">VaultGate</h1>

<p align="center">
  <b>Una contraseña para todo el sitio — nada más en la página.</b><br>
  Un plugin de WordPress discreto, para sitios en construcción.
</p>

<p align="center">
  <a href="README.md">English</a> ·
  <a href="README.fr.md">Français</a> ·
  <a href="README.es.md"><b>Español</b></a>
</p>

<p align="center">
  <a href="https://github.com/zigmoon/passwordprotect/releases/latest"><b>⬇ Descargar</b></a> ·
  <a href="#instalación">Instalación</a> ·
  <a href="#qué-obtienes">Qué obtienes</a> ·
  <a href="#créditos">Créditos</a>
</p>

<p align="center">
  <img alt="WordPress 6.5 → 7.1" src="https://img.shields.io/badge/WordPress-6.5%20%E2%86%92%207.1-0b0d10?style=flat-square">
  <img alt="PHP 8.2 → 8.5" src="https://img.shields.io/badge/PHP-8.2%20%E2%86%92%208.5-0b0d10?style=flat-square">
  <img alt="Versión 1.1.1" src="https://img.shields.io/badge/version-1.1.1-0a66ff?style=flat-square">
  <img alt="181 aserciones" src="https://img.shields.io/badge/tests-181%20assertions-0b0d10?style=flat-square">
  <img alt="Licencia GPL-2.0-or-later" src="https://img.shields.io/badge/licence-GPL--2.0--or--later-0b0d10?style=flat-square">
</p>

![Ajustes → VaultGate → Página de acceso: logotipo de la biblioteca de medios, matices de acento, tema, textos](docs/admin-appearance.png)

## Qué obtienes

**Ajustes → VaultGate** — cinco pestañas, sin ruido: **Protección**, **Página de acceso**, **Acceso**, **Avanzado**, **Actividad**.

- **Una contraseña y todo queda cerrado.** Páginas, archivos, feeds, sitemaps, la API REST, AJAX anónimo y XML-RPC — cada superficie con su interruptor.
- **Tu logotipo, tu color.** Logotipo elegido en la biblioteca de medios (`wp-content/uploads`), ocho matices o cualquier hexadecimal, página clara / oscura / sistema. El contraste se calcula, nunca se adivina.
- **Nada que tú no hayas escrito.** Sin frase de marketing, sin enlace de soporte, sin pie de página. Un logotipo, un título, un campo, un botón.
- **Sin terceros.** Fuentes, iconos y estilos viven dentro del plugin. Sin Google Fonts, sin CDN, sin analítica, ninguna petición saliente.
- **Cookie firmada, no sesiones.** HMAC-SHA256, `HttpOnly`, `SameSite`; un clic revoca a todos los visitantes.
- **Hecho para quitarse.** Sin tablas, sin `.htaccess`, sin reglas de reescritura. Desactiva y el sitio vuelve a ser público.

## La página de acceso

| Clara | Oscura |
|---|---|
| ![Clara](docs/gate-light.png) | ![Oscura](docs/gate-dark.png) |

Una columna, centrada desde 320 px, y funciona sin JavaScript.

![Protección: interruptor principal, contraseña, cerrar sesión a todos los visitantes](docs/admin-protection.png)

## Instalación

1. Descarga `vaultgate-1.1.1.zip` desde las [versiones](https://github.com/zigmoon/passwordprotect/releases/latest).
2. **Plugins → Añadir nuevo → Subir plugin**, elige el ZIP, instala, activa.
3. **Ajustes → VaultGate**: define la contraseña y activa la protección.

O desde un repositorio:

```bash
cp -r vaultgate /ruta/a/wp-content/plugins/
wp plugin activate vaultgate
wp vaultgate password 'una frase larga'
wp vaultgate enable
```

WordPress 6.5+ y PHP 8.2+ — nada más que instalar. La primera activación muestra un único aviso que lleva al campo de contraseña: se puede descartar.

![Primera activación](docs/admin-welcome.png)

## Calidad

- **Últimas versiones.** WordPress 6.5 → 7.1, PHP 8.2 → 8.5, ninguna llamada obsoleta, ninguna biblioteca incluida. Las traducciones se cargan en `init`, como exige WordPress 6.7+.
- **181 aserciones.** Dos suites (`php tests/run.php`, `php tests/smoke.php`) en PHP 8.3 y 8.5, sin instalar WordPress.
- **Probado en una instalación real.** Todas las superficies verificadas en WordPress 7.1.3 / PHP 8.3: portada, entradas, feeds, sitemaps, REST, AJAX, XML-RPC, `robots.txt`, `wp-login.php`, administración, activación y borrado.
- **Panel responsive.** La pantalla de ajustes sigue siendo usable hasta 360 px; la página de acceso está centrada en cualquier ancho.

## Límites

- **Archivos estáticos.** `wp-content/uploads/...` lo sirve el servidor web y nunca llega a WordPress. Usa autenticación de servidor para archivos sensibles.
- **Caché de página.** La página envía `no-store` y comprueba la cookie en cada petición; excluye el sitio de una caché de página mientras esté bloqueado.
- **`wp-login.php` sigue abierto.** Es la salida: las sesiones de administrador nunca se bloquean.
- **Una contraseña compartida.** No es un gestor de usuarios o roles.

## Quitarlo

Desactivar vuelve a hacer público el sitio y revoca todas las cookies emitidas. Borrar el plugin elimina sus ajustes, el hash y el registro — **activado por defecto** — así que no queda nada. Si te bloqueas: `define( 'VAULTGATE_DISABLED', true );` en `wp-config.php`, o `wp vaultgate disable`.

## Créditos

Un plugin de **[2.13](https://2points13.fr)** · dev by **[zigmoon](https://zigmoon.com)**.

![La fila del plugin en la pantalla de plugins](docs/admin-plugins.png)

GPL-2.0-or-later — ver [LICENSE](LICENSE). Fuentes: Inter, Space Grotesk, JetBrains Mono (SIL Open Font License, incluidas).
