# Laboratorio SSRF – Seguridad Web (Opción B)

Este proyecto corresponde a un laboratorio práctico sobre la vulnerabilidad **Server-Side Request Forgery (SSRF)**, desarrollado como parte de la asignatura de **Seguridad Web** del Máster en la **VIU**.

El objetivo es **demostrar una vulnerabilidad SSRF real**, su explotación y posteriormente **implementar una mitigación efectiva** a nivel de backend.

---

## 📌 Tecnologías utilizadas

- PHP 8.3
- Laravel 12
- Blade (vista simple)
- HTTP Client de Laravel
- Servidor embebido de Laravel (`php artisan serve`)

---

## 📖 ¿Qué es SSRF?

**Server-Side Request Forgery (SSRF)** es una vulnerabilidad que ocurre cuando una aplicación backend realiza peticiones HTTP a URLs controladas por el usuario sin validación adecuada.

Esto permite a un atacante:
- Acceder a servicios internos
- Consultar endpoints no expuestos públicamente
- Interactuar con metadatos o APIs internas
- Utilizar el servidor como intermediario para otros ataques

---

## 🧪 Descripción del laboratorio

El laboratorio contiene dos versiones del mismo flujo:

### ❌ Versión vulnerable
- El backend recibe una URL desde el frontend
- Realiza una petición HTTP directa usando dicha URL
- No valida el destino ni la IP
- Permite acceder a recursos internos

### ✅ Versión segura
- Valida el esquema (`http/https`)
- Resuelve el host a IPs mediante DNS
- Bloquea direcciones privadas, loopback y reservadas
- Bloquea `localhost`
- Deshabilita redirecciones
- Aplica timeout a las peticiones

---

## 🗂️ Rutas disponibles

### Recurso interno (solo servidor)
```
GET /internal-secret
```

### Versión vulnerable (SSRF explotable)
```
GET /ssrf/vulnerable?url={URL}
```

### Versión segura (SSRF mitigado)
```
GET /ssrf/secure?url={URL}
```

---

## 💥 Ejemplo de explotación (SSRF)

Entrada en versión vulnerable:
```
http://127.0.0.1:8000/internal-secret
```

Resultado:
```json
{
  "requested_url": "http://127.0.0.1:8000/internal-secret",
  "response": "INTERNAL SECRET - solo accesible desde el servidor"
}
```

---

## 🔐 Ejemplo de mitigación

Entrada en versión segura:
```
http://127.0.0.1:8000/internal-secret
```

Resultado:
```json
{
  "error": "Destino no permitido (IP privada o local)",
  "resolved_ip": "127.0.0.1"
}
```

---

## ▶️ Cómo ejecutar el proyecto

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --port=8001
```

---

## 🎯 Conclusión

Este laboratorio demuestra cómo una implementación aparentemente simple puede introducir una vulnerabilidad SSRF crítica si no se valida adecuadamente la entrada del usuario.

La versión segura implementa controles efectivos que evitan el abuso del backend como intermediario, manteniendo la funcionalidad legítima de acceso a recursos externos.

---

## 👨‍🎓 Contexto académico

- Asignatura: Seguridad Web
- Actividad: SSRF – Opción B
- Universidad: VIU
