<!-- sweetalert2 -->
<script src="<?=base_url('assets/plugins/sweetalert/js/sweetalert2.all.min.js')?>" type="text/javascript"></script>        

<script>
$(function(){
   'use strict'

    <?php
    if (session()->has('show_sweetalert')){
        $type = session()->getFlashdata('type') ?? 'info';
        if($type == 'danger'){
            $type = 'error';
        }
    ?>
    Swal.fire({
        title: `<?=session()->getFlashdata('title') ?? 'Atenção!'?>`,
        icon: `<?=$type?>`,
        html: `<?=session()->getFlashdata('text') ?? ''?>`,
        showCloseButton: true,
        showCancelButton: `<?=session()->getFlashdata('showCancelButton') ?? false?>`,
        focusConfirm: false,
        confirmButtonText: `<?=session()->getFlashdata('confirmButtonText') ?? 'Ok'?>`,
        confirmButtonAriaLabel: "OK",
        cancelButtonText:  `<?=session()->getFlashdata('cancelButtonText') ?? 'Cancelar'?>`,
        cancelButtonAriaLabel: "Cancelar"
    });
    <?php
    }
    ?>

    $(document).on('click', '.link-excluir-swal', function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var text = $(this).data('mensagem') || 'Esta operação não poderá ser desfeita.';
        Swal.fire({
            title: "Confirma exclusão?",
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Excluir",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed && href) {
                window.location.href = href;
            }
        });
    });
});
</script>