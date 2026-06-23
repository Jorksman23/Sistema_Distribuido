document.addEventListener('DOMContentLoaded', function() {

    const inputComprobante = document.getElementById('comprobante');
    if (!inputComprobante) return;

    // Preview del archivo seleccionado
    inputComprobante.addEventListener('change', function(){
        const file = this.files[0];
        if (!file) return;

        document.getElementById('fileInfo').classList.remove('hidden');
        document.getElementById('fileName').innerText = file.name;
        document.getElementById('fileSize').innerText = (file.size / 1024 / 1024).toFixed(2) + ' MB';

        const previewContainer = document.getElementById('previewContainer');
        const previewImage     = document.getElementById('previewImage');
        previewContainer.classList.remove('hidden');
        previewImage.classList.remove('hidden');

        const reader = new FileReader();
        reader.onload = function(e){
            previewImage.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    // Eliminar archivo seleccionado
    document.getElementById('btnEliminarArchivo').addEventListener('click', function(){
        document.getElementById('comprobante').value = '';
        document.getElementById('fileInfo').classList.add('hidden');
        document.getElementById('previewContainer').classList.add('hidden');
        document.getElementById('previewImage').src = '';
    });

    // Confirmación antes de enviar
    document.getElementById('formComprobante').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;

        const archivo = document.getElementById('comprobante').files[0];
        if (!archivo) {
            Swal.fire({
                title: 'Sin archivo',
                text: 'Por favor selecciona un comprobante antes de enviar.',
                icon: 'warning',
                confirmButtonColor: '#0300a3',
            });
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿El comprobante "' + archivo.name + '" que deseas enviar es el correcto?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0300a3',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, enviar',
            cancelButtonText: 'No, revisar',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

});
