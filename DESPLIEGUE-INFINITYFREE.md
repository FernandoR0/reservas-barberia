# Publicar en InfinityFree (gratis)

InfinityFree no tiene terminal, así que se sube el proyecto ya preparado.

## 0. Preparar el paquete (en tu computadora)

Con Docker abierto y los contenedores en marcha (`docker compose up -d`):

```bash
bash scripts/empaquetar-infinityfree.sh
```

Se crean dos archivos en `deploy/`:

| Archivo | Para qué |
|---|---|
| `barberia-htdocs.zip` | Todo el sitio. Se descomprime dentro de `htdocs/`. |
| `barberia.sql` | Tablas y datos de demo. Se importa en phpMyAdmin. |

## 1. Crear la cuenta y el sitio

1. Regístrate en https://infinityfree.com.
2. Pulsa **Create Account** y elige un **subdominio gratuito**, por ejemplo `barberia-navaja.infinityfreeapp.com`.
3. Entra al **Control Panel** de esa cuenta de hosting.

## 2. Crear la base de datos

1. En el Control Panel, abre **MySQL Databases** y crea una base llamada `barberia`.
2. Anota los cuatro datos que muestra (también están en **Account Details** del panel de InfinityFree):
   - **MySQL Hostname**, por ejemplo `sql312.infinityfree.com`
   - **MySQL Database Name**, por ejemplo `if0_12345678_barberia`
   - **MySQL Username**, por ejemplo `if0_12345678`
   - **MySQL Password**, que es la contraseña de la cuenta de hosting
3. Pulsa **Admin** junto a la base para abrir **phpMyAdmin**.
4. Ve a la pestaña **Importar**, elige `deploy/barberia.sql` y pulsa **Continuar**.

## 3. Subir los archivos

1. En el Control Panel abre **Online File Manager** y entra a `htdocs/`.
2. Borra los archivos de ejemplo que trae (`index2.html` u otros).
3. Sube `barberia-htdocs.zip` y descomprímelo ahí con la opción **Extract**.

Si el administrador no permite descomprimir, usa **FileZilla**:
- Los datos de FTP están en **Account Details** de InfinityFree.
- Descomprime el zip en tu computadora y arrastra todo su contenido a `htdocs/`.
- Son unos 6 700 archivos; puede tardar de 10 a 20 minutos.

Debe quedar así:

```
htdocs/
├── .htaccess
├── index.php
├── build/
└── laravel/      ← la aplicación (bloqueada al público)
```

## 4. Configurar el `.env`

Abre `htdocs/laravel/.env` con el editor del File Manager y reemplaza cada `CAMBIAR`:

```
APP_URL=http://barberia-navaja.infinityfreeapp.com
DB_HOST=sql312.infinityfree.com
DB_DATABASE=if0_12345678_barberia
DB_USERNAME=if0_12345678
DB_PASSWORD=tu_contraseña_de_hosting
```

Guarda los cambios. Ya puedes abrir tu dirección.

## 5. Activar HTTPS (recomendado)

1. En el panel de InfinityFree, abre **SSL Certificates** y pide el certificado gratuito para tu subdominio. Puede tardar unos minutos.
2. Cuando esté activo, cambia `APP_URL` a `https://...` en el `.env`.

## Problemas comunes

| Síntoma | Solución |
|---|---|
| Error 500 o "Server Error" | Revisa los datos `DB_*` del `.env`. Los detalles están en `htdocs/laravel/storage/logs/laravel.log`. |
| La página se ve sin estilos | `APP_URL` tiene `https://` pero el certificado aún no está activo. Usa `http://` mientras tanto. |
| "Este horario acaba de ocuparse" en todos | Revisa que `APP_TIMEZONE=America/Lima` esté en el `.env`. |
| La agenda de demo está vacía | Las citas de ejemplo se crearon para la fecha en que armaste el paquete. Vuelve a ejecutar el script e importa el nuevo `.sql`. |

## Actualizar el sitio después de hacer cambios

1. Ejecuta `bash scripts/empaquetar-infinityfree.sh` otra vez.
2. Si solo cambiaste estilos o vistas, basta con subir de nuevo `htdocs/build/` y `htdocs/laravel/resources/views/`.
3. **No reemplaces** `htdocs/laravel/.env`, que ya tiene tus datos. El paquete trae uno nuevo con `CAMBIAR`.
