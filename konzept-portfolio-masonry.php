<?php
/*
 * Plugin Name: Konzept Portfolio Masonry
 * Version: 1.0
 * Plugin URI: http://www.konzeptdigital.com/
 * Description: Plugin de Masonry creado para designa
 * Author: KonzeptDigital
 * Author URI: http://www.konzeptdigital.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: konzept-portfolio-masonry
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Simon Berton
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {exit;
}

// Load plugin class files
require_once ('includes/class-konzept-portfolio-masonry.php');
require_once ('includes/class-konzept-portfolio-masonry-settings.php');

// Load plugin libraries
require_once ('includes/lib/class-konzept-portfolio-masonry-admin-api.php');
require_once ('includes/lib/class-konzept-portfolio-masonry-post-type.php');
require_once ('includes/lib/class-konzept-portfolio-masonry-taxonomy.php');

/**
 * Returns the main instance of Konzept_Plugin_Masonry to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Konzept_Plugin_Masonry
 */
function Konzept_Plugin_Masonry() {
	$instance = Konzept_Plugin_Masonry::instance(__FILE__, '1.0.0');

	if (is_null($instance->settings)) {
		$instance->settings = Konzept_Plugin_Masonry_Settings::instance($instance);
	}

	return $instance;
}

Konzept_Plugin_Masonry();

add_shortcode('masonry_home', 'shortcode_home');

function shortcode_home() {
	$output = "";


	$args                 = array('post_type' => 'project', 'posts_per_page' => 80);
	$loop                 = new WP_Query($args);
	$proyectos_categorias = array();

	//Tengo que agregarlos a un array que tenga tipo array('columna1' => array(proyecto1, proyecto2),'columna2'=> array(proyecto3, proyecto5))
	while ($loop->have_posts()):$loop->the_post();

		$proyecto_categoria = get_the_terms(get_the_ID(), 'project_category');
		$proyecto_massonry  = get_the_terms(get_the_ID(), 'massonry');
		$proyecto_clientes  = get_the_terms(get_the_ID(), 'clientes');
		$post_url           = get_the_permalink();
		$shadow             = get_post_custom_values('shadow', get_the_ID());
		$shadow_out         = get_post_custom_values('shadow_out', get_the_ID());
		$shadow_in          = get_post_custom_values('shadow_in', get_the_ID());

		$massonrytexto = false; //Utilizamos para mostrar tipo massonry con foto y textos ///// o solo texto con url
		$custom_link = "";

		$imageUrl = get_the_post_thumbnail_url();

		$title            = get_the_title();
		$massonry = "massonry";
		if(isset($proyecto_massonry[0]))
		{
			$massonry  = $proyecto_massonry[0]->slug;
		}
		$categoria = "categoria";
		$categoria_nombre = "Categoria";
		if(isset($proyecto_categoria[0]))
		{
			$categoria        = $proyecto_categoria[0]->slug;
			$categoria_nombre = $proyecto_categoria[0]->name;
		}
		$cliente = "cliente";
		if(isset($proyecto_clientes[0]))
		{
			$cliente = $proyecto_clientes[0]->name;
		}

		

		$class_massonry = "grid-item";
		if ($massonry == "massonry-2") {
			$class_massonry .= " grid-item--width2 ";
		} else if ($massonry == "massonry-3") {
			$class_massonry .= " grid-item--height2 ";
		} else if ($massonry == "massonry-4") {
			$class_massonry .= " grid-item--width2 ";
			$class_massonry .= " grid-item--height2 ";
		}else if ($massonry == "massonrytexto") {
			$massonrytexto = true;
			$custom_link = get_post_custom_values('custom_link', get_the_ID());
			$custom_link = $custom_link[0];

		}

		$class_shadow = "";
		if ($shadow) {
			$class_shadow .= "shadow ";
		}
		if ($shadow_out) {
			$class_shadow .= "shadow_out ";
		}
		if ($shadow_in) {
			$class_shadow .= "shadow_in ";
		}

		$proyectos_categorias[] = array('ID' => get_the_ID(), 'url' => $post_url, 'imageUrl' => $imageUrl, 'title' => $title, 'massonry' => $class_massonry, 'categoria' => $categoria, 'cliente' => $cliente, 'shadow' => $class_shadow, 'massonrytexto' => $massonrytexto, "custom_link" => $custom_link);	

		

	endwhile;

	$output .= '<div class="grid"> <div class="grid-sizer"></div>';

	foreach ($proyectos_categorias as $proyecto) {
		if(!$proyecto['massonrytexto'])
		{
			$output .= sprintf('<div style="background-image: url(%s)" class="%s %s"><a href="%s"><span class="datos"><span class="title">%s</span><span class="categoria">proyecto: <b>%s</b></span><span class="cliente">cliente: <b>%s</b></span></span></a></div>', $proyecto['imageUrl'], $proyecto['massonry'], $proyecto['shadow'], $proyecto['url'], $proyecto['title'], $categoria_nombre, $cliente);
		}
		else
		{
			$output .= sprintf('<div style="background-image: url(%s)" class="%s %s"><a href="//%s"><span class="datos"><span class="title">%s</span><span class="categoria">Proyecto: <b>%s</b></span><span class="cliente">cliente: <b>%s</b></span></span></a></div>', $proyecto['imageUrl'], $proyecto['massonry'], $proyecto['shadow'], $proyecto['custom_link'], $proyecto['title'], $categoria_nombre, $cliente);
		}


	}

	//Levantar todos los proyectos que tengan el massonry home y ordenarlos segun la categoria seleccionada

	$output .= '</div>';
	$output .= "<script>jQuery('.grid').isotope({
				  percentPosition: true,
				  itemSelector: '.grid-item',
				  masonry: {
				    columnWidth: '.grid-sizer'
				  }
				});</script>";

	return $output;
}

add_shortcode('masonry_cliente_categoria_proyecto', 'shortcode_cliente_categoria_proyecto');
function shortcode_cliente_categoria_proyecto() {

	$proyecto_categoria = get_the_terms(get_the_ID(), 'project_category');
	$proyecto_clientes  = get_the_terms(get_the_ID(), 'clientes');

	return sprintf("<a href='%s' class='datos-proyecto datos-categoria'>%s</a><a href='%s' class='datos-proyecto datos-cliente'>%s</a>", get_site_url() . '?project_category=' . $proyecto_categoria[0]->slug, $proyecto_categoria[0]->name, get_site_url() . '?clientes=' . $proyecto_clientes[0]->slug, $proyecto_clientes[0]->name);
}

add_shortcode('masonry_cliente_categoria', 'shortcode_cliente_categoria');
//
function shortcode_cliente_categoria($atts) {
	$atts = shortcode_atts(array('cliente' => null), $atts);

	//Se muestran las categorias de los proyectos de un cliente dado
	//$atts tiene que venir el cliente
	$output = "<style>* { box-sizing: border-box; }

body {
  font-family: sans-serif;
}

/* ---- button ---- */

.button {
  display: inline-block;
  padding: 0.5em 1.0em;
  background: #EEE;
  border: none;
  border-radius: 7px;
  background-image: linear-gradient( to bottom, hsla(0, 0%, 0%, 0), hsla(0, 0%, 0%, 0.2) );
  color: #222;
  font-family: sans-serif;
  font-size: 16px;
  text-shadow: 0 1px white;
  cursor: pointer;
}

.button:hover {
  background-color: #8CF;
  text-shadow: 0 1px hsla(0, 0%, 100%, 0.5);
  color: #222;
}

.button:active,
.button.is-checked {
  background-color: #28F;
}

.button.is-checked {
  color: white;
  text-shadow: 0 -1px hsla(0, 0%, 0%, 0.8);
}

.button:active {
  box-shadow: inset 0 1px 10px hsla(0, 0%, 0%, 0.8);
}

/* ---- button-group ---- */

.button-group {
  margin-bottom: 20px;
}

.button-group:after {
  content: '';
  display: block;
  clear: both;
}

.button-group .button {
  float: left;
  border-radius: 0;
  margin-left: 0;
  margin-right: 1px;
}

.button-group .button:first-child { border-radius: 0.5em 0 0 0.5em; }
.button-group .button:last-child { border-radius: 0 0.5em 0.5em 0; }

/* ---- isotope ---- */

.grid {
  border: 1px solid #333;
}

/* clear fix */
.grid:after {
  content: '';
  display: block;
  clear: both;
}

/* ---- .element-item ---- */

.element-item {
  position: relative;
  float: left;
  width: 100px;
  height: 100px;
  margin: 5px;
  padding: 10px;
  background: #888;
  color: #262524;
}

.element-item > * {
  margin: 0;
  padding: 0;
}

.element-item .name {
  position: absolute;

  left: 10px;
  top: 60px;
  text-transform: none;
  letter-spacing: 0;
  font-size: 12px;
  font-weight: normal;
}

.element-item .symbol {
  position: absolute;
  left: 10px;
  top: 0px;
  font-size: 42px;
  font-weight: bold;
  color: white;
}

.element-item .number {
  position: absolute;
  right: 8px;
  top: 5px;
}

.element-item .weight {
  position: absolute;
  left: 10px;
  top: 76px;
  font-size: 12px;
}

.element-item.alkali          { background: #F00; background: hsl(   0, 100%, 50%); }
.element-item.alkaline-earth  { background: #F80; background: hsl(  36, 100%, 50%); }
.element-item.lanthanoid      { background: #FF0; background: hsl(  72, 100%, 50%); }
.element-item.actinoid        { background: #0F0; background: hsl( 108, 100%, 50%); }
.element-item.transition      { background: #0F8; background: hsl( 144, 100%, 50%); }
.element-item.post-transition { background: #0FF; background: hsl( 180, 100%, 50%); }
.element-item.metalloid       { background: #08F; background: hsl( 216, 100%, 50%); }
.element-item.diatomic        { background: #00F; background: hsl( 252, 100%, 50%); }
.element-item.halogen         { background: #F0F; background: hsl( 288, 100%, 50%); }
.element-item.noble-gas       { background: #F08; background: hsl( 324, 100%, 50%); }



</style>";

	if ($atts['cliente']) {
		$args = array('post_type' => 'project', 'taxonomy' => 'clientes', 'term' => $atts['cliente'], 'posts_per_page' => 80);

	} else {
		$args = array('post_type' => 'project', 'posts_per_page' => 80);
	}

	$loop                 = new WP_Query($args);
	$proyectos_categorias = array();
	$categorias           = array();

	//Tengo que agregarlos a un array que tenga tipo array('columna1' => array(proyecto1, proyecto2),'columna2'=> array(proyecto3, proyecto5))
	while ($loop->have_posts()):$loop->the_post();

	$proyecto_categoria = get_the_terms(get_the_ID(), 'project_category');
	$proyecto_massonry  = get_the_terms(get_the_ID(), 'massonry');
	$proyecto_clientes  = get_the_terms(get_the_ID(), 'clientes');
	$post_url           = get_permalink();
	$shadow             = get_post_custom_values('shadow', get_the_ID());
	$shadow_out         = get_post_custom_values('shadow_out', get_the_ID());
	$shadow_in          = get_post_custom_values('shadow_in', get_the_ID());

	$imageUrl = get_the_post_thumbnail_url();

	$title            = get_the_title();
	$massonry         = $proyecto_massonry[0]->slug;
	$categoria        = $proyecto_categoria[0]->slug;
	$categoria_nombre = $proyecto_categoria[0]->name;
	$cliente          = $proyecto_clientes[0]->name;

	//Guardo todas las categorias
	foreach ($proyecto_categoria as $cat) {
		$categorias[$cat->slug] = $cat->name;
	}

	$class_shadow = "";
	if ($shadow) {
		$class_shadow .= "shadow ";
	}
	if ($shadow_out) {
		$class_shadow .= "shadow_out ";
	}
	if ($shadow_in) {
		$class_shadow .= "shadow_in ";
	}
	$proyectos_categorias[] = array('ID' => get_the_ID(), 'url' => $post_url, 'imageUrl' => $imageUrl, 'title' => $title, 'massonry' => $class_massonry, 'categoria' => $categoria, 'cliente' => $cliente, 'shadow' => $class_shadow);

	endwhile;

	$output .= '<h2>Categorias</h2><div id="filters" class="button-group">  <button class="button is-checked" data-filter="*">Todos</button>';
	foreach ($categorias as $key => $value) {
		$output .= sprintf('<button class="button" data-filter=".%s">%s</button>', $key, $value);
	}

	$output .= '
</div>

<div class="grid">';
	foreach ($proyectos_categorias as $proyecto_categoria) {
		$output .= sprintf('<div style="background-image: url(%s)" class="element-item transition %s " data-category="">
								<a href="%s">
							    <h3 class="name">%s</h3>
							    <p class="symbol"></p>
							    </a>
							  </div>', $proyecto_categoria['imageUrl'], $proyecto_categoria['categoria'], $proyecto_categoria['url'], $proyecto_categoria['title']);
	}

	$output .= '</div>';

	$output .= "<script>

var grid= jQuery('.grid').isotope({
	percentPosition: true,
  itemSelector: '.element-item',
  layoutMode: 'fitRows',
  getSortData: {
    name: '.name',
    symbol: '.symbol',
    number: '.number parseInt',
    category: '[data-category]',
    weight: function( itemElem ) {
      var weight = jQuery( itemElem ).find('.weight').text();
      return parseFloat( weight.replace( /[\(\)]/g, '') );
    }
  }
});

// filter functions
var filterFns = {
  // show if number is greater than 50
  numberGreaterThan50: function() {
    var number = jQuery(this).find('.number').text();
    return parseInt( number, 10 ) > 50;
  },
  // show if name ends with -ium
  ium: function() {
    var name = jQuery(this).find('.name').text();
    return name.match( /ium$/ );
  }
};

// bind filter button click
jQuery('#filters').on( 'click', 'button', function() {
  var filterValue = jQuery( this ).attr('data-filter');
  // use filterFn if matches value
  filterValue = filterFns[ filterValue ] || filterValue;
grid.isotope({ filter: filterValue });
});

// bind sort button click
jQuery('#sorts').on( 'click', 'button', function() {
  var sortByValue = jQuery(this).attr('data-sort-by');
grid.isotope({ sortBy: sortByValue });
});

// change is-checked class on buttons
jQuery('.button-group').each( function( i, buttonGroup ) {
  var buttonGroup= jQuery( buttonGroup );
buttonGroup.on( 'click', 'button', function() {
buttonGroup.find('.is-checked').removeClass('is-checked');
    jQuery( this ).addClass('is-checked');
  });
});
  </script>";
	//Levantar todos los proyectos que tengan el massonry home y ordenarlos segun la categoria seleccionada
	return $output;
}