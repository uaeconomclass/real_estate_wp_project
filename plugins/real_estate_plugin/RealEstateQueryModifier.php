<?php
/**
 * Class RealEstateQueryModifier
 *
 * Змінює запит для кастомного типу записів "real_estate"
 */
class RealEstateQueryModifier {

    public function __construct() {
        add_action('pre_get_posts', [$this, 'modify_real_estate_query']);
    }

    // Метод для зміни запиту
    public function modify_real_estate_query($query) {
        // Перевіряємо, чи це основний запит і виводиться кастомний тип запису
        if (is_admin() || !$query->is_main_query() || !is_post_type_archive('real_estate')) {
            return;
        }

        // Перевіряємо, чи користувач на архіві типу запису real_estate
        if ($query->is_post_type_archive('real_estate')) {
            // Додаємо сортування за полем "екологічність" (екологічний рейтинг)
            $query->set('meta_key', 'field_ecological_rating'); // Поле, по якому сортуємо
            $query->set('orderby', 'meta_value_num'); // Сортуємо за числовим значенням
            $query->set('order', 'DESC'); // Сортування за спаданням
        }
    }
}
