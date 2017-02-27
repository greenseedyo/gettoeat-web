$('.date').datepicker();

$('.select-type').on('change', function(){
    var $this = $(this);
    var type_id = parseInt($this.find('option:selected').val());
    if (0 == type_id) {
        return;
    }
    var id = $this.data('id') || '';
    var url = '/manage_events.php?id=' + id + '&type_id=' + type_id + '&action=get_form';
    $this.closest('form').find('.form-by-type').load(url);
}).trigger('change');
