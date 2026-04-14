document.addEventListener('DOMContentLoaded', () => {
    cargarDespachos();
    cargarDocumentos();

    //setear la fecha
    document.getElementById('fecha').valueAsDate = new Date();

    // Evento para busqueda en tiempo real
    document.getElementById('buscarCodigo').addEventListener('input', cargarDocumentos);
    document.getElementById('filtroDespacho').addEventListener('change', cargarDocumentos);
    
    // Enviar formulario
    document.getElementById('formRegistro').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api.php?action=registrar_documento', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            mostrarToast(result.message, result.status);
            if(result.status === 'success'){
                e.target.reset();
                document.getElementById('fecha').valueAsDate = new Date(); // resetear fecha
                cargarDocumentos();
            }
        } catch (error) {
            mostrarToast('Error de conexión con el servidor', 'error');
        }
    });
});

async function cargarDespachos() {
    const res = await fetch('api.php?action=get_despachos');
    const despachos = await res.json();
    
    const selectForm = document.getElementById('despacho');
    const selectFiltro = document.getElementById('filtroDespacho');
    
    let options = '<option value="">Seleccione...</option>';
    despachos.forEach(d => {
        options += `<option value="${d.id}">${d.nombre}</option>`;
    });

    selectForm.innerHTML = options;
    selectFiltro.innerHTML = '<option value="">Todos los despachos</option>' + options;
}

async function cargarDocumentos() {
    const search = document.getElementById('buscarCodigo').value;
    const despacho = document.getElementById('filtroDespacho').value;
    
    const res = await fetch(`api.php?action=listar_documentos&search=${search}&despacho=${despacho}`);
    const documentos = await res.json();
    
    const tbody = document.querySelector('#tablaDocumentos tbody');
    tbody.innerHTML = '';

    documentos.forEach(doc => {
        let claseBadge = '';
        if(doc.estado === 'Pendiente de entrega') claseBadge = 'pendiente';
        else if(doc.estado === 'Cargo de envío') claseBadge = 'envio';
        else if(doc.estado === 'Cargo devuelto entregado') claseBadge = 'entregado';
        else claseBadge = 'notificado';

        // Select dinámico para cambiar estado según el flujo de procesos
        let acciones = `
            <select onchange="cambiarEstado(${doc.id}, this.value)">
                <option value="" disabled selected>Cambiar estado...</option>
                <option value="Cargo devuelto entregado">Marcar Entregado</option>
                <option value="No recepcionado (notificado)">Marcar No Recepcionado</option>
            </select>
        `;

        if(doc.estado === 'Pendiente de entrega') {
            acciones = `<small>Genere guía para enviar</small>`;
        }

        tbody.innerHTML += `
            <tr>
                <td><strong>${doc.codigo_unico}</strong></td>
                <td>${doc.tipo_documento}</td>
                <td>${doc.nombre_despacho}</td>
                <td><span class="badge ${claseBadge}">${doc.estado}</span></td>
                <td>${acciones}</td>
            </tr>
        `;
    });
}

async function generarGuia() {
    const id_despacho = document.getElementById('filtroDespacho').value;
    if(!id_despacho) {
        alert("Por favor, filtre por un despacho específico primero para agrupar sus documentos y generar la guía.");
        return;
    }

    const formData = new FormData();
    formData.append('id_despacho', id_despacho);

    const response = await fetch('api.php?action=generar_guia', {
        method: 'POST',
        body: formData
    });
    const result = await response.json();
    
    alert(result.message); // Usamos alert para notificaciones críticas de negocio
    if(result.status === 'success') cargarDocumentos();
}

async function cambiarEstado(id_documento, nuevo_estado) {
    if(!confirm(`¿Seguro que desea cambiar el estado a: ${nuevo_estado}?`)) {
        cargarDocumentos(); // resetear select
        return;
    }

    const formData = new FormData();
    formData.append('id_documento', id_documento);
    formData.append('nuevo_estado', nuevo_estado);

    await fetch('api.php?action=actualizar_estado_documento', {
        method: 'POST',
        body: formData
    });
    cargarDocumentos();
}

function mostrarToast(mensaje, tipo) {
    const toast = document.getElementById('toast');
    toast.textContent = mensaje;
    toast.className = `toast ${tipo}`;
    setTimeout(() => { toast.classList.add('hidden'); }, 3000);
}

// Funcionalidad de Modo Oscuro (Creatividad Adicional)
const btnDarkMode = document.getElementById('btnDarkMode');

// Verificar si ya tenía el modo oscuro activado antes
if(localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark-mode');
    btnDarkMode.textContent = 'Modo Claro';
}

btnDarkMode.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    
    if(document.body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        btnDarkMode.textContent = 'Modo Claro';
    } else {
        localStorage.setItem('theme', 'light');
        btnDarkMode.textContent = 'Modo Oscuro';
    }
});
