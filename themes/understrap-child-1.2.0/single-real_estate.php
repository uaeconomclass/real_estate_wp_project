<?php
/**
 * The template for displaying single real estate
 *
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

?>

<div class="wrapper" id="page-wrapper">

	<div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

		<div class="row">

			<?php
			// Do the left sidebar check and open div#primary.
			get_template_part( 'global-templates/left-sidebar-check' );
			?>

			<main class="site-main" id="main">
			
			
<?php
// Отримати ID посту
$post_id = get_the_ID();

// Отримати значення полів
$building_name = get_field('field_building_name', $post_id);
$location_coordinates = get_field('field_location_coordinates', $post_id);
$number_of_floors = get_field('field_number_of_floors', $post_id);
$building_type = get_field('field_building_type', $post_id);
$ecological_rating = get_field('field_ecological_rating', $post_id);
$building_image = get_field('field_building_image', $post_id);
$rooms = get_field('field_rooms', $post_id);
$district_terms = get_the_terms($post_id, 'district');
?>

<div class="real-estate-single">


<div class="container my-4">
    <h1 class="text-center mb-4"><?php echo esc_html($building_name); ?></h1>

    <div class="row">
        <div class="col-md-6">
            <?php if ($building_image): ?>
                <div class="building-image text-center mb-3">
                    <img class="img-fluid" src="<?php echo esc_url($building_image['url']); ?>" alt="<?php echo esc_attr($building_name); ?>" style="max-width: 100%; height: auto;">
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <?php if ($district_terms && !is_wp_error($district_terms)): ?>
                <p><strong>Район:</strong> 
                    <?php foreach ($district_terms as $term): ?>
                        <?php echo esc_html($term->name); ?> 
                    <?php endforeach; ?>
                </p>
            <?php else: ?>
                <p><strong>Район:</strong> Не вказано</p>
            <?php endif; ?>

            <p><strong>Координати:</strong> <?php echo esc_html($location_coordinates); ?></p>
            <p><strong>Кількість поверхів:</strong> <?php echo esc_html($number_of_floors); ?></p>
            <p><strong>Тип будівлі:</strong> <?php echo esc_html($building_type); ?></p>
            <p><strong>Екологічність:</strong> <?php echo esc_html($ecological_rating); ?></p>
        </div>
    </div>
</div>

	<h2 class="text-center my-4">Доступні квартири:</h2>
	<ol class="rooms-list list-unstyled row">
		<?php if ($rooms): ?>
			<?php foreach ($rooms as $room): ?>
				<li class="col-md-6 col-lg-4 mb-4">
					<div class="card h-100">
						<?php if ($room['field_room_image']): ?>
							<div class="room-image">
								<img class="card-img-top" src="<?php echo esc_url($room['field_room_image']['url']); ?>" alt="Room Image" style="height: 200px; object-fit: cover;">
							</div>
						<?php endif; ?>
						<div class="card-body">
							<p><strong>Площа:</strong> <?php echo esc_html($room['field_room_area']); ?> м²</p>
							<p><strong>Кількість кімнат:</strong> <?php echo esc_html($room['field_number_of_rooms']); ?></p>
							<p><strong>Балкон:</strong> <?php echo esc_html($room['field_balcony']); ?></p>
							<p><strong>Санвузол:</strong> <?php echo esc_html($room['field_bathroom']); ?></p>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		<?php else: ?>
			<p class="text-center">Квартири не доступні.</p>
		<?php endif; ?>
	</ol>

</div>



			</main>

			<?php
			// Do the right sidebar check and close div#primary.
			get_template_part( 'global-templates/right-sidebar-check' );
			?>

		</div><!-- .row -->

	</div><!-- #content -->

</div><!-- #page-wrapper -->

<?php
get_footer();
