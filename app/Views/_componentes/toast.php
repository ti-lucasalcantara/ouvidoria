<!-- toast -->
<link href="<?=base_url('assets/plugins/toast/css/jquery.growl.css')?>" rel="stylesheet" type="text/css" />

<!-- toast -->
<script src="<?=base_url('assets/plugins/toast/js/jquery.growl.js')?>" type="text/javascript"></script>        
        
<script>
function showToast(title = 'Atenção!', text = '-', type = 'default') {
    
    if (type === 'danger') type = 'error';
    if (type === 'success') type = 'notice';

    $.growl({
        title: title,
        message: text,
        style: type,
    });
}

// Exibe o toast se houver dados na sessão
<?php if (session()->has('show_toast')): ?>
    showToast(
        `<?= session()->getFlashdata('title') ?? 'Atenção!' ?>`,
        `<?= session()->getFlashdata('text') ?? '-' ?>`,
        `<?= session()->getFlashdata('type') ?? 'default' ?>`
    );
<?php endif; ?>
</script>