<script>
    $(".modalExcluir").click(function(e){
        e.preventDefault();
       
        var id      = $(this).data('id-excluir');
        var tabela  = $(this).data('tabela-excluir') ?? '';
        var url     = $(this).data('url-excluir');
        var text    = $(this).data('mensagem-excluir');

        Swal.fire({
            title: "Confirma exclusão?",
            html:  `${text ?? ''}<br>Essa operação não poderá ser desfeita.`,
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#828282",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33",
            confirmButtonText: "Excluir",
            preConfirm: async (login) => {
                try {
                    var fd = new FormData();
                    fd.append('id', id);
                    fd.append('tabela', tabela);
                    
                    const resultAjax = await $.ajax({
                        type: "POST",
                        method: 'POST',
                        url: url,
                        data: fd,
                        dataType: 'html',
                        enctype:"multipart/form-data",
                        contentType: false,
                        processData: false,
                        beforeSend: function(){
                            // loading btn
                        },
                    });
                    return JSON.parse(resultAjax);

                } catch (error) {
                    /*  console.log('error', error);  */
                    Swal.showValidationMessage(`Falha na requisição:  ${error.responseText ??  error}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                console.log(result);
                Swal.fire({
                    title: `${result.value.title ?? 'Erro inesperado' }`,
                    text: `${result.value.message ?? '' }`,
                    icon: `${result.value.type ?? '' }`,
                }).then( (result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }
        });

    });
</script>