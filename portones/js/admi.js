document.addEventListener('DOMContentLoaded', async () => {

    const filaEstado    = document.getElementById('fila_estado');
    const statRegistros = document.getElementById('stat_registros');

    // ── Verificar estado del servidor consultando un endpoint real ────────────
    try {
        const inicio = Date.now();
        const res    = await fetch(`${API_URL}/monitoreo.php`);
        const ms     = Date.now() - inicio;
        const data   = await res.json();

        const latenciaClase = ms < 200 ? 'text-success' : ms < 600 ? 'text-warning' : 'text-danger';

        filaEstado.innerHTML = `
            <div class="col-md-4">
                <span class="text-success">🟢 Servidor AWS: Online</span>
            </div>
            <div class="col-md-4">
                <span class="${latenciaClase}">
                    ${ms < 600 ? '🟢' : '🟡'} BD control_escolar: Latencia ${ms}ms
                </span>
            </div>
            <div class="col-md-4">
                <span class="text-secondary">Total Registros Hoy: ${data.registros?.length ?? '–'}</span>
            </div>
        `;

    } catch {
        filaEstado.innerHTML = `
            <div class="col-12 text-center">
                <span class="text-danger">🔴 No se pudo conectar con el servidor AWS</span>
            </div>`;
    }
});
