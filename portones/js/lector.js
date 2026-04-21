(() => {
    const video      = document.getElementById('video');
    const canvas     = document.getElementById('canvas-scan');
    const ctx        = canvas.getContext('2d');
    const overlay    = document.getElementById('resultado-overlay');
    const resIcono   = document.getElementById('res-icono');
    const resMsg     = document.getElementById('res-msg');
    const resSub     = document.getElementById('res-sub');

    let modoEvento     = 'entrada';   // 'entrada' | 'salida'
    let escaneando     = true;
    let cooldown       = false;
    let streamActivo   = null;

    // ── Modo entrada/salida ───────────────────────────────────────────────────
    document.getElementById('btn_tipo_entrada').addEventListener('click', () => setModo('entrada'));
    document.getElementById('btn_tipo_salida').addEventListener('click',  () => setModo('salida'));

    function setModo(modo) {
        modoEvento = modo;
        document.getElementById('lbl_modo').textContent = modo === 'entrada' ? 'Entrada' : 'Salida';
        document.getElementById('btn_tipo_entrada').classList.toggle('btn-success',     modo === 'entrada');
        document.getElementById('btn_tipo_entrada').classList.toggle('btn-outline-secondary', modo !== 'entrada');
        document.getElementById('btn_tipo_salida').classList.toggle('btn-warning',      modo === 'salida');
        document.getElementById('btn_tipo_salida').classList.toggle('btn-outline-secondary',  modo !== 'salida');
    }
    setModo('entrada');

    // ── Iniciar cámara ────────────────────────────────────────────────────────
    async function iniciarCamara() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            streamActivo = stream;
            video.srcObject = stream;
            video.addEventListener('loadedmetadata', () => {
                canvas.width  = video.videoWidth;
                canvas.height = video.videoHeight;
                requestAnimationFrame(escanearFrame);
            });
        } catch (err) {
            console.error('Cámara no disponible:', err);
            document.querySelector('#video-container').innerHTML =
                '<p class="text-white text-center p-4">⚠️ Cámara no disponible.<br>Usa el campo manual.</p>';
        }
    }

    // ── Loop de escaneo ───────────────────────────────────────────────────────
    function escanearFrame() {
        if (!escaneando || cooldown) { requestAnimationFrame(escanearFrame); return; }
        if (video.readyState !== video.HAVE_ENOUGH_DATA) { requestAnimationFrame(escanearFrame); return; }

        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const codigo    = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: 'dontInvert'
        });

        if (codigo && codigo.data) {
            procesarCodigo(codigo.data);
        }

        requestAnimationFrame(escanearFrame);
    }

    // ── Procesar código escaneado ─────────────────────────────────────────────
    async function procesarCodigo(valor) {
        if (cooldown) return;
        cooldown = true;

        const payload = construirPayload(valor);
        if (!payload) { cooldown = false; return; }

        try {
            const res = await enviarDatos('acceso.php', payload);
            mostrarResultado(res);
        } catch {
            mostrarResultado(null);
        }
    }

    // ── Construir payload según tipo de código ────────────────────────────────
    function construirPayload(valor) {
        valor = valor.trim();
        if (/^ITLA-\d+$/i.test(valor)) {
            return { qr_token: valor, tipo_evento: modoEvento };
        }
        if (/^[A-Z0-9\-]+$/i.test(valor) && valor.length >= 4) {
            return { matricula: valor, tipo_evento: modoEvento };
        }
        return null;
    }

    // ── Mostrar overlay resultado ─────────────────────────────────────────────
    function mostrarResultado(res) {
        const permitido = res && res.ok && res.permitido;

        overlay.className = permitido ? 'concedido' : 'denegado';
        overlay.style.display = 'flex';

        resIcono.textContent = permitido ? '✅' : '❌';
        resMsg.textContent   = permitido
            ? (modoEvento === 'entrada' ? 'Acceso Concedido' : 'Salida Registrada')
            : 'Acceso Denegado';
        resSub.textContent   = res ? (res.nombre ?? res.mensaje ?? '') : 'Sin respuesta del servidor';

        // Ocultar overlay tras 2.5 segundos y reactivar escaneo
        setTimeout(() => {
            overlay.style.display = 'none';
            cooldown = false;
        }, 2500);
    }

    // ── Verificación manual ───────────────────────────────────────────────────
    document.getElementById('btn_manual').addEventListener('click', () => {
        const val = document.getElementById('input_manual').value.trim();
        if (val) procesarCodigo(val);
        document.getElementById('input_manual').value = '';
    });
    document.getElementById('input_manual').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') document.getElementById('btn_manual').click();
    });

    // ── Iniciar ───────────────────────────────────────────────────────────────
    iniciarCamara();
})();
