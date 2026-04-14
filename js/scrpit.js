// Esperar a que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Validación para el Formulario de Registro (index.php)
    const formRegistro = document.getElementById('formRegistro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            const codigo = document.getElementsByName('codigo')[0].value;
            const remitente = document.getElementsByName('remitente')[0].value;

            if (codigo.trim().length < 3) {
                alert(" El código debe tener al menos 3 caracteres.");
                e.preventDefault();
                return;
            }

            if (remitente.trim() === "") {
                alert(" Por favor, ingrese el nombre del remitente.");
                e.preventDefault();
            }
        });
    }

    // 2. Validación para la Bandeja de Salida (bandeja_entrada.php)
    const formGuia = document.getElementById('formGuia');
    if (formGuia) {
        formGuia.addEventListener('submit', function(e) {
            const seleccionados = document.querySelectorAll('input[name="docs[]"]:checked');
            
            if (seleccionados.length === 0) {
                alert(" Error: Selecciona al menos un documento para generar la guía.");
                e.preventDefault();
            } else {
                const total = seleccionados.length;
                if (!confirm(`¿Deseas generar una guía para ${total} documento(s)?`)) {
                    e.preventDefault();
                }
            }
        });
    }
});