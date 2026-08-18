<script>
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href*="/lang/"]');

        if (! link) {
            return;
        }

        if (! document.querySelector('.fi-modal-open')) {
            return;
        }

        const message = document.documentElement.lang === 'en'
            ? 'Switching language will discard any unsaved changes in this form. Continue?'
            : 'تغيير اللغة سيؤدي إلى فقدان أي بيانات غير محفوظة في هذا النموذج. هل تريد المتابعة؟';

        if (! window.confirm(message)) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);
</script>
