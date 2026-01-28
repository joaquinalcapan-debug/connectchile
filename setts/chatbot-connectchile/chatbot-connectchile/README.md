# 🤖 Chatbot WhatsApp + IA - Connect Chile

Sistema de chatbot inteligente con integración a WhatsApp Business para Connect Chile S.A.

## 📋 Características

- ✅ **Respuestas con IA** - Procesamiento de lenguaje natural
- ✅ **Integración WhatsApp** - Conexión directa con WhatsApp Business
- ✅ **Menú interactivo** - Opciones predefinidas para usuarios
- ✅ **Base de conocimientos** - Información sobre planes, cobertura, soporte
- ✅ **Contexto de conversación** - El bot recuerda el historial
- ✅ **Diseño responsive** - Funciona en móvil y desktop
- ✅ **Colores de marca** - Integrado con la identidad de Connect Chile

## 🚀 Instalación

### 1. Copiar archivos

Copia todos los archivos a tu servidor web:

```
/chatbot-connectchile/
  ├── chatbot-widget.css    # Estilos del widget
  ├── chatbot-widget.js     # Lógica del chatbot
  ├── api.php               # Backend PHP
  └── README.md             # Este archivo
```

### 2. Incluir en tus páginas HTML

Agrega estos códigos antes del cierre de `</body>` en tus páginas:

```html
<!-- Font Awesome (si no lo tienes) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Chatbot CSS -->
<link rel="stylesheet" href="chatbot-connectchile/chatbot-widget.css">

<!-- Chatbot JS -->
<script src="chatbot-connectchile/chatbot-widget.js"></script>
```

### 3. Configurar API de OpenAI (Opcional)

Para respuestas con IA más avanzadas:

1. Obtén una API key de OpenAI en https://platform.openai.com
2. Edita `api.php` y agrega tu key:

```php
private $openaiApiKey = 'tu-api-key-aqui';
private $useOpenAI = true;
```

## 📝 Personalización

### Cambiar número de WhatsApp

Edita `chatbot-widget.js`:

```javascript
chatbot = new ConnectChileChatbot({
  whatsappNumber: '569XXXXXXXX',  // Tu número
  businessName: 'Connect Chile',
  apiEndpoint: '/chatbot/api.php'
});
```

### Modificar mensajes de bienvenida

Edita el método `getLocalResponse()` en `api.php` para cambiar las respuestas automáticas.

### Agregar nuevas opciones al menú

Edita `getWindowHTML()` en `chatbot-widget.js`:

```javascript
<button class="cc-chat-option" onclick="chatbot.selectOption('nueva_opcion')">
  <i class="fas fa-icono"></i>
  <div>
    <strong>Título</strong>
    <span>Descripción</span>
  </div>
</button>
```

## 🔧 Integración con WhatsApp Business API

Para una integración completa con WhatsApp Business API:

1. Crear cuenta en [Meta for Developers](https://developers.facebook.com/)
2. Configurar WhatsApp Business
3. Obtener Phone Number ID y Access Token
4. Configurar webhook en `api.php`

## 📱 Uso

El chatbot aparecerá automáticamente como un botón flotante en la esquina inferior derecha. Al hacer clic, se abre el menú de opciones.

### Opciones disponibles:

1. **Planes de Internet** - Información sobre planes y precios
2. **Consultar Cobertura** - Verificar disponibilidad en zona
3. **Solicitar Factibilidad** - Evaluación técnica gratuita
4. **Soporte Técnico** - Ayuda con problemas de conexión
5. **Pagar Cuenta** - Acceso al portal de pagos
6. **Contacto Directo** - Hablar con un ejecutivo

## 🎨 Colores

El chatbot usa los colores de marca de Connect Chile:

- Rojo: `#e63946`
- Azul: `#1d3557`
- Verde WhatsApp: `#25D366`

## 🔒 Seguridad

- Las sesiones se guardan en localStorage
- Los datos sensibles no se almacenan
- Las llamadas API usan HTTPS

## 📞 Soporte

Para soporte técnico contactar a:
- WhatsApp: +56 9 9991 8468
- Email: contacto@connectchile.cl

---

**Connect Chile S.A.** - Internet inalámbrico para zonas rurales y urbanas.
