/**
 * ============================================
 * CHATBOT WHATSAPP + IA - CONNECT CHILE
 * ============================================
 * 
 * Widget de chat inteligente con:
 * - Respuestas automáticas con IA
 * - Integración con WhatsApp Business
 * - Menú de opciones personalizable
 * - Historial de conversaciones
 */

class ConnectChileChatbot {
  constructor(config = {}) {
    this.config = {
      whatsappNumber: config.whatsappNumber || '+56999918468',
      businessName: config.businessName || 'Connect Chile',
      welcomeMessage: config.welcomeMessage || '¡Hola! 👋 Soy el asistente virtual de Connect Chile. ¿En qué puedo ayudarte hoy?',
      apiEndpoint: config.apiEndpoint || '/chatbot/api.php',
      ...config
    };

    this.state = {
      isOpen: false,
      currentView: 'menu', // 'menu', 'chat', 'form'
      messages: [],
      userContext: {},
      isTyping: false
    };

    this.init();
  }

  init() {
    this.createWidget();
    this.attachEvents();
    this.loadContext();
  }

  // ===== CREAR ELEMENTOS DEL WIDGET =====
  createWidget() {
    // Contenedor principal
    const widget = document.createElement('div');
    widget.className = 'cc-chat-widget';
    widget.id = 'ccChatWidget';

    // Botón flotante
    const button = document.createElement('button');
    button.className = 'cc-chat-button';
    button.innerHTML = `
      <i class="fab fa-whatsapp"></i>
      <span class="cc-chat-badge" style="display: none;">1</span>
    `;
    button.onclick = () => this.toggleChat();

    // Tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'cc-chat-tooltip';
    tooltip.textContent = '¿Necesitas ayuda? Escríbenos';

    // Ventana de chat
    const window_ = document.createElement('div');
    window_.className = 'cc-chat-window';
    window_.id = 'ccChatWindow';
    window_.innerHTML = this.getWindowHTML();

    widget.appendChild(button);
    widget.appendChild(tooltip);
    widget.appendChild(window_);

    document.body.appendChild(widget);
    this.elements = { widget, button, window: window_ };
  }

  getWindowHTML() {
    return `
      <!-- Header -->
      <div class="cc-chat-header">
        <div class="cc-chat-avatar">
          <i class="fas fa-robot"></i>
        </div>
        <div class="cc-chat-header-info">
          <h4>Asistente Virtual</h4>
          <span><span class="cc-chat-status"></span> En línea</span>
        </div>
        <button class="cc-chat-close" onclick="chatbot.toggleChat()">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Menú Principal -->
      <div class="cc-chat-menu" id="ccChatMenu">
        <div class="cc-chat-welcome">
          <i class="fab fa-whatsapp"></i>
          <h3>¡Bienvenido a Connect Chile!</h3>
          <p>Selecciona una opción para comenzar:</p>
        </div>
        <div class="cc-chat-options">
          <button class="cc-chat-option" onclick="chatbot.selectOption('planes')">
            <i class="fas fa-wifi"></i>
            <div>
              <strong>Planes de Internet</strong>
              <span>Conoce nuestros planes y precios</span>
            </div>
          </button>
          <button class="cc-chat-option" onclick="chatbot.selectOption('cobertura')">
            <i class="fas fa-map-marker-alt"></i>
            <div>
              <strong>Consultar Cobertura</strong>
              <span>Verifica si llegamos a tu zona</span>
            </div>
          </button>
          <button class="cc-chat-option" onclick="chatbot.selectOption('factibilidad')">
            <i class="fas fa-clipboard-check"></i>
            <div>
              <strong>Solicitar Factibilidad</strong>
              <span>Evaluación técnica gratuita</span>
            </div>
          </button>
          <button class="cc-chat-option" onclick="chatbot.selectOption('soporte')">
            <i class="fas fa-headset"></i>
            <div>
              <strong>Soporte Técnico</strong>
              <span>Reporta problemas de conexión</span>
            </div>
          </button>
          <button class="cc-chat-option" onclick="chatbot.selectOption('pago')">
            <i class="fas fa-credit-card"></i>
            <div>
              <strong>Pagar Cuenta</strong>
              <span>Accede a tu cuenta y pagos</span>
            </div>
          </button>
          <button class="cc-chat-option" onclick="chatbot.selectOption('contacto')">
            <i class="fas fa-phone"></i>
            <div>
              <strong>Contacto Directo</strong>
              <span>Habla con un ejecutivo</span>
            </div>
          </button>
        </div>
        <a href="https://wa.me/${this.config.whatsappNumber}" target="_blank" class="cc-whatsapp-direct">
          <i class="fab fa-whatsapp"></i>
          Abrir WhatsApp Directo
        </a>
      </div>

      <!-- Área de Conversación -->
      <div class="cc-chat-conversation" id="ccChatConversation" style="display: none;">
        <div class="cc-chat-messages" id="ccChatMessages"></div>
        <div class="cc-chat-input-area">
          <input 
            type="text" 
            class="cc-chat-input" 
            id="ccChatInput" 
            placeholder="Escribe tu mensaje..."
            onkeypress="if(event.key==='Enter') chatbot.sendMessage()"
          >
          <button class="cc-chat-send" onclick="chatbot.sendMessage()">
            <i class="fas fa-paper-plane"></i>
          </button>
        </div>
      </div>
    `;
  }

  // ===== EVENTOS =====
  attachEvents() {
    // Cerrar al hacer clic fuera
    document.addEventListener('click', (e) => {
      if (this.state.isOpen && 
          !this.elements.widget.contains(e.target) && 
          !e.target.closest('.cc-chat-widget')) {
        this.toggleChat();
      }
    });
  }

  // ===== FUNCIONALIDAD =====
  toggleChat() {
    this.state.isOpen = !this.state.isOpen;
    this.elements.window.classList.toggle('active', this.state.isOpen);
    
    if (this.state.isOpen) {
      this.hideBadge();
      // Enfocar input si estamos en chat
      setTimeout(() => {
        const input = document.getElementById('ccChatInput');
        if (input) input.focus();
      }, 300);
    }
  }

  selectOption(option) {
    this.state.currentView = 'chat';
    document.getElementById('ccChatMenu').style.display = 'none';
    document.getElementById('ccChatConversation').style.display = 'flex';

    // Agregar mensaje de bienvenida según la opción
    const welcomeMessages = {
      planes: '¡Perfecto! Te ayudo con información sobre nuestros planes de internet. ¿Qué te gustaría saber?',
      cobertura: 'Te ayudo a verificar la cobertura en tu zona. ¿Cuál es tu dirección o comuna?',
      factibilidad: 'Excelente, vamos a solicitar una evaluación técnica. ¿Cuál es tu dirección?',
      soporte: 'Entiendo que necesitas soporte técnico. ¿Cuál es el problema que estás experimentando?',
      pago: 'Te redirigiré al portal de pagos. ¿Tienes tu número de cliente?',
      contacto: 'Te conectaré con un ejecutivo. Mientras tanto, ¿puedes contarme brevemente qué necesitas?'
    };

    this.addMessage('bot', welcomeMessages[option] || this.config.welcomeMessage);
    this.state.userContext.lastOption = option;
  }

  async sendMessage() {
    const input = document.getElementById('ccChatInput');
    const message = input.value.trim();
    
    if (!message) return;

    // Agregar mensaje del usuario
    this.addMessage('user', message);
    input.value = '';

    // Mostrar "escribiendo..."
    this.showTyping();

    // Procesar con IA
    await this.processWithAI(message);
  }

  async processWithAI(userMessage) {
    try {
      // Llamar a la API
      const response = await fetch(this.config.apiEndpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: userMessage,
          context: this.state.userContext,
          sessionId: this.getSessionId()
        })
      });

      const data = await response.json();
      this.hideTyping();

      if (data.success) {
        this.addMessage('bot', data.response);
        
        // Actualizar contexto
        if (data.context) {
          this.state.userContext = { ...this.state.userContext, ...data.context };
          this.saveContext();
        }

        // Si hay acción adicional
        if (data.action) {
          this.handleAction(data.action, data.actionData);
        }
      } else {
        this.addMessage('bot', 'Lo siento, tuve un problema. ¿Podrías intentar de nuevo?');
      }
    } catch (error) {
      console.error('Error:', error);
      this.hideTyping();
      
      // Respuesta local si falla la API
      const localResponse = this.getLocalResponse(userMessage);
      this.addMessage('bot', localResponse);
    }
  }

  // ===== RESPUESTAS LOCALES (Fallback) =====
  getLocalResponse(message) {
    const lowerMsg = message.toLowerCase();
    const option = this.state.userContext.lastOption;

    // Respuestas según el contexto
    const responses = {
      planes: {
        keywords: ['precio', 'costo', 'cuanto', 'vale', 'plan', 'megas', 'mb'],
        response: `Tenemos planes desde $20.990 mensual (15 Mbps) hasta $45.990 (35 Mbps). Todos incluyen IVA y transferencia ilimitada. ¿Te gustaría que te envíe más detalles de algún plan específico?`
      },
      cobertura: {
        keywords: ['panguipulli', 'lago', 'choshuenco', 'neltume', 'coñaripe'],
        response: `¡Excelente! Tenemos cobertura en esa zona. Para confirmar tu dirección específica, te recomiendo solicitar una factibilidad técnica gratuita. ¿Deseas que te ayude con eso?`
      },
      factibilidad: {
        keywords: ['direccion', 'calle', 'numero', 'casa'],
        response: `Perfecto, estoy registrando tu solicitud. También puedes completar el formulario en nuestra página de factibilidad para una evaluación más rápida. ¿Te gustaría el enlace?`
      },
      soporte: {
        keywords: ['lento', 'no funciona', 'cortado', 'sin internet', 'problema'],
        response: `Lamento escuchar eso. Voy a crear un ticket de soporte. Mientras tanto, prueba reiniciar tu router desconectándolo 10 segundos. ¿El problema persiste?`
      },
      pago: {
        keywords: ['pagar', 'cuenta', 'factura', 'deuda'],
        response: `Puedes pagar tu cuenta ingresando al portal de clientes con tu usuario y contraseña. ¿Necesitas ayuda para acceder?`
      }
    };

    // Buscar respuesta relevante
    if (option && responses[option]) {
      const { keywords, response } = responses[option];
      if (keywords.some(k => lowerMsg.includes(k))) {
        return response;
      }
    }

    // Respuestas generales
    if (lowerMsg.includes('hola') || lowerMsg.includes('buenos') || lowerMsg.includes('buenas')) {
      return '¡Hola! 👋 ¿En qué puedo ayudarte hoy?';
    }
    if (lowerMsg.includes('gracias')) {
      return '¡De nada! 😊 Estoy aquí para lo que necesites.';
    }
    if (lowerMsg.includes('adios') || lowerMsg.includes('chao') || lowerMsg.includes('hasta luego')) {
      return '¡Hasta luego! 👋 Que tengas un excelente día.';
    }

    return 'Entiendo. Para ayudarte mejor, ¿podrías darme más detalles? También puedes hablar directamente con un ejecutivo haciendo clic en "Abrir WhatsApp Directo".';
  }

  // ===== UTILIDADES =====
  addMessage(sender, text) {
    const messagesContainer = document.getElementById('ccChatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `cc-chat-message ${sender}`;
    
    const time = new Date().toLocaleTimeString('es-CL', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });

    messageDiv.innerHTML = `
      ${text}
      <div class="cc-chat-message-time">${time}</div>
    `;

    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Guardar en historial
    this.state.messages.push({ sender, text, time });
  }

  showTyping() {
    this.state.isTyping = true;
    const messagesContainer = document.getElementById('ccChatMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'cc-chat-typing';
    typingDiv.id = 'ccTypingIndicator';
    typingDiv.innerHTML = '<span></span><span></span><span></span>';
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  hideTyping() {
    this.state.isTyping = false;
    const typing = document.getElementById('ccTypingIndicator');
    if (typing) typing.remove();
  }

  hideBadge() {
    const badge = document.querySelector('.cc-chat-badge');
    if (badge) badge.style.display = 'none';
  }

  showBadge(count = 1) {
    const badge = document.querySelector('.cc-chat-badge');
    if (badge) {
      badge.textContent = count;
      badge.style.display = 'flex';
    }
  }

  handleAction(action, data) {
    switch (action) {
      case 'redirect':
        if (data.url) {
          setTimeout(() => {
            window.open(data.url, '_blank');
          }, 1000);
        }
        break;
      case 'showBadge':
        this.showBadge(data.count || 1);
        break;
    }
  }

  // ===== SESIÓN Y CONTEXTO =====
  getSessionId() {
    let sessionId = localStorage.getItem('cc_chat_session');
    if (!sessionId) {
      sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      localStorage.setItem('cc_chat_session', sessionId);
    }
    return sessionId;
  }

  saveContext() {
    localStorage.setItem('cc_chat_context', JSON.stringify(this.state.userContext));
    localStorage.setItem('cc_chat_messages', JSON.stringify(this.state.messages));
  }

  loadContext() {
    const context = localStorage.getItem('cc_chat_context');
    const messages = localStorage.getItem('cc_chat_messages');
    
    if (context) this.state.userContext = JSON.parse(context);
    if (messages) this.state.messages = JSON.parse(messages);
  }

  // ===== DESTRUIR =====
  destroy() {
    const widget = document.getElementById('ccChatWidget');
    if (widget) widget.remove();
  }
}

// ===== INICIALIZACIÓN =====
let chatbot;

document.addEventListener('DOMContentLoaded', () => {
  chatbot = new ConnectChileChatbot({
    whatsappNumber: '56999918468',
    businessName: 'Connect Chile',
    apiEndpoint: '/chatbot/api.php'
  });
});

// Exportar para uso global
window.ConnectChileChatbot = ConnectChileChatbot;
