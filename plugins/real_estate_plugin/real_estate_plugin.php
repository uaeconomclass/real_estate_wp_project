<?php
/**
 * Plugin Name: Real Estate Plugin
 * Description: Плагін для створення об'єктів нерухомості.
 */

require_once plugin_dir_path(__FILE__) . 'RealEstateQueryModifier.php';

new RealEstateQueryModifier();

function create_real_estate_post_type() {
    register_post_type('real_estate', array(
        'labels' => array(
            'name' => __('Об’єкти нерухомості'),
            'singular_name' => __('Об’єкт нерухомості')
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'real-estate'),
        'supports' => array('title', 'editor', 'thumbnail'),
    ));
}
add_action('init', 'create_real_estate_post_type');

function create_district_taxonomy() {
    register_taxonomy('district', 'real_estate', array(
        'label' => __('Район'),
        'rewrite' => array('slug' => 'district'),
        'hierarchical' => true,
    ));
}
add_action('init', 'create_district_taxonomy');

function enqueue_custom_scripts() {
    wp_enqueue_script('ajax-filter-script', plugins_url('/js/ajax-filter.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('ajax-filter-script', 'ajaxfilter', array('ajaxurl' => admin_url('admin-ajax.php')));
}


add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');



class Real_Estate_Filter_Widget extends WP_Widget {
    function __construct() {
        parent::__construct('real_estate_filter_widget', 'Фільтр Нерухомості', array('description' => 'Віджет для фільтрації нерухомості'));
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo real_estate_filter_shortcode();
        echo $args['after_widget'];
    }

    public function form($instance) {}

    public function update($new_instance, $old_instance) {}
}
add_action('widgets_init', function() {
    register_widget('Real_Estate_Filter_Widget');
});

function real_estate_filter_shortcode() {
    ob_start();
    ?>
    <form id="real-estate-filter" class="mb-4">
        <div class="form-group">
            <label for="field_building_name">Назва будинку</label>
            <input type="text" id="field_building_name" name="field_building_name" class="form-control" placeholder="Назва будинку">
        </div>
        
        <div class="form-group">
            <label for="field_location_coordinates">Координати місцезнаходження</label>
            <input type="text" id="field_location_coordinates" name="field_location_coordinates" class="form-control" placeholder="Широта, Довгота">
        </div>
        
        <div class="form-group">
            <label for="field_number_of_floors">Кількість поверхів</label>
            <select id="field_number_of_floors" name="field_number_of_floors" class="form-control">
                <option value="">Виберіть кількість поверхів</option>
                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Тип будівлі</label>
            <div>
                <?php foreach (['панель', 'цегла', 'піноблок'] as $type): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="field_building_type" id="type_<?php echo $type; ?>" value="<?php echo $type; ?>">
                        <label class="form-check-label" for="type_<?php echo $type; ?>"><?php echo ucfirst($type); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="field_ecological_rating">Екологічність</label>
            <select id="field_ecological_rating" name="field_ecological_rating" class="form-control">
                <option value="">Виберіть рівень екологічності</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Фільтрувати</button>	<button type="reset" class="btn btn-primary" id="reset-button">Очистити</button>
    </form>
	

    <div id="filter-results" class="mt-4"></div>
    <div id="results" class="mt-5">

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('real_estate_filter', 'real_estate_filter_shortcode');


add_action('wp_ajax_filter_real_estate', 'filter_real_estate_callback');
add_action('wp_ajax_nopriv_filter_real_estate', 'filter_real_estate_callback');

function filter_real_estate_callback() {
    global $wpdb;

    // Parse form data from POST request
    parse_str($_POST['form_data'], $form_data);

    // Кількість елементів на сторінку
    $items_per_page = 5;
    
    // Отримання номера сторінки з POST даних
    $current_page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
    
    // Розрахунок зсуву для LIMIT
    $offset = ($current_page - 1) * $items_per_page;

    // Start building the query
    $query = "SELECT p.ID, p.post_title 
              FROM wp_posts AS p 
              LEFT JOIN wp_postmeta AS pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'field_number_of_floors'
              LEFT JOIN wp_postmeta AS pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'field_building_type'
              LEFT JOIN wp_postmeta AS pm3 ON p.ID = pm3.post_id AND pm3.meta_key = 'field_ecological_rating'
              LEFT JOIN wp_postmeta AS pm4 ON p.ID = pm4.post_id AND pm4.meta_key = 'field_location_coordinates'
              WHERE p.post_type = 'real_estate' 
              AND p.post_status = 'publish'";

    // Check for the building name
    if (!empty($form_data['field_building_name'])) {
        $building_name = esc_sql($form_data['field_building_name']);
        $query .= " AND p.post_title LIKE '%$building_name%'";
    }

    // Check for the number of floors
    if (!empty($form_data['field_number_of_floors'])) {
        $number_of_floors = esc_sql($form_data['field_number_of_floors']);
        $query .= " AND pm1.meta_value = '$number_of_floors'";
    }

    // Check for the building type
    if (!empty($form_data['field_building_type'])) {
        $building_type = esc_sql($form_data['field_building_type']);
        $query .= " AND pm2.meta_value = '$building_type'";
    }

    // Check for the ecological rating
    if (!empty($form_data['field_ecological_rating'])) {
        $ecological_rating = esc_sql($form_data['field_ecological_rating']);
        $query .= " AND pm3.meta_value = '$ecological_rating'";
    }

    // Check for location coordinates
    if (!empty($form_data['field_location_coordinates'])) {
        $location_coordinates = esc_sql($form_data['field_location_coordinates']);
        $query .= " AND pm4.meta_value LIKE '%$location_coordinates%'";
    }

    // Group by to avoid duplicates in case of multiple meta entries
    $query .= " GROUP BY p.ID";

    // Додаємо обмеження та зсув
    $query .= " LIMIT $items_per_page OFFSET $offset";

    // Execute the query
    $results = $wpdb->get_results($query);

    if ($results) {
        echo '<div class="container mt-4"><div class="row">'; // Bootstrap контейнер та рядок

        foreach ($results as $result) {
            $permalink = get_permalink($result->ID);
            $building_image = get_field('field_building_image', $result->ID);

            // Bootstrap колонка з карткою
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card">'; // Картка
            display_real_estate_details($result->ID);
            echo '</div>'; // Закриття картки
            echo '</div>'; // Закриття колонки
        }

        echo '</div></div>'; // Закриття ряду та контейнера
    } else {
        // Виведення повідомлення, якщо нічого не знайдено
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-warning" role="alert">Об\'єкти нерухомості не знайдені.</div>';
        echo '</div>';
    }

    // Пагінація
    // Отримуємо загальну кількість записів для визначення кількості сторінок
    $total_count = count($results);

    // Визначаємо загальну кількість сторінок
    $total_pages = ceil($total_count / $items_per_page);

    // Генеруємо HTML для пагінації
    echo '<nav aria-label="Page navigation example">';
    echo '<ul class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        echo "<li class='page-item $active_class'><a class='page-link ' href='#' >$i</a></li>";
    }
    echo '</ul>';
    echo '</nav>';

    wp_die();
}


function display_real_estate_details($post_id) {
    $building_name = get_field('field_building_name', $post_id);
    $location_coordinates = get_field('field_location_coordinates', $post_id);
    $number_of_floors = get_field('field_number_of_floors', $post_id);
    $building_type = get_field('field_building_type', $post_id);
    $ecological_rating = get_field('field_ecological_rating', $post_id);
    $building_image = get_field('field_building_image', $post_id);
    $field_rooms = get_post_meta($post_id, 'field_rooms', true);
    $district_terms = get_the_terms($post_id, 'district');
    $permalink = get_permalink($post_id);

    if ($building_image) {
        echo '<img src="' . esc_url($building_image['url']) . '" class="card-img-top" alt="' . esc_attr($building_name) . '">';
    }

    echo '<div class="card-body">';
    echo '<h5 class="card-title"><a href="' . esc_url($permalink) . '">' . esc_html($building_name) . '</a></h5>';
    echo '<ul class="list-group list-group-flush">';

    if ($building_type) {
        echo '<li class="list-group-item"><strong>Тип будівлі: </strong>' . esc_html($building_type) . '</li>';
    }
    if ($location_coordinates) {
        echo '<li class="list-group-item"><strong>Координати: </strong>' . esc_html($location_coordinates) . '</li>';
    }
    if ($number_of_floors) {
        echo '<li class="list-group-item"><strong>Кількість поверхів: </strong>' . esc_html($number_of_floors) . '</li>';
    }
    if ($ecological_rating) {
        echo '<li class="list-group-item"><strong>Екологічний рейтинг: </strong>' . esc_html($ecological_rating) . '</li>';
    }
    if ($field_rooms) {
        echo '<li class="list-group-item"><strong>Доступних приміщень: </strong>' . esc_html($field_rooms) . '</li>';
    }
    if ($district_terms && !is_wp_error($district_terms)) {
        $districts = array_map(function($term) { return $term->name; }, $district_terms);
        echo '<li class="list-group-item"><strong>Район: </strong>' . esc_html(implode(', ', $districts)) . '</li>';
    }

    echo '</ul>';
    echo '</div>';
}

