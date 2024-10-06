jQuery(document).ready(function($) {
    function filterRealEstate(page = 1) { // Додаємо параметр для номера сторінки
        var formData = $('#real-estate-filter').serialize(); // Серіалізація даних форми

        $.ajax({
            type: 'POST', // Тип запиту
            url: ajaxfilter.ajaxurl, // URL для AJAX-запиту
            data: {
                action: 'filter_real_estate', // Ім'я дії
                form_data: formData, // Дані форми
                page: page // Додаємо номер сторінки
            },
            success: function(response) {
                $('#results').html(response); // Вставка відповіді на сторінку
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown); // Логування помилок для налагодження
            }
        });
    }

    // Виклик фільтра при завантаженні сторінки
    filterRealEstate();

    // Виклик фільтра при натисканні кнопки "Submit" форми
    $('#real-estate-filter').submit(function(e) {
        e.preventDefault(); // Запобігання стандартному відправленню форми
        filterRealEstate(); // Виклик AJAX-фільтрації
    });

    $('#reset-button').click(function() {
        $('#real-estate-filter')[0].reset();
        filterRealEstate();
    });

    // Обробка кліків на пагінації
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault(); // Запобігання стандартній поведінці
        var page = $(this).text(); // Отримання номера сторінки з тексту посилання
        filterRealEstate(page); // Виклик фільтрації з переданим номером сторінки
    });
});
