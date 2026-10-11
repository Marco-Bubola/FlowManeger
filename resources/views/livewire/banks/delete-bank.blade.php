<!-- Modal de Confirmação de Exclusão -->
@if($showDeleteModal)
    <x-delete-dialog confirm="delete" cancel="closeDeleteModal" title="Excluir cartão?" confirm-label="Excluir cartão" icon="bi-credit-card">
        O cartão e as faturas dele serão apagados. Não dá para desfazer.
    </x-delete-dialog>
@endif 