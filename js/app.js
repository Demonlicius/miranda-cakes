// ============================================
// MIRANDA CAKES - JavaScript Principal
// ============================================

// ---- ANIMACIONES AL SCROLL ----
const observerOptions = { threshold: 0.15, rootMargin: '0px 0px -50px 0px' };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(el => {
    if (el.isIntersecting) { el.target.classList.add('visible'); }
  });
}, observerOptions);
document.querySelectorAll('.animate-up').forEach(el => observer.observe(el));

// ---- COTIZADOR ----
const PRECIOS = {
  tamano: { pequeño: 250, mediano: 400, grande: 600, extra: 850 },
  betun: { fondant: 80, crema_mantequilla: 50, chantilly: 40, ganache: 90 },
  forma: { redondo: 0, cuadrado: 50 },
  deco3d: 150,
  personas_factor: 3
};

let cotizacion = {
  pastel_base: null,
  tamano: 'mediano',
  betun: 'fondant',
  forma: 'redondo',
  decoracion_3d: false,
  personas: 10,
  colores: '',
  mensaje: '',
  decoracion: ''
};

function calcularPrecio() {
  let precio = PRECIOS.tamano[cotizacion.tamano];
  precio += PRECIOS.betun[cotizacion.betun];
  precio += PRECIOS.forma[cotizacion.forma];
  if (cotizacion.decoracion_3d) precio += PRECIOS.deco3d;
  if (cotizacion.personas > 20) precio += (cotizacion.personas - 20) * PRECIOS.personas_factor;
  return precio;
}

function actualizarPrecio() {
  const precio = calcularPrecio();
  const display = document.getElementById('precio-total');
  const anticipo = document.getElementById('anticipo-display');
  if (display) display.textContent = '$' + precio.toFixed(2);
  if (anticipo) anticipo.textContent = '$' + (precio * 0.5).toFixed(2);
}

function seleccionarOpcion(grupo, valor, elemento) {
  cotizacion[grupo] = valor;
  const btns = document.querySelectorAll(`[data-grupo="${grupo}"]`);
  btns.forEach(b => b.classList.remove('selected'));
  if (elemento) elemento.classList.add('selected');
  actualizarPrecio();
}

// Inicializar cotizador
document.addEventListener('DOMContentLoaded', () => {
  actualizarPrecio();

  const personasInput = document.getElementById('personas');
  if (personasInput) {
    personasInput.addEventListener('input', (e) => {
      cotizacion.personas = parseInt(e.target.value) || 10;
      actualizarPrecio();
      document.getElementById('personas-val').textContent = e.target.value;
    });
  }

  const deco3dCheck = document.getElementById('decoracion_3d');
  if (deco3dCheck) {
    deco3dCheck.addEventListener('change', (e) => {
      cotizacion.decoracion_3d = e.target.checked;
      actualizarPrecio();
    });
  }
});

// ---- ENVIAR COTIZACION ----
async function enviarCotizacion(e) {
  e.preventDefault();
  if (!document.getElementById('cotizador-form')) return;

  cotizacion.colores = document.getElementById('colores')?.value || '';
  cotizacion.mensaje = document.getElementById('mensaje_deco')?.value || '';
  cotizacion.decoracion = document.getElementById('descripcion_deco')?.value || '';

  const precio = calcularPrecio();
  const anticipo = precio * 0.5;

  const { isConfirmed } = await Swal.fire({
    title: 'Confirmar Pedido',
    html: `
      <div style="text-align:left; padding: 1rem 0;">
        <p style="color:#7A5C4A; margin-bottom:1rem">Resumen de tu pastel personalizado:</p>
        <table style="width:100%; font-size:0.92rem;">
          <tr><td style="padding:4px 0"><b>Tamaño:</b></td><td>${cotizacion.tamano}</td></tr>
          <tr><td><b>Betún:</b></td><td>${cotizacion.betun.replace('_', ' ')}</td></tr>
          <tr><td><b>Forma:</b></td><td>${cotizacion.forma}</td></tr>
          <tr><td><b>Personas:</b></td><td>${cotizacion.personas}</td></tr>
          <tr><td><b>Dec. 3D:</b></td><td>${cotizacion.decoracion_3d ? 'Sí' : 'No'}</td></tr>
          <tr><td colspan="2" style="padding-top:1rem;border-top:1px dashed #E8C4B8;"><b>Total: <span style="color:#C4735A;font-size:1.2rem">$${precio.toFixed(2)}</span></b></td></tr>
          <tr><td colspan="2"><small style="color:#B09080">Anticipo (50%): $${anticipo.toFixed(2)}</small></td></tr>
        </table>
      </div>`,
    confirmButtonText: 'Confirmar Pedido',
    cancelButtonText: 'Revisar',
    showCancelButton: true,
    confirmButtonColor: '#C4735A',
    cancelButtonColor: '#A07850',
  });

  if (!isConfirmed) return;

  const formData = new FormData();
  formData.append('action', 'crear_pedido');
  Object.entries(cotizacion).forEach(([k, v]) => formData.append(k, v));
  formData.append('precio_total', precio);
  formData.append('anticipo_monto', anticipo);

  try {
    const res = await fetch('/php/pedidos.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Pedido creado!',
        text: 'Tu pedido ha sido registrado. Pronto te contactaremos.',
        confirmButtonColor: '#C4735A',
      }).then(() => { window.location.href = '/mis-pedidos.php'; });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al crear el pedido', confirmButtonColor: '#C4735A' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión. Intenta de nuevo.', confirmButtonColor: '#C4735A' });
  }
}

// ---- PAGO DE ANTICIPO ----
async function pagarAnticipo(pedidoId, monto) {
  const { isConfirmed } = await Swal.fire({
    title: 'Pagar Anticipo',
    html: `
      <p style="color:#7A5C4A; margin-bottom:1rem">Confirmas el pago de anticipo:</p>
      <div style="background:#F5E8E4; border-radius:12px; padding:1.2rem; text-align:center;">
        <p style="font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; color:#B09080; margin-bottom:0.3rem">Monto del Anticipo</p>
        <p style="font-size:2rem; font-weight:700; color:#C4735A; margin:0">$${parseFloat(monto).toFixed(2)}</p>
      </div>
      <p style="font-size:0.85rem; color:#B09080; margin-top:1rem;">⚠️ Esto es un prototipo. No se realizará ningún cobro real.</p>`,
    confirmButtonText: 'Confirmar Anticipo',
    cancelButtonText: 'Cancelar',
    showCancelButton: true,
    confirmButtonColor: '#C4735A',
    cancelButtonColor: '#A07850',
  });

  if (!isConfirmed) return;

  const formData = new FormData();
  formData.append('action', 'pagar_anticipo');
  formData.append('pedido_id', pedidoId);

  try {
    const res = await fetch('/php/pedidos.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Anticipo registrado!',
        text: 'Tu anticipo ha sido confirmado.',
        confirmButtonColor: '#C4735A',
      }).then(() => location.reload());
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#C4735A' });
    }
  } catch {
    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#C4735A' });
  }
}

// ---- LIQUIDAR ----
async function liquidarPedido(pedidoId, saldo) {
  const { isConfirmed } = await Swal.fire({
    title: 'Liquidar Pedido',
    html: `
      <p style="color:#7A5C4A; margin-bottom:1rem">Estás a punto de liquidar tu pedido:</p>
      <div style="background:#F5E8E4; border-radius:12px; padding:1.2rem; text-align:center;">
        <p style="font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; color:#B09080; margin-bottom:0.3rem">Saldo Final</p>
        <p style="font-size:2rem; font-weight:700; color:#C4735A; margin:0">$${parseFloat(saldo).toFixed(2)}</p>
      </div>
      <p style="color:#7A5C4A; margin-top:1rem; font-size:0.92rem;">Al confirmar, recibirás un <b>código único</b> de confirmación para recoger tu pastel. 🎂</p>
      <p style="font-size:0.85rem; color:#B09080; margin-top:0.5rem;">⚠️ Esto es un prototipo. No se realizará ningún cobro real.</p>`,
    confirmButtonText: 'Liquidar y obtener recibo',
    cancelButtonText: 'Cancelar',
    showCancelButton: true,
    confirmButtonColor: '#C4735A',
    cancelButtonColor: '#A07850',
  });

  if (!isConfirmed) return;

  const formData = new FormData();
  formData.append('action', 'liquidar_pedido');
  formData.append('pedido_id', pedidoId);

  try {
    const res = await fetch('/php/pedidos.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: '¡Pedido Liquidado!',
        html: `
          <p style="color:#7A5C4A; margin-bottom:1rem">Tu recibo ha sido generado. Guarda este código:</p>
          <div style="background:linear-gradient(135deg,#F5E8E4,#E8C4B8); border:2px dashed #C4735A; border-radius:12px; padding:1.5rem; text-align:center;">
            <p style="font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; color:#B09080; margin-bottom:0.5rem">Código de Confirmación</p>
            <p style="font-family:'Courier New',monospace; font-size:1.8rem; font-weight:bold; color:#6B4C35; letter-spacing:4px; margin:0">${data.codigo}</p>
          </div>
          <p style="font-size:0.85rem; color:#B09080; margin-top:1rem;">Presenta este código al recoger tu pastel. 🎂</p>`,
        confirmButtonText: 'Ver mi recibo',
        confirmButtonColor: '#C4735A',
      }).then(() => { window.location.href = '/recibo.php?id=' + data.recibo_id; });
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#C4735A' });
    }
  } catch {
    Swal.fire({ icon: 'error', title: 'Error de conexión', confirmButtonColor: '#C4735A' });
  }
}
